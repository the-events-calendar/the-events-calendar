<?php

namespace TEC\Events\Recurrence;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use WP_Post;

class Authoring_GuardTest extends WPTestCase {
	/**
	 * @before
	 */
	public function activate_recurrence_engine(): void {
		add_filter( 'tec_events_recurrence_enabled', '__return_true' );
		tribe()->setVar( 'ct1_fully_activated', true );
		// The WordPress test case restores the hooks state after each test: force a re-registration.
		tribe()->setVar( Controller::class . '_registered', false );
		tribe( Controller::class )->register();
		// Reset the Model static extensions cache: it may have been locked before the engine registered.
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	/**
	 * @after
	 */
	public function reset_registration_state(): void {
		remove_all_filters( 'tec_events_recurrence_enabled' );
		// Symmetric cleanup: no engine hook or extended model state leaks into other tests.
		tribe( Controller::class )->unregister();
		tribe()->setVar( Controller::class . '_registered', false );
		$extensions = new \ReflectionProperty( \TEC\Events\Custom_Tables\V1\Models\Model::class, 'extensions' );
		$extensions->setAccessible( true );
		$extensions->setValue( null, [] );
	}

	private function given_an_event(): WP_Post {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Authoring Guard Test Event',
				'status'     => 'publish',
				'start_date' => '2026-11-05 09:00:00',
				'end_date'   => '2026-11-05 10:00:00',
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();

		$this->assertInstanceOf( WP_Post::class, $post );

		return $post;
	}

	private function given_a_rset_only_rrule_event(): WP_Post {
		$post = $this->given_an_event();

		Event::find( $post->ID, 'post_id' )->update(
			[ 'rset' => "DTSTART;TZID=America/Sao_Paulo:20261105T090000\nRRULE:FREQ=WEEKLY;COUNT=10" ]
		);

		return $post;
	}

	private function given_a_dates_only_rset_event_without_meta(): WP_Post {
		$post = $this->given_an_event();

		Event::find( $post->ID, 'post_id' )->update(
			[
				'rset' => "DTSTART;TZID=America/Sao_Paulo:20261105T090000\n"
						. "RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261112T090000/20261112T100000\n"
						. 'RDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20261105T090000/20261105T100000',
			]
		);

		return $post;
	}

	/**
	 * It should not lock plain and dates only events
	 *
	 * @test
	 */
	public function should_not_lock_plain_and_dates_only_events(): void {
		$guard = tribe( Authoring_Guard::class );

		$plain = $this->given_an_event();
		$this->assertFalse( $guard->is_rule_locked( $plain->ID ) );

		$dates = $this->given_an_event();
		tribe( Dates_Service::class )->set_dates( $dates->ID, [ [ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ] ] );
		$this->assertFalse( $guard->is_rule_locked( $dates->ID ) );

		$rset_dates = $this->given_a_dates_only_rset_event_without_meta();
		$this->assertFalse( $guard->is_rule_locked( $rset_dates->ID ) );
	}

	/**
	 * It should lock rule based events with and without authored meta
	 *
	 * @test
	 */
	public function should_lock_rule_based_events_with_and_without_authored_meta(): void {
		$guard = tribe( Authoring_Guard::class );

		$meta_rules = $this->given_an_event();
		update_post_meta( $meta_rules->ID, '_EventRecurrence', [ 'rules' => [ [ 'type' => 'Weekly' ] ] ] );
		$this->assertTrue( $guard->is_rule_locked( $meta_rules->ID ) );

		$rset_only = $this->given_a_rset_only_rrule_event();
		$this->assertTrue( $guard->is_rule_locked( $rset_only->ID ) );
	}

	/**
	 * It should refuse setting dates on a rule locked event
	 *
	 * @test
	 */
	public function should_refuse_setting_dates_on_a_rule_locked_event(): void {
		$post = $this->given_a_rset_only_rrule_event();
		$rset = (string) Event::find( $post->ID, 'post_id' )->rset;

		$set = tribe( Dates_Service::class )->set_dates( $post->ID, [ [ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ] ] );

		$this->assertFalse( $set );
		$this->assertEquals( $rset, (string) Event::find( $post->ID, 'post_id' )->rset );
		$this->assertEmpty( get_post_meta( $post->ID, '_EventRecurrence', true ) );
	}

	/**
	 * It should return the authored periods from the canonical meta
	 *
	 * @test
	 */
	public function should_return_the_authored_periods_from_the_canonical_meta(): void {
		$post = $this->given_an_event();
		tribe( Dates_Service::class )->set_dates(
			$post->ID,
			[
				// An added date EARLIER than the event date: rows must not be positional.
				[ 'start' => '2026-11-01 08:00:00', 'end' => '2026-11-01 09:00:00' ],
				[ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ],
			]
		);

		$periods = tribe( Authoring_Guard::class )->get_authored_periods( $post->ID );

		$this->assertCount( 2, $periods );
		$starts = array_map( static fn( array $period ): string => $period['start']->format( 'Y-m-d H:i:s' ), $periods );
		sort( $starts );
		$this->assertEquals( [ '2026-11-01 08:00:00', '2026-11-12 09:00:00' ], $starts );
	}

	/**
	 * It should derive the periods from a dates only rset without meta
	 *
	 * @test
	 */
	public function should_derive_the_periods_from_a_dates_only_rset_without_meta(): void {
		$post = $this->given_a_dates_only_rset_event_without_meta();

		$periods = tribe( Authoring_Guard::class )->get_authored_periods( $post->ID );

		// The DTSTART period (the Event's own date) is excluded.
		$this->assertCount( 1, $periods );
		$this->assertEquals( '2026-11-12 09:00:00', $periods[0]['start']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * It should return no periods for rule locked events
	 *
	 * @test
	 */
	public function should_return_no_periods_for_rule_locked_events(): void {
		$post = $this->given_a_rset_only_rrule_event();

		$this->assertEquals( [], tribe( Authoring_Guard::class )->get_authored_periods( $post->ID ) );
	}

	/**
	 * It should detect provisional occurrence ids
	 *
	 * @test
	 */
	public function should_detect_provisional_occurrence_ids(): void {
		$post = $this->given_an_event();
		tribe( Dates_Service::class )->set_dates( $post->ID, [ [ 'start' => '2026-11-12 09:00:00', 'end' => '2026-11-12 10:00:00' ] ] );

		$occurrence = Occurrence::where( 'post_id', '=', $post->ID )->first();
		$this->assertInstanceOf( Occurrence::class, $occurrence );
		$provisional_id = tribe( \TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator::class )->provide_id( $occurrence->occurrence_id );

		$guard = tribe( Authoring_Guard::class );
		$this->assertTrue( $guard->is_occurrence_edit( $provisional_id ) );
		$this->assertFalse( $guard->is_occurrence_edit( $post->ID ) );
	}

	/**
	 * It should summarize the scheduled dates counting all and listing the next upcoming ones
	 *
	 * @test
	 */
	public function should_summarize_the_scheduled_dates(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Summary Test Event',
				'status'     => 'publish',
				'start_date' => date( 'Y-m-d 09:00:00', strtotime( '+10 days' ) ),
				'end_date'   => date( 'Y-m-d 10:00:00', strtotime( '+10 days' ) ),
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();
		$this->assertInstanceOf( WP_Post::class, $post );

		tribe( Dates_Service::class )->set_dates(
			$post->ID,
			[
				[ 'start' => date( 'Y-m-d 09:00:00', strtotime( '+20 days' ) ), 'end' => date( 'Y-m-d 10:00:00', strtotime( '+20 days' ) ) ],
				[ 'start' => date( 'Y-m-d 09:00:00', strtotime( '+30 days' ) ), 'end' => date( 'Y-m-d 10:00:00', strtotime( '+30 days' ) ) ],
				[ 'start' => date( 'Y-m-d 09:00:00', strtotime( '+40 days' ) ), 'end' => date( 'Y-m-d 10:00:00', strtotime( '+40 days' ) ) ],
			]
		);

		$summary = tribe( Authoring_Guard::class )->get_dates_summary( $post->ID );

		$this->assertEquals( 4, $summary['count'] );
		$this->assertCount( 3, $summary['next_dates'] );
		$this->assertEquals(
			[
				date( 'Y-m-d 09:00:00', strtotime( '+10 days' ) ),
				date( 'Y-m-d 09:00:00', strtotime( '+20 days' ) ),
				date( 'Y-m-d 09:00:00', strtotime( '+30 days' ) ),
			],
			array_map( static fn( $date ) => $date->format( 'Y-m-d H:i:s' ), $summary['next_dates'] )
		);
	}

	/**
	 * It should fall back to the final dates when every date is in the past
	 *
	 * @test
	 */
	public function should_fall_back_to_the_final_dates_when_all_past(): void {
		$post = tribe_events()->set_args(
			[
				'title'      => 'Past Summary Test Event',
				'status'     => 'publish',
				'start_date' => date( 'Y-m-d 09:00:00', strtotime( '-40 days' ) ),
				'end_date'   => date( 'Y-m-d 10:00:00', strtotime( '-40 days' ) ),
				'timezone'   => 'America/Sao_Paulo',
			]
		)->create();
		$this->assertInstanceOf( WP_Post::class, $post );

		tribe( Dates_Service::class )->set_dates(
			$post->ID,
			[
				[ 'start' => date( 'Y-m-d 09:00:00', strtotime( '-30 days' ) ), 'end' => date( 'Y-m-d 10:00:00', strtotime( '-30 days' ) ) ],
			]
		);

		$summary = tribe( Authoring_Guard::class )->get_dates_summary( $post->ID );

		$this->assertEquals( 2, $summary['count'] );
		$this->assertEquals(
			[
				date( 'Y-m-d 09:00:00', strtotime( '-40 days' ) ),
				date( 'Y-m-d 09:00:00', strtotime( '-30 days' ) ),
			],
			array_map( static fn( $date ) => $date->format( 'Y-m-d H:i:s' ), $summary['next_dates'] )
		);
	}

	/**
	 * It should return an empty summary when the Custom Tables are not fully activated
	 *
	 * @test
	 */
	public function should_return_an_empty_summary_without_ct1(): void {
		$post = $this->given_an_event();

		tribe()->setVar( 'ct1_fully_activated', false );
		$summary = tribe( Authoring_Guard::class )->get_dates_summary( $post->ID );
		tribe()->setVar( 'ct1_fully_activated', true );

		$this->assertEquals( [ 'count' => 0, 'next_dates' => [] ], $summary );
	}
}
