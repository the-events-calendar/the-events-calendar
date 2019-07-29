<?php

namespace Tribe\Events\Views\V2\Partials\Month\Calendar_Body\Day\Calendar_Events\Calendar_Event;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DateTest extends HtmlPartialTestCase {

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/date';

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

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$event = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();
		$event = tribe_get_event($event);
		$event->featured = true;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with recurring event
	 */
	public function test_render_with_recurring_event() {
		$this->markTestSkipped('This should be moved into PRO.');
		$event = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();
		$event            = tribe_get_event( $event );
		$event->recurring = true;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured image
	 */
	public function test_render_with_featured_image() {
		$thumbnail_id = static::factory()->attachment->create_upload_object(
			codecept_data_dir( 'images/featured-image.jpg' )
		);
		$event        = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
				'image'      => '#',
			]
		)->create();
		set_post_thumbnail( $event, $thumbnail_id );
		$event           = tribe_get_event( $event );
		$event->featured = true;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
