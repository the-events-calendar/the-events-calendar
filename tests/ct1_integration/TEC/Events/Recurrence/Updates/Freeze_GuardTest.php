<?php

namespace TEC\Events\Recurrence\Updates;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Recurrence\Settings;
use Tribe\Events\Test\Traits\With_Recurrence_Engine;
use WP_Post;

class Freeze_GuardTest extends WPTestCase {
	use With_Recurrence_Engine;

	/**
	 * The refusals captured from the action, as `[ post_id, meta_key ]` pairs.
	 *
	 * Static and captured by a static method: closure callbacks leak across tests in this suite.
	 *
	 * @var array<int,array{0: int, 1: string}>
	 */
	public static array $refusals = [];

	public static function capture_refusal( $post_id, $meta_key ): void {
		self::$refusals[] = [ (int) $post_id, (string) $meta_key ];
	}

	/**
	 * @before
	 */
	public function capture_refusals(): void {
		self::$refusals = [];
		add_action( 'tec_events_recurrence_frozen_write_refused', [ self::class, 'capture_refusal' ], 10, 2 );
	}

	/**
	 * @after
	 */
	public function release_capture(): void {
		remove_action( 'tec_events_recurrence_frozen_write_refused', [ self::class, 'capture_refusal' ], 10 );
		remove_all_filters( 'tec_events_recurrence_freeze_meta_write' );
		remove_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
		tribe_remove_option( Settings::LOCK_OPTION );
		// The per-test rollback runs before this: drop the settings cache so the next read reloads the restored DB.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, [] );
		delete_transient( Admin_Notice::TRANSIENT . get_current_user_id() );
	}

	/**
	 * A rule-based Event on 2050-01-03 09:00 with rows on 2050-01-10 and 2050-01-17, its rules frozen.
	 */
	private function given_a_rule_locked_event(): WP_Post {
		$post = $this->given_a_multi_date_event(
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

		delete_post_meta( $post->ID, '_EventRecurrence' );
		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20500103T090000\nRRULE:FREQ=WEEKLY;COUNT=3;BYDAY=MO" ]
		);

		return $post;
	}

	/**
	 * @return array<string,string> The date and recurrence columns of the Event row.
	 */
	private function event_row( int $post_id ): array {
		$event = Event::find( $post_id, 'post_id' );

		return [
			'start_date'     => (string) $event->start_date,
			'end_date'       => (string) $event->end_date,
			'start_date_utc' => (string) $event->start_date_utc,
			'end_date_utc'   => (string) $event->end_date_utc,
			'timezone'       => (string) $event->timezone,
			'duration'       => (string) $event->duration,
			'rset'           => (string) $event->rset,
		];
	}

