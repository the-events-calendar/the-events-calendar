<?php

namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe__Timezones as Timezones;

class GoogleCalendarTest extends WPTestCase {
	private $mode = '';
	private $initial_tz;

	public function setUp() {
		parent::setUp();
		$this->mode       = Timezones::mode();
		$this->initial_tz = Timezones::build_timezone_object();
	}

	public function tearDown() {
		parent::tearDown();
		tribe_update_option( 'tribe_events_timezone_mode', $this->mode );
		update_option( 'timezone_string', $this->initial_tz );
	}

	/**
	 * should generate the event with the event timezone when timezone mode is event
	 *
	 * @test
	 */
	public function should_generate_the_event_with_the_event_timezone_when_timezone_mode_is_event() {
		tribe_update_option( 'tribe_events_timezone_mode', Timezones::EVENT_TIMEZONE );
		update_option( 'timezone_string', 'America/Los_Angeles' );

		tribe( 'cache' )->reset();

		$event_id = ( new Event() )->create_object( [ 'timezone' => 'America/Mexico_City' ] );

		$this->assertSame(
			'America/Mexico_City',
			$this->get_property_from_google_calendar_link( $event_id, 'ctz' )
		);
	}

	/**
	 * should generate the event with the global timezone when the timezone mode is site
	 *
	 * @test
	 */
	public function should_generate_the_event_with_the_global_timezone_when_the_timezone_mode_is_site() {
		tribe_update_option( 'tribe_events_timezone_mode', Timezones::SITE_TIMEZONE );
		update_option( 'timezone_string', 'America/Los_Angeles' );

		tribe( 'cache' )->reset();

		$event_id = ( new Event() )->create_object( [ 'timezone' => 'America/Mexico_City' ] );

		$this->assertSame(
			'America/Los_Angeles',
			$this->get_property_from_google_calendar_link( $event_id, 'ctz' )
		);
	}

	/**
	 * Extract the timezone from the generated gcal link.
	 *
	 * @since TBD
	 *
	 * @param $event_id
	 *
	 * @return mixed
	 */
	private function get_property_from_google_calendar_link( $event_id, string $property ) {
		$url = wp_parse_url( tribe_get_gcal_link( $event_id ) );

		$this->assertIsArray( $url );
		$this->assertArrayHasKey( 'query', $url );

		parse_str( $url['query'], $result );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( $property, $result );

		return $result[ $property ];
	}
}
