<?php

namespace Tribe\Events;

use tad\WP\Snapshots\WPHtmlOutputDriver;
use Tribe__Events__Timezones as Timezones;
use Tribe\Events\Test\Factories\Event;
use Spatie\Snapshots\MatchesSnapshots;

class TimezonesTest extends \Codeception\TestCase\WPTestCase {

	use MatchesSnapshots;

	public function setUp() {
		// before
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// your set up methods here
		$this->factory()->event = new Event();

		// snapshots
		$this->driver = new WPHtmlOutputDriver( home_url(), 'http://tribe.dev' );

		tribe( 'cache' )->reset();
	}

	/**
	 *
	 */
	public function test_append_timzeone_should_append_correct_abbreviation_in_sitewide_mode() {

		$event_id = $this->factory()->event->create( [
			'when'       => '2018-05-01 08:00:00',
			'meta_input' => [
				'_EventTimezoneAbbr' => 'CEST'
			]
		] );

		update_option( 'timezone_string', 'America/New_York' );

		// Sitewide timezone everywhere
		tribe_update_option( 'tribe_events_timezone_mode', 'site' );

		tribe( 'cache' )->reset();

		$output = Timezones::append_timezone( '', $event_id );

		$this->assertMatchesSnapshot( $output, $this->driver );
	}

	/**
	 *
	 */
	public function test_append_timzeone_should_append_correct_abbreviation_in_event_mode() {

		$event_id = $this->factory()->event->create( [
			'when'       => '2018-05-01 08:00:00',
			'meta_input' => [
				'_EventTimezoneAbbr' => 'CEST'
			]
		] );

		update_option( 'timezone_string', 'America/New_York' );

		// Local event timezone
		tribe_update_option( 'tribe_events_timezone_mode', 'event' );

		tribe( 'cache' )->reset();

		$output = Timezones::append_timezone( '', $event_id );

		$this->assertMatchesSnapshot( $output, $this->driver );
	}

	/**
	 * Event time zone string should be returned, not site-wide time zone.
	 *
	 * @since 4.6.20
	 */
	public function test_get_event_timezone_string_should_return_event_meta_time_zone_if_set() {
		$event_id = $this->factory()->event->create( [
			'when'       => '2018-07-01 08:00:00',
			'meta_input' => [
				'_EventTimezone' => 'America/Chicago'
			]
		] );

		update_option( 'timezone_string', 'America/New_York' );

		// Site-wide general event time zone setting
		tribe_update_option( 'tribe_events_timezone_mode', 'site' );

		tribe( 'cache' )->reset();

		$event_tz_string = Timezones::get_event_timezone_string( $event_id );

		$this->assertEquals( $event_tz_string, 'America/Chicago' );
	}

	/**
	 * Event time zone string should fallback to site-wide time zone if event's is not set.
	 *
	 * @since 4.6.20
	 */
	public function test_get_event_timezone_string_should_return_site_time_zone_if_event_meta_time_zone_not_set() {
		$event_id = $this->factory()->event->create( [
			'when' => '2018-07-01 08:00:00',
		] );

		// to overcome factory creating a default value
		delete_post_meta( $event_id, '_EventTimezone' );

		update_option( 'timezone_string', 'America/New_York' );

		// Local event timezone
		tribe_update_option( 'tribe_events_timezone_mode', 'event' );

		tribe( 'cache' )->reset();

		$event_tz_string = Timezones::get_event_timezone_string( $event_id );

		$this->assertEquals( $event_tz_string, 'America/New_York' );
	}

}