	/** @test */
	public function should_allow_rule_writes_only_while_an_external_editor_owns_updates(): void {
		$post  = $this->given_a_rule_locked_event();
		$guard = tribe( Freeze_Guard::class );
		add_filter( 'tec_events_recurrence_updates_handled', '__return_true' );

		$this->assertFalse( $guard->is_frozen( $post->ID ) );
		$this->assertTrue( tribe( \TEC\Events\Recurrence\Authoring_Guard::class )->is_rule_locked( $post->ID ) );
		update_post_meta( $post->ID, '_EventStartDate', '2050-02-01 09:00:00' );
		$rules = [ 'rules' => [ [ 'type' => 'Weekly' ] ] ];
		add_post_meta( $post->ID, '_EventRecurrence', $rules );
		$this->assertSame( '2050-02-01 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertSame( $rules, get_post_meta( $post->ID, '_EventRecurrence', true ) );
		delete_post_meta( $post->ID, '_EventRecurrence' );
		$this->assertSame( '', get_post_meta( $post->ID, '_EventRecurrence', true ) );
		$this->assertSame( [], $guard->get_refused( $post->ID ) );

		remove_filter( 'tec_events_recurrence_updates_handled', '__return_true' );
		$this->assertTrue( $guard->is_frozen( $post->ID ) );
		update_post_meta( $post->ID, '_EventStartDate', '2050-03-01 09:00:00' );
		$this->assertSame( '2050-02-01 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
	}

	/**
	 * It should register its hooks at minus five
	 *
	 * @test
	 */
	public function should_register_its_hooks_at_minus_five(): void {
		$guard = tribe( Freeze_Guard::class );

		$this->assertEquals( -5, has_filter( 'update_post_metadata', [ $guard, 'refuse_update' ] ) );
		$this->assertEquals( -5, has_filter( 'add_post_metadata', [ $guard, 'refuse_add' ] ) );
		$this->assertEquals( -5, has_filter( 'delete_post_metadata', [ $guard, 'refuse_delete' ] ) );
		$this->assertEquals( 30, has_action( 'tribe_events_update_meta', [ $guard, 'on_classic_save' ] ) );

		$guard->unregister();

		$this->assertFalse( has_filter( 'update_post_metadata', [ $guard, 'refuse_update' ] ) );
		$this->assertFalse( has_action( 'tribe_events_update_meta', [ $guard, 'on_classic_save' ] ) );

		$guard->register();

		$this->assertEquals( -5, has_filter( 'update_post_metadata', [ $guard, 'refuse_update' ] ) );
	}

	/**
	 * It should refuse date meta updates on a rule locked event
	 *
	 * @test
	 */
	public function should_refuse_date_meta_updates_on_a_rule_locked_event(): void {
		$post   = $this->given_a_rule_locked_event();
		$before = [];

		foreach ( Freeze_Guard::FROZEN_META_KEYS as $key ) {
			$before[ $key ] = get_post_meta( $post->ID, $key, true );
		}
		$row_before = $this->event_row( $post->ID );

		$this->assertTrue( update_post_meta( $post->ID, '_EventStartDate', '2050-02-01 09:00:00' ), 'A swallowed write reports success.' );
		$this->assertTrue( update_post_meta( $post->ID, '_EventEndDate', '2050-02-01 10:00:00' ) );
		$this->assertTrue( update_post_meta( $post->ID, '_EventStartDateUTC', '2050-02-01 12:00:00' ) );
		$this->assertTrue( update_post_meta( $post->ID, '_EventEndDateUTC', '2050-02-01 13:00:00' ) );
		$this->assertTrue( update_post_meta( $post->ID, '_EventAllDay', 'yes' ) );
		$this->assertTrue( update_post_meta( $post->ID, '_EventTimezone', 'Europe/Paris' ) );
		$this->assertTrue( update_post_meta( $post->ID, '_EventDuration', 7200 ) );
		$this->assertTrue( update_post_meta( $post->ID, '_EventRecurrence', [ 'rules' => [ [ 'type' => 'Daily' ] ] ] ) );

		foreach ( Freeze_Guard::FROZEN_META_KEYS as $key ) {
			$this->assertEquals( $before[ $key ], get_post_meta( $post->ID, $key, true ), "{$key} must not change." );
		}
		$this->assertEquals( $row_before, $this->event_row( $post->ID ), 'The Custom Tables row must not be re-committed.' );

		$refused_keys = array_column( self::$refusals, 1 );
		$this->assertEqualsCanonicalizing( Freeze_Guard::FROZEN_META_KEYS, $refused_keys );
		$this->assertEqualsCanonicalizing( Freeze_Guard::FROZEN_META_KEYS, tribe( Freeze_Guard::class )->get_refused( $post->ID ) );
	}

	/**
	 * It should refuse adds and deletes on a rule locked event
	 *
	 * @test
	 */
	public function should_refuse_adds_and_deletes_on_a_rule_locked_event(): void {
		$post  = $this->given_a_rule_locked_event();
		$start = get_post_meta( $post->ID, '_EventStartDate', true );

		$this->assertTrue( delete_post_meta( $post->ID, '_EventStartDate' ) );
		$this->assertEquals( $start, get_post_meta( $post->ID, '_EventStartDate', true ) );

		$this->assertTrue( (bool) add_post_meta( $post->ID, '_EventAllDay', 'yes' ) );
		$this->assertEmpty( get_post_meta( $post->ID, '_EventAllDay', true ) );

		$this->assertEqualsCanonicalizing( [ '_EventStartDate', '_EventAllDay' ], array_column( self::$refusals, 1 ) );
	}

	/**
	 * It should refuse through a provisional id
	 *
	 * @test
	 */
	public function should_refuse_through_a_provisional_id(): void {
		$post       = $this->given_a_rule_locked_event();
		$occurrence = Occurrence::where( 'post_id', '=', $post->ID )->order_by( 'start_date', 'DESC' )->first();
		$provisional_id = tribe( ID_Generator::class )->provide_id( $occurrence->occurrence_id );

		// The timezone is not among the keys the single Occurrence update buffers: the guard sees it.
		$this->assertTrue( update_post_meta( $provisional_id, '_EventTimezone', 'Europe/Paris' ) );

		$this->assertEquals( 'America/Sao_Paulo', get_post_meta( $post->ID, '_EventTimezone', true ) );
		$this->assertEquals( [ [ $post->ID, '_EventTimezone' ] ], self::$refusals );
	}

	/**
	 * It should record refusals handed over through a provisional id
	 *
	 * The single Occurrence update hands the guard the moves it refuses on a rule-based
	 * Event; they land on the Event and fire the action like the guard's own refusals.
	 *
	 * @test
	 */
	public function should_record_refusals_handed_over_through_a_provisional_id(): void {
		$post           = $this->given_a_rule_locked_event();
		$occurrence     = Occurrence::where( 'post_id', '=', $post->ID )->order_by( 'start_date', 'DESC' )->first();
		$provisional_id = tribe( ID_Generator::class )->provide_id( $occurrence->occurrence_id );
		$guard          = tribe( Freeze_Guard::class );

		$guard->record_refusal( $provisional_id, '_EventStartDate' );

		$this->assertEquals( [ '_EventStartDate' ], $guard->get_refused( $post->ID ) );
		$this->assertEquals( [ '_EventStartDate' ], $guard->get_refused( $provisional_id ) );
		$this->assertEquals( [ [ $post->ID, '_EventStartDate' ] ], self::$refusals );

		// The classic save notice follows, as for the guard's own refusals.
		do_action( 'tribe_events_update_meta', $provisional_id, [], get_post( $post->ID ) );
		$notice = get_transient( Admin_Notice::TRANSIENT . get_current_user_id() );
		$this->assertEquals( 'warning', $notice['type'] );
	}

	/**
	 * It should let writes through on dates only and single events
	 *
	 * @test
	 */
	public function should_let_writes_through_on_dates_only_and_single_events(): void {
		$dates  = $this->given_a_multi_date_event();
		$single = tribe_events()->set_args(
			[
				'title'      => 'Single',
				'status'     => 'publish',
				'start_date' => '2050-03-01 09:00:00',
				'end_date'   => '2050-03-01 10:00:00',
				'timezone'   => 'UTC',
			]
		)->create();

		update_post_meta( $dates->ID, '_EventTimezone', 'Europe/Paris' );
		update_post_meta( $single->ID, '_EventTimezone', 'Europe/Paris' );

		$this->assertEquals( 'Europe/Paris', get_post_meta( $dates->ID, '_EventTimezone', true ) );
		$this->assertEquals( 'Europe/Paris', get_post_meta( $single->ID, '_EventTimezone', true ) );
		$this->assertEquals( [], self::$refusals );
	}

	/**
	 * It should let writes through inside allow
	 *
	 * @test
	 */
	public function should_let_writes_through_inside_allow(): void {
		$post  = $this->given_a_rule_locked_event();
		$guard = tribe( Freeze_Guard::class );

		$result = $guard->allow(
			static function () use ( $post, $guard ) {
				// Nested: the outer suspension holds.
				return $guard->allow(
					static function () use ( $post ) {
						return update_post_meta( $post->ID, '_EventTimezone', 'Europe/Paris' );
					}
				);
			}
		);

		$this->assertTrue( (bool) $result );
		$this->assertEquals( 'Europe/Paris', get_post_meta( $post->ID, '_EventTimezone', true ) );
		$this->assertEquals( [], self::$refusals );

		// The suspension is over.
		update_post_meta( $post->ID, '_EventTimezone', 'Europe/Rome' );
		$this->assertEquals( 'Europe/Paris', get_post_meta( $post->ID, '_EventTimezone', true ) );
		$this->assertCount( 1, self::$refusals );
	}

	/**
	 * It should not record a refusal for an unchanged value
	 *
	 * @test
	 */
	public function should_not_record_a_refusal_for_an_unchanged_value(): void {
		$post = $this->given_a_rule_locked_event();

		// A plain save re-posts the current dates.
		update_post_meta( $post->ID, '_EventStartDate', get_post_meta( $post->ID, '_EventStartDate', true ) );
		update_post_meta( $post->ID, '_EventDuration', (int) get_post_meta( $post->ID, '_EventDuration', true ) );

		$this->assertEquals( [], self::$refusals );
		$this->assertEquals( [], tribe( Freeze_Guard::class )->get_refused( $post->ID ) );
	}

	/**
	 * It should honor the freeze filter
	 *
	 * @test
	 */
	public function should_honor_the_freeze_filter(): void {
		$post = $this->given_a_rule_locked_event();
		add_filter( 'tec_events_recurrence_freeze_meta_write', '__return_false' );

		update_post_meta( $post->ID, '_EventTimezone', 'Europe/Paris' );

		$this->assertEquals( 'Europe/Paris', get_post_meta( $post->ID, '_EventTimezone', true ) );
	}

	/**
	 * It should set the classic notice after a refused save
	 *
	 * @test
	 */
	public function should_set_the_classic_notice_after_a_refused_save(): void {
		$post = $this->given_a_rule_locked_event();

		update_post_meta( $post->ID, '_EventStartDate', '2050-02-01 09:00:00' );
		do_action( 'tribe_events_update_meta', $post->ID, [], get_post( $post->ID ) );

		$notice = get_transient( Admin_Notice::TRANSIENT . get_current_user_id() );
		$this->assertEquals( 'warning', $notice['type'] );
		$this->assertStringContainsString( 'were left unchanged', $notice['message'] );
		$this->assertStringContainsString( 'tab=' . Settings::TAB_SLUG, $notice['message'], 'With the lock on, the notice links to the setting.' );

		delete_transient( Admin_Notice::TRANSIENT . get_current_user_id() );
		tribe_update_option( Settings::LOCK_OPTION, false );
		do_action( 'tribe_events_update_meta', $post->ID, [], get_post( $post->ID ) );

		$notice = get_transient( Admin_Notice::TRANSIENT . get_current_user_id() );
		$this->assertStringContainsString( 'Event Dates section', $notice['message'] );
	}

	/**
	 * It should not set a notice after a clean save
	 *
	 * @test
	 */
	public function should_not_set_a_notice_after_a_clean_save(): void {
		$post = $this->given_a_rule_locked_event();

		do_action( 'tribe_events_update_meta', $post->ID, [], get_post( $post->ID ) );

		$this->assertFalse( get_transient( Admin_Notice::TRANSIENT . get_current_user_id() ) );
	}

	/**
	 * It should keep an orm date update from moving a rule locked event
	 *
	 * @test
	 */
	public function should_keep_an_orm_date_update_from_moving_a_rule_locked_event(): void {
		$post = $this->given_a_rule_locked_event();

		tribe_events()->where( 'post__in', [ $post->ID ] )->set( 'start_date', '2050-02-01 09:00:00' )->set( 'end_date', '2050-02-01 10:00:00' )->save();

		$this->assertEquals( '2050-01-03 09:00:00', get_post_meta( $post->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2050-01-03 09:00:00', Event::find( $post->ID, 'post_id' )->start_date );
		$this->assertCount( 3, iterator_to_array( Occurrence::where( 'post_id', '=', $post->ID )->all(), false ) );
	}
}
