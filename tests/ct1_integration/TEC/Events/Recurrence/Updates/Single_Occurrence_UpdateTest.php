<?php

namespace TEC\Events\Recurrence\Updates;

use Codeception\TestCase\WPTestCase;
use DateTimeImmutable;
use DateTimeZone;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Dates_Service;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

class Single_Occurrence_UpdateTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * The refusals captured from the Freeze_Guard action, as `[ post_id, meta_key ]` pairs.
	 *
	 * @var array<int,array{0: int, 1: string}>
	 */
	public static array $refusals = [];

	public static function capture_refusal( $post_id, $meta_key ): void {
		self::$refusals[] = [ (int) $post_id, (string) $meta_key ];
	}

	/**
	 * @after
	 */
	public function clear_notice(): void {
		delete_transient( Single_Occurrence_Update::NOTICE_TRANSIENT . get_current_user_id() );
	}

	private function tz(): DateTimeZone {
		return new DateTimeZone( 'America/Sao_Paulo' );
	}

	/**
	 * A dates-only Event on 2050-01-03 09:00 with additional dates on 2050-01-10 and 2050-01-17.
	 */
	private function given_a_dates_only_event(): WP_Post {
		return $this->given_a_multi_date_event(
			[
				[ 'start' => '2050-01-10 09:00:00', 'end' => '2050-01-10 10:00:00' ],
				[ 'start' => '2050-01-17 09:00:00', 'end' => '2050-01-17 10:00:00' ],
			],
			[
				'start_date' => '2050-01-03 09:00:00',
				'end_date'   => '2050-01-03 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		);
	}

	/**
	 * A rule-based Event (weekly, 3 dates) whose rules are frozen: the rows exist, the rset carries an RRULE.
	 */
	private function given_a_frozen_rule_event( bool $with_meta ): WP_Post {
		$post = $this->given_a_dates_only_event();

		if ( $with_meta ) {
			update_post_meta(
				$post->ID,
				'_EventRecurrence',
				[
					'rules'       => [
						[
							'type'           => 'Weekly',
							'custom'         => [ 'interval' => 1 ],
							'end-type'       => 'After',
							'end-count'      => 3,
							'EventStartDate' => '2050-01-03 09:00:00',
							'EventEndDate'   => '2050-01-03 10:00:00',
						],
					],
					'exclusions'  => [],
					'description' => null,
				]
			);
		} else {
			delete_post_meta( $post->ID, '_EventRecurrence' );
		}

		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20500103T090000\nDTEND;TZID=America/Sao_Paulo:20500103T100000\nRRULE:FREQ=WEEKLY;COUNT=3;BYDAY=MO" ]
		);

		return $post;
	}

	private function occurrence_on( int $post_id, string $start ): Occurrence {
		$occurrence = Occurrence::where( 'post_id', '=', $post_id )->where( 'start_date', '=', $start )->first();
		$this->assertInstanceOf( Occurrence::class, $occurrence, "No occurrence on {$start}." );

		return $occurrence;
	}

	private function provisional_id( Occurrence $occurrence ): int {
		return tribe( ID_Generator::class )->provide_id( (int) $occurrence->occurrence_id );
	}

	private function starts_of( int $post_id ): array {
		return array_map(
			static fn( Occurrence $o ) => $o->start_date,
			iterator_to_array( Occurrence::where( 'post_id', '=', $post_id )->order_by( 'start_date', 'ASC' )->all(), false )
		);
	}

	/**
	 * It should move an additional date of a dates only event keeping the occurrence id
	 *
	 * @test
	 */
	public function should_move_an_additional_date_of_a_dates_only_event_keeping_the_occurrence_id(): void {
		$post       = $this->given_a_dates_only_event();
		$occurrence = $this->occurrence_on( $post->ID, '2050-01-10 09:00:00' );
		$id         = (int) $occurrence->occurrence_id;

		$moved = tribe( Single_Occurrence_Update::class )->apply(
			$this->provisional_id( $occurrence ),
			new DateTimeImmutable( '2050-01-12 14:00:00', $this->tz() ),
			new DateTimeImmutable( '2050-01-12 15:30:00', $this->tz() )
		);

		$this->assertTrue( $moved );
		// Same row, new dates.
		$row = Occurrence::find( $id, 'occurrence_id' );
		$this->assertEquals( '2050-01-12 14:00:00', $row->start_date );
		$this->assertEquals( '2050-01-12 15:30:00', $row->end_date );
		$this->assertEquals( 5400, (int) $row->duration );
		// The set is intact: the moved date replaced the old one, count unchanged.
		$this->assertEquals( [ '2050-01-03 09:00:00', '2050-01-12 14:00:00', '2050-01-17 09:00:00' ], $this->starts_of( $post->ID ) );
		// The parent's own date did not move.
		$this->assertEquals( '2050-01-03 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		// The authored meta and the rset follow.
		$meta = get_post_meta( $post->ID, '_EventRecurrence', true );
		$this->assertEquals( [ '2050-01-12', '2050-01-17' ], array_column( array_column( array_column( $meta['rules'], 'custom' ), 'date' ), 'date' ) );
		$this->assertStringContainsString( 'RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20500112T140000/20500112T153000', (string) Event::find( $post->ID, 'post_id' )->rset );
		$this->assertStringNotContainsString( '20500110T090000', (string) Event::find( $post->ID, 'post_id' )->rset );
	}

	/**
	 * It should move the event own date when the first occurrence moves
	 *
	 * @test
	 */
	public function should_move_the_event_own_date_when_the_first_occurrence_moves(): void {
		$post       = $this->given_a_dates_only_event();
		$occurrence = $this->occurrence_on( $post->ID, '2050-01-03 09:00:00' );
		$id         = (int) $occurrence->occurrence_id;

		$moved = tribe( Single_Occurrence_Update::class )->apply(
			$this->provisional_id( $occurrence ),
			new DateTimeImmutable( '2050-01-04 09:00:00', $this->tz() ),
			new DateTimeImmutable( '2050-01-04 10:00:00', $this->tz() )
		);

		$this->assertTrue( $moved );
		$this->assertEquals( '2050-01-04 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2050-01-04 10:00:00', get_post_meta( $post->ID, '_EventEndDate', true ) );
		$this->assertEquals( '2050-01-04 09:00:00', Event::find( $post->ID, 'post_id' )->start_date );
		$this->assertEquals( '2050-01-04 09:00:00', Occurrence::find( $id, 'occurrence_id' )->start_date );
		// The additional dates are absolute and stay.
		$this->assertEquals( [ '2050-01-04 09:00:00', '2050-01-10 09:00:00', '2050-01-17 09:00:00' ], $this->starts_of( $post->ID ) );
	}

	/**
	 * It should refuse a move colliding with another occurrence
	 *
	 * @test
	 */
	public function should_refuse_a_move_colliding_with_another_occurrence(): void {
		$post       = $this->given_a_dates_only_event();
		$occurrence = $this->occurrence_on( $post->ID, '2050-01-10 09:00:00' );

		$moved = tribe( Single_Occurrence_Update::class )->apply(
			$this->provisional_id( $occurrence ),
			new DateTimeImmutable( '2050-01-17 09:00:00', $this->tz() ),
			new DateTimeImmutable( '2050-01-17 10:00:00', $this->tz() )
		);

		$this->assertFalse( $moved );
		$this->assertEquals( [ '2050-01-03 09:00:00', '2050-01-10 09:00:00', '2050-01-17 09:00:00' ], $this->starts_of( $post->ID ) );
		$notice = get_transient( Single_Occurrence_Update::NOTICE_TRANSIENT . get_current_user_id() );
		$this->assertEquals( 'error', $notice['type'] );
	}

	/**
	 * It should refuse moving any occurrence of a rule based event
	 *
	 * The Occurrences follow the Events Calendar Pro rules: neither the first date nor
	 * another one moves, even when the freeze filter lets raw meta writes through. The
	 * refusal is recorded with the Freeze_Guard, which owns the notice.
	 *
	 * @test
	 */
	public function should_refuse_moving_any_occurrence_of_a_rule_based_event(): void {
		$post   = $this->given_a_frozen_rule_event( true );
		$rset   = Event::find( $post->ID, 'post_id' )->rset;
		$meta   = get_post_meta( $post->ID, '_EventRecurrence', true );
		$starts = $this->starts_of( $post->ID );

		self::$refusals = [];
		add_action( 'tec_events_recurrence_frozen_write_refused', [ self::class, 'capture_refusal' ], 10, 2 );
		add_filter( 'tec_events_recurrence_freeze_meta_write', '__return_false' );

		try {
			foreach ( [ '2050-01-03 09:00:00', '2050-01-10 09:00:00' ] as $start ) {
				$occurrence = $this->occurrence_on( $post->ID, $start );
				$before     = [ $occurrence->start_date, $occurrence->end_date, (bool) $occurrence->is_rdate ];

				$moved = tribe( Single_Occurrence_Update::class )->apply(
					$this->provisional_id( $occurrence ),
					new DateTimeImmutable( '2050-01-11 09:00:00', $this->tz() ),
					new DateTimeImmutable( '2050-01-11 10:00:00', $this->tz() )
				);

				$this->assertFalse( $moved, "The occurrence on {$start} must not move." );
				$row = Occurrence::find( (int) $occurrence->occurrence_id, 'occurrence_id' );
				$this->assertEquals( $before, [ $row->start_date, $row->end_date, (bool) $row->is_rdate ] );
			}
		} finally {
			remove_action( 'tec_events_recurrence_frozen_write_refused', [ self::class, 'capture_refusal' ], 10 );
			remove_filter( 'tec_events_recurrence_freeze_meta_write', '__return_false' );
		}

		$this->assertEquals( $rset, Event::find( $post->ID, 'post_id' )->rset );
		$this->assertEquals( $meta, get_post_meta( $post->ID, '_EventRecurrence', true ) );
		$this->assertEquals( $starts, $this->starts_of( $post->ID ) );
		$this->assertEquals( '2050-01-03 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( [ [ $post->ID, '_EventStartDate' ], [ $post->ID, '_EventStartDate' ] ], self::$refusals );
		// No notice of its own: the Freeze_Guard leaves the warning when the save completes.
		$this->assertFalse( get_transient( Single_Occurrence_Update::NOTICE_TRANSIENT . get_current_user_id() ) );
	}

	/**
	 * It should leave the frozen warning after a classic editor save of a rule based occurrence
	 *
	 * @test
	 */
	public function should_leave_the_frozen_warning_after_a_classic_editor_save_of_a_rule_based_occurrence(): void {
		$post           = $this->given_a_frozen_rule_event( true );
		$rset           = Event::find( $post->ID, 'post_id' )->rset;
		$occurrence     = $this->occurrence_on( $post->ID, '2050-01-17 09:00:00' );
		$id             = (int) $occurrence->occurrence_id;
		$provisional_id = $this->provisional_id( $occurrence );
		tribe( \TEC\Events\Custom_Tables\V1\Models\Provisional_Post::class )->hydrate_caches( [ $provisional_id ] );

		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		// Re-posting the occurrence's own dates, as an untouched save does: nothing to refuse, no notice.
		\Tribe__Events__API::saveEventMeta(
			$provisional_id,
			[
				'EventStartDate' => '2050-01-17',
				'EventStartTime' => '09:00:00',
				'EventEndDate'   => '2050-01-17',
				'EventEndTime'   => '10:00:00',
				'EventTimezone'  => 'America/Sao_Paulo',
			],
			get_post( $provisional_id )
		);

		$this->assertFalse( get_transient( Admin_Notice::TRANSIENT . get_current_user_id() ) );

		\Tribe__Events__API::saveEventMeta(
			$provisional_id,
			[
				'EventStartDate' => '2050-01-20',
				'EventStartTime' => '11:00:00',
				'EventEndDate'   => '2050-01-20',
				'EventEndTime'   => '12:00:00',
				'EventTimezone'  => 'America/Sao_Paulo',
			],
			get_post( $provisional_id )
		);

		// Nothing moved: not the occurrence, not the parent, not the rules.
		$this->assertEquals( '2050-01-17 09:00:00', Occurrence::find( $id, 'occurrence_id' )->start_date );
		$this->assertEquals( '2050-01-03 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( $rset, Event::find( $post->ID, 'post_id' )->rset );
		$this->assertEquals( [ '2050-01-03 09:00:00', '2050-01-10 09:00:00', '2050-01-17 09:00:00' ], $this->starts_of( $post->ID ) );
		// The user gets the Freeze_Guard warning, the one a refused Event date write leaves.
		$notice = get_transient( Admin_Notice::TRANSIENT . get_current_user_id() );
		$this->assertEquals( 'warning', $notice['type'] );
		$this->assertStringContainsString( 'were left unchanged', $notice['message'] );
	}

	/**
	 * It should apply a classic editor save of an occurrence to that occurrence only
	 *
	 * The regression this guards: the posted dates used to land on the parent Event
	 * through the provisional meta retargeting, moving the Event's own date.
	 *
	 * @test
	 */
	public function should_apply_a_classic_editor_save_of_an_occurrence_to_that_occurrence_only(): void {
		$post           = $this->given_a_dates_only_event();
		$occurrence     = $this->occurrence_on( $post->ID, '2050-01-17 09:00:00' );
		$id             = (int) $occurrence->occurrence_id;
		$provisional_id = $this->provisional_id( $occurrence );
		tribe( \TEC\Events\Custom_Tables\V1\Models\Provisional_Post::class )->hydrate_caches( [ $provisional_id ] );

		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$data = [
			'EventStartDate' => '2050-01-20',
			'EventStartTime' => '11:00:00',
			'EventEndDate'   => '2050-01-20',
			'EventEndTime'   => '12:00:00',
			'EventTimezone'  => 'America/Sao_Paulo',
		];

		\Tribe__Events__API::saveEventMeta( $provisional_id, $data, get_post( $provisional_id ) );

		// The parent kept its own date and every other occurrence.
		$this->assertEquals( '2050-01-03 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2050-01-03 10:00:00', get_post_meta( $post->ID, '_EventEndDate', true ) );
		$this->assertEquals( [ '2050-01-03 09:00:00', '2050-01-10 09:00:00', '2050-01-20 11:00:00' ], $this->starts_of( $post->ID ) );
		// The edited occurrence moved, keeping its ID.
		$row = Occurrence::find( $id, 'occurrence_id' );
		$this->assertEquals( '2050-01-20 11:00:00', $row->start_date );
		$this->assertEquals( '2050-01-20 12:00:00', $row->end_date );
		// The user gets told.
		$notice = get_transient( Single_Occurrence_Update::NOTICE_TRANSIENT . get_current_user_id() );
		$this->assertEquals( 'success', $notice['type'] );
		// The Dates_Service view of the event agrees.
		$this->assertCount( 3, tribe( Dates_Service::class )->get_dates( $post->ID ) );
	}

	/**
	 * It should apply a block editor save of an occurrence to that occurrence only
	 *
	 * @test
	 */
	public function should_apply_a_block_editor_save_of_an_occurrence_to_that_occurrence_only(): void {
		$post           = $this->given_a_dates_only_event();
		$occurrence     = $this->occurrence_on( $post->ID, '2050-01-10 09:00:00' );
		$id             = (int) $occurrence->occurrence_id;
		$provisional_id = $this->provisional_id( $occurrence );
		tribe( \TEC\Events\Custom_Tables\V1\Models\Provisional_Post::class )->hydrate_caches( [ $provisional_id ] );

		// What the REST meta fields write for the datetime block attributes.
		update_post_meta( $provisional_id, '_EventStartDate', '2050-01-13 09:00:00' );
		update_post_meta( $provisional_id, '_EventEndDate', '2050-01-13 10:00:00' );
		do_action( 'rest_after_insert_tribe_events', get_post( $provisional_id ), new \WP_REST_Request( 'PUT' ), false );

		$this->assertEquals( '2050-01-03 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2050-01-13 09:00:00', Occurrence::find( $id, 'occurrence_id' )->start_date );
		$this->assertEquals( [ '2050-01-03 09:00:00', '2050-01-13 09:00:00', '2050-01-17 09:00:00' ], $this->starts_of( $post->ID ) );
	}
}
