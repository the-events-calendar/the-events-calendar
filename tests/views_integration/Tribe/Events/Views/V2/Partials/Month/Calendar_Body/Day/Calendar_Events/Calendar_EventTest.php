<?php

namespace Tribe\Events\Views\V2\Partials\Month\Calendar_Body\Day\Calendar_Events;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Calendar_EventTest extends HtmlPartialTestCase {

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$event = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();
		$event = tribe_get_event( $event );
		// Mock the event ID to make it consistent across tests.
		$event->ID = 99999;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$event           = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();
		$event           = tribe_get_event( $event );
		$event->featured = true;
		// Mock the event ID to make it consistent across tests.
		$event->ID = 99999;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with recurring event
	 */
	public function test_render_with_recurring_event() {
		$this->markTestSkipped( 'This should be moved to PRO' );
		$event            = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
				'recurring'  => true,
			]
		)->create();
		$event            = tribe_get_event( $event );
		$event->recurring = true;
		// Mock the event ID to make it consistent across tests.
		$event->ID = 99999;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured image
	 */
	public function test_render_with_featured_image() {
		$thumbnail_id = self::factory()->attachment->create_upload_object(
			codecept_data_dir( 'images/featured-image.jpg' )
		);
		$event        = tribe_events()->set_args(
			[
				'start_date'    => '2018-01-01 10am',
				'timezone'      => 'Europe/Paris',
				'duration'      => 3 * HOUR_IN_SECONDS,
				'title'         => 'Test Event - 2018-01-01 10am',
				'status'        => 'publish',
				'_thumbnail_id' => $thumbnail_id
			]
		)->create();
		$event        = tribe_get_event( $event );
		// Mock the event ID to make it consistent across tests.
		$event->ID = 99999;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
