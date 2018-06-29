<?php
namespace Tribe\Events;

use Tribe__Events__Timezones as Timezones;
use Tribe\Events\Test\Factories\Event;
use Spatie\Snapshots\MatchesSnapshots;
use tad\WPBrowser\Snapshot\WPHtmlOutputDriver;

class TimezonesTest extends \Codeception\TestCase\WPTestCase
{

    use MatchesSnapshots;

    public function setUp() {
        // before
        parent::setUp();

        // your set up methods here
        $this->factory()->event = new Event();

        // snapshots
        $this->driver = new WPHtmlOutputDriver( home_url(), 'http://tribe.dev' );
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
        
        $output = Timezones::append_timezone( '', $event_id );
        
        $this->assertMatchesSnapshot( $output, $this->driver );
    }

	/**
	 * Event time zone string should be returned, not site-wide time zone.
	 *
	 * @since TBD
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

		$event_tz_string = Timezones::get_event_timezone_string( $event_id );

		$this->assertEquals( $event_tz_string, 'America/Chicago' );
	}

	/**
	 * Event time zone string should fallback to site-wide time zone if event's is not set.
	 *
	 * @since TBD
	 */
	public function test_get_event_timezone_string_should_return_site_time_zone_if_event_meta_time_zone_not_set() {
		$event_id = $this->factory()->event->create( [
			'when' => '2018-07-01 08:00:00',
		] );

		update_option( 'timezone_string', 'America/New_York' );

		// Local event timezone
		tribe_update_option( 'tribe_events_timezone_mode', 'event' );

		$event_tz_string = Timezones::get_event_timezone_string( $event_id );

		$this->assertEquals( $event_tz_string, 'America/New_York' );
	}

}