<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;

class UpdateTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
		$this->factory()->event = new Event();
	}

	public function meta_key_aliases() {
		return [
			'start_date'     => [ 'start_date', '_EventStartDate', '2018-09-30 09:00:00' ],
			'start_date_utc' => [ 'start_date_utc', '_EventStartDateUTC', '2018-10-01 09:00:00' ],
			'end_date'       => [ 'end_date', '_EventEndDate', '2018-10-31 11:00:00' ],
			'end_date_utc'   => [ 'end_date_utc', '_EventEndDateUTC', '2018-10-31 11:00:00' ],
			'duration'       => [ 'duration', '_EventDuration', '14400' ],
			'all_day'        => [ 'all_day', '_EventAllDay', 'yes' ],
			'timezone'       => [ 'timezone', '_EventTimezone', 'Europe/Paris' ],
		];
	}

	/**
	 * It should allow updating some event meta with aliases
	 *
	 * @test
	 * @dataProvider meta_key_aliases
	 */
	public function should_allow_updating_some_event_meta_with_aliases( $alias, $meta_key, $value ) {
		$event = $this->factory()->event->create( [ 'when' => '2018-01-12 09:00:00', 'duration' => YEAR_IN_SECONDS ] );

		tribe_events()->set( $alias, $value )->save();

		$this->assertEquals( $value, get_post_meta( $event, $meta_key, true ) );
	}

	/**
	 * It should set the UTC date when setting the dates
	 *
	 * @test
	 */
	public function should_set_the_utc_date_when_setting_the_dates() {
		list( $start_date, $end_date, $event ) = $this->create_test_event();

		// Move the start forward 1 hour and the end by 2.
		tribe_events()
			->where( 'post__in', [ $event ] )
			->set( 'start_date', $start_date->add( new \DateInterval( 'PT1H' ) )->format( 'Y-m-d H:i:s' ) )
			->set( 'end_date', $end_date->add( new \DateInterval( 'PT2H' ) )->format( 'Y-m-d H:i:s' ) )
			->save();

		$this->assertEquals( '2018-01-15 11:00:00', get_post_meta( $event, '_EventStartDate', true ) );
		$this->assertEquals( '2018-01-15 16:00:00', get_post_meta( $event, '_EventStartDateUTC', true ) );
		$this->assertEquals( '2018-01-15 15:00:00', get_post_meta( $event, '_EventEndDate', true ) );
		$this->assertEquals( '2018-01-15 20:00:00', get_post_meta( $event, '_EventEndDateUTC', true ) );
		$this->assertEquals( 4 * HOUR_IN_SECONDS, get_post_meta( $event, '_EventDuration', true ) );
	}

	/**
	 *
	 *
	 * @since 4.9.2
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function create_test_event(): array {
		$three_hours          = 10800;
		$start                = '2018-01-15 10:00:00';
		$start_utc            = '2018-01-15 15:00:00';
		$end                  = '2018-01-15 13:00:00';
		$end_utc              = '2018-01-15 18:00:00';
		$start_date           = new \DateTime( $start, new \DateTimeZone( 'America/New_York' ) );
		$start_date_utc       = new \DateTime( $start_utc, new \DateTimeZone( 'UTC' ) );
		$three_hours_interval = new \DateInterval( 'PT3H' );
		$end_date             = clone $start_date;
		$end_date->add( $three_hours_interval );
		$end_date_utc = clone $start_date_utc;
		$end_date_utc->add( $three_hours_interval );
		$event = $this->factory()->event->create( [
			'when'       => $start_utc,
			'duration'   => $three_hours,
			'utc_offset' => - 5,
			'meta_input' => [
				'_EventTimezone' => 'America/New_York',
			]
		] );
		$this->assertEquals( $start, get_post_meta( $event, '_EventStartDate', true ) );
		$this->assertEquals( $start_utc, get_post_meta( $event, '_EventStartDateUTC', true ) );
		$this->assertEquals( $end, get_post_meta( $event, '_EventEndDate', true ) );
		$this->assertEquals( $end_utc, get_post_meta( $event, '_EventEndDateUTC', true ) );

		return array( $start_date, $end_date, $event );
	}

	/**
	 * It should not update the dates and duration if start and end do not make sense
	 *
	 * @test
	 */
	public function should_not_update_the_dates_and_duration_if_start_and_end_do_not_make_sense() {
		list( $start_date, $end_date, $event ) = $this->create_test_event();

		// Try to set start+10 and end-10 hours.
		tribe_events()
			->where( 'post__in', [ $event ] )
			->set( 'start_date', $start_date->add( new \DateInterval( 'PT10H' ) )->format( 'Y-m-d H:i:s' ) )
			->set( 'end_date', $end_date->sub( new \DateInterval( 'PT10H' ) )->format( 'Y-m-d H:i:s' ) )
			->save();

		$this->assertEquals( '2018-01-15 10:00:00', get_post_meta( $event, '_EventStartDate', true ) );
		$this->assertEquals( '2018-01-15 15:00:00', get_post_meta( $event, '_EventStartDateUTC', true ) );
		$this->assertEquals( '2018-01-15 13:00:00', get_post_meta( $event, '_EventEndDate', true ) );
		$this->assertEquals( '2018-01-15 18:00:00', get_post_meta( $event, '_EventEndDateUTC', true ) );
		$this->assertEquals( 3 * HOUR_IN_SECONDS, get_post_meta( $event, '_EventDuration', true ) );
	}

	/**
	 * It should not set the venue if invalid
	 *
	 * @test
	 */
	public function should_not_set_the_venue_if_invalid() {
		list( $start_date, $end_date, $event ) = $this->create_test_event();

		tribe_events()
			->where( 'post__in', [ $event ] )
			->set( 'venue', 2389 )
			->save();

		$this->assertEmpty( get_post_meta( $event, '_EventVenueID', true ) );
	}

	/**
	 * It should not set the organizer if invalid
	 *
	 * @test
	 */
	public function should_not_set_the_organizer_if_invalid() {
		list( $start_date, $end_date, $event ) = $this->create_test_event();

		tribe_events()
			->where( 'post__in', [ $event ] )
			->set( 'organizer', 2389 )
			->save();

		$this->assertEmpty( get_post_meta( $event, '_EventOrganizerID', true ) );
	}

	/**
	 * It should update UTC time when setting timezone
	 *
	 * @test
	 */
	public function should_update_utc_time_when_setting_timezone() {
		list( $start_date, $end_date, $event ) = $this->create_test_event();

		tribe_events()
			->where( 'post__in', [ $event ] )
			->set( 'timezone', 'Indian/Mayotte' )
			->save();

		$this->assertEquals( '2018-01-15 10:00:00', get_post_meta( $event, '_EventStartDate', true ) );
		$this->assertEquals( '2018-01-15 07:00:00', get_post_meta( $event, '_EventStartDateUTC', true ) );
		$this->assertEquals( '2018-01-15 13:00:00', get_post_meta( $event, '_EventEndDate', true ) );
		$this->assertEquals( '2018-01-15 10:00:00', get_post_meta( $event, '_EventEndDateUTC', true ) );
		$this->assertEquals( 3 * HOUR_IN_SECONDS, get_post_meta( $event, '_EventDuration', true ) );
	}

	/**
	 * It should allow updating an event map settings
	 *
	 * @test
	 */
	public function should_allow_updating_an_event_map_settings() {
		$event_id = $this->factory()->event->create( [
			'meta_input' => [
				'_EventShowMap'     => '1',
				'_EventShowMapLink' => '',
			]
		] );

		tribe_events()->where( 'post__in', [ $event_id ] )
		              ->set( 'show_map', false )
		              ->set( 'show_map_link', true )
		              ->save();

		$this->assertEquals( '', get_post_meta( $event_id, '_EventShowMap', true ) );
		$this->assertEquals( '1', get_post_meta( $event_id, '_EventShowMapLink', true ) );
	}
}
