<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use DateTimeImmutable;
use DateTimeZone;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use WP_Post;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;

class Dates_GeneratorTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * Creates an Event and applies a dates-only RSET to it.
	 *
	 * @param array<int,array{start: DateTimeImmutable, end: DateTimeImmutable}> $dates The additional dates.
	 *
	 * @return WP_Post The created Event post.
	 */
	private function given_an_event_with_dates( array $dates ): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Dates Generator Test Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		$this->apply_dates( $post->ID, $dates );

		return $post;
	}

	/**
	 * Serializes and applies a dates-only RSET to an Event, regenerating its Occurrences.
	 *
	 * @param int                                                                $post_id The Event post ID.
	 * @param array<int,array{start: DateTimeImmutable, end: DateTimeImmutable}> $dates   The additional dates.
	 */
	private function apply_dates( int $post_id, array $dates ): void {
		$tz   = new DateTimeZone( 'America/Sao_Paulo' );
		$rset = Dates::serialize(
			new DateTimeImmutable( '2026-11-05 09:00:00', $tz ),
			new DateTimeImmutable( '2026-11-05 10:00:00', $tz ),
			$dates
		);

		Event::find( $post_id, 'post_id' )->update( [ 'rset' => $rset ] );
		// Clear the per-request match cache: each application models a separate request.
		wp_cache_delete( $post_id, 'tec_occurrence_matches' );
		Event::find( $post_id, 'post_id' )->occurrences()->save_occurrences();
	}

	private function date( string $start, string $end ): array {
		$tz = new DateTimeZone( 'America/Sao_Paulo' );

		return [
			'start' => new DateTimeImmutable( $start, $tz ),
			'end'   => new DateTimeImmutable( $end, $tz ),
		];
	}

	/**
	 * It should expand a dates only RSET into Occurrences
	 *
	 * @test
	 */
	public function should_expand_a_dates_only_rset_into_occurrences(): void {
		$post = $this->given_an_event_with_dates(
			[
				$this->date( '2026-11-12 09:00:00', '2026-11-12 10:00:00' ),
				$this->date( '2026-11-20 14:00:00', '2026-11-20 15:30:00' ),
			]
		);

		$occurrences = Occurrence::where( 'post_id', '=', $post->ID )
								->order_by( 'start_date', 'ASC' )
								->all();
		$occurrences = iterator_to_array( $occurrences, false );

		$this->assertCount( 3, $occurrences );
		$this->assertEquals(
			[ '2026-11-05 09:00:00', '2026-11-12 09:00:00', '2026-11-20 14:00:00' ],
			array_map( static fn( Occurrence $o ) => $o->start_date, $occurrences )
		);
		$this->assertEquals( '2026-11-20 15:30:00', end( $occurrences )->end_date );

		foreach ( $occurrences as $occurrence ) {
			$this->assertTrue( (bool) $occurrence->has_recurrence );
			$this->assertTrue( (bool) $occurrence->is_rdate );
			$this->assertEquals( 1, (int) $occurrence->sequence );
		}
	}

	/**
	 * It should keep Occurrence IDs stable and prune removed dates on edit
	 *
	 * @test
	 */
	public function should_keep_occurrence_ids_stable_and_prune_removed_dates_on_edit(): void {
		$post = $this->given_an_event_with_dates(
			[
				$this->date( '2026-11-12 09:00:00', '2026-11-12 10:00:00' ),
				$this->date( '2026-11-20 14:00:00', '2026-11-20 15:30:00' ),
			]
		);

		$before = [];
		foreach ( Occurrence::where( 'post_id', '=', $post->ID )->all() as $occurrence ) {
			$before[ $occurrence->start_date ] = $occurrence->occurrence_id;
		}

		// Drop Nov 20, add Nov 27.
		$this->apply_dates(
			$post->ID,
			[
				$this->date( '2026-11-12 09:00:00', '2026-11-12 10:00:00' ),
				$this->date( '2026-11-27 09:00:00', '2026-11-27 10:00:00' ),
			]
		);

		$after = [];
		foreach ( Occurrence::where( 'post_id', '=', $post->ID )->all() as $occurrence ) {
			$after[ $occurrence->start_date ] = $occurrence;
		}

		$this->assertCount( 3, $after );
		$this->assertArrayNotHasKey( '2026-11-20 14:00:00', $after );
		$this->assertArrayHasKey( '2026-11-27 09:00:00', $after );

		// Kept dates keep their Occurrence IDs, so provisional IDs and URLs stay stable.
		$this->assertEquals( $before['2026-11-05 09:00:00'], $after['2026-11-05 09:00:00']->occurrence_id );
		$this->assertEquals( $before['2026-11-12 09:00:00'], $after['2026-11-12 09:00:00']->occurrence_id );

		foreach ( $after as $occurrence ) {
			$this->assertEquals( 2, (int) $occurrence->sequence );
		}
	}

	/**
	 * It should resolve Occurrences through provisional post IDs
	 *
	 * @test
	 */
	public function should_resolve_occurrences_through_provisional_post_ids(): void {
		$post = $this->given_an_event_with_dates(
			[
				$this->date( '2026-11-12 09:00:00', '2026-11-12 10:00:00' ),
			]
		);

		$second = Occurrence::where( 'post_id', '=', $post->ID )
							->order_by( 'start_date', 'DESC' )
							->first();

		$provisional_id = $second->provisional_id;

		$this->assertIsInt( $provisional_id );
		$this->assertGreaterThan( $second->occurrence_id, $provisional_id );

		$provisional_post = get_post( $provisional_id );

		$this->assertInstanceOf( WP_Post::class, $provisional_post );
		$this->assertEquals( $post->post_title, $provisional_post->post_title );
		$this->assertInstanceOf( Occurrence::class, $provisional_post->_tec_occurrence );
		$this->assertEquals(
			'2026-11-12 09:00:00',
			get_post_meta( $provisional_id, '_EventStartDate', true )
		);
	}

	/**
	 * It should freeze the Occurrences of a rule based RSET when no rule engine is active
	 *
	 * @test
	 */
	public function should_freeze_the_occurrences_of_a_rule_based_rset_when_no_rule_engine_is_active(): void {
		$post = $this->given_an_event_with_dates(
			[
				$this->date( '2026-11-12 09:00:00', '2026-11-12 10:00:00' ),
			]
		);

		$before = [];
		foreach ( Occurrence::where( 'post_id', '=', $post->ID )->all() as $occurrence ) {
			$before[ $occurrence->occurrence_id ] = $occurrence->start_date;
		}

		$frozen = 0;
		add_action(
			'tec_events_recurrence_rules_frozen',
			static function () use ( &$frozen ) {
				$frozen ++;
			}
		);

		$rules_rset = "DTSTART;TZID=America/Sao_Paulo:20261105T090000\nRRULE:FREQ=WEEKLY;COUNT=5";
		Event::find( $post->ID, 'post_id' )->update( [ 'rset' => $rules_rset ] );
		wp_cache_delete( $post->ID, 'tec_occurrence_matches' );
		Event::find( $post->ID, 'post_id' )->occurrences()->save_occurrences();

		$after = [];
		foreach ( Occurrence::where( 'post_id', '=', $post->ID )->all() as $occurrence ) {
			$after[ $occurrence->occurrence_id ] = $occurrence->start_date;
		}

		$this->assertGreaterThanOrEqual( 1, $frozen );
		$this->assertEquals( $before, $after, 'Rule-based Occurrences must be preserved, not collapsed.' );
	}

	/**
	 * It should not interfere with single Occurrence Events
	 *
	 * @test
	 */
	public function should_not_interfere_with_single_occurrence_events(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Plain Single Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		$occurrences = iterator_to_array( Occurrence::where( 'post_id', '=', $post->ID )->all(), false );

		$this->assertCount( 1, $occurrences );
		$this->assertFalse( tribe_is_recurring_event( $post->ID ) );
	}

	/**
	 * It should report multi date Events as recurring
	 *
	 * @test
	 */
	public function should_report_multi_date_events_as_recurring(): void {
		$post = $this->given_an_event_with_dates(
			[
				$this->date( '2026-11-12 09:00:00', '2026-11-12 10:00:00' ),
			]
		);

		$this->assertTrue( tribe_is_recurring_event( $post->ID ) );

		$start_dates = tribe_get_recurrence_start_dates( $post->ID );

		$this->assertEquals( [ '2026-11-05 09:00:00', '2026-11-12 09:00:00' ], $start_dates );
	}

	/**
	 * It should not freeze when an earlier generator claimed the event
	 *
	 * @test
	 */
	public function should_not_freeze_when_an_earlier_generator_claimed_the_event(): void {
		$post  = $this->given_an_event_with_dates(
			[
				$this->date( '2026-11-12 09:00:00', '2026-11-12 10:00:00' ),
			]
		);
		$event = Event::find( $post->ID, 'post_id' );

		$claimed = ( static function () {
			yield from [];
		} )();

		$fired_before = did_action( 'tec_events_recurrence_rules_frozen' );

		// A generator provided by an earlier filter (e.g. a rule engine) wins.
		$this->assertSame( $claimed, tribe( Engine_Provider::class )->get_freeze_generator( $claimed, $event ) );
		$this->assertEquals( $fired_before, did_action( 'tec_events_recurrence_rules_frozen' ) );
	}

	/**
	 * It should not freeze events without an rset
	 *
	 * @test
	 */
	public function should_not_freeze_events_without_an_rset(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'No RSET Event',
				'status'     => 'publish',
				'start_date' => '2026-11-06 09:00:00',
				'end_date'   => '2026-11-06 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		$event        = Event::find( $post->ID, 'post_id' );
		$fired_before = did_action( 'tec_events_recurrence_rules_frozen' );

		// No RSET: the default single Occurrence logic applies, nothing is frozen.
		$this->assertNull( tribe( Engine_Provider::class )->get_freeze_generator( null, $event ) );
		$this->assertEquals( $fired_before, did_action( 'tec_events_recurrence_rules_frozen' ) );
	}

	/**
	 * It should decline generating from a rule based rset
	 *
	 * @test
	 */
	public function should_decline_generating_from_a_rule_based_rset(): void {
		$post = $this->given_an_event_with_dates(
			[
				$this->date( '2026-11-12 09:00:00', '2026-11-12 10:00:00' ),
			]
		);

		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=UTC:20261105T090000\nRRULE:FREQ=WEEKLY;COUNT=5" ]
		);
		$event = Event::find( $post->ID, 'post_id' );

		// The dates generator only handles dates-only RSETs: rule-based ones are left alone.
		$this->assertNull( tribe( Engine_Provider::class )->get_dates_generator( null, $event ) );
	}
}
