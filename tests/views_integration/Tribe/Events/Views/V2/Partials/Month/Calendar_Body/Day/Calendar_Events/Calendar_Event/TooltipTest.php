<?php

namespace Tribe\Events\Views\V2\Partials\Month\Calendar_Body\Day\Calendar_Events\Calendar_Event;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class TooltipTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/tooltip';

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
		$event->cost = '$10';
		$event->ID = 4;

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
				'timezone' => 'Europe/Paris',
				'duration' => 3 * HOUR_IN_SECONDS,
				'title' => 'Test Event - 2018-01-01 10am',
				'status' => 'publish',
				'_thumbnail_id' => $thumbnail_id,
			]
		)->create();
		$event        = tribe_get_event( $event );
		$event->cost  = '$10';
		$event->ID = 6;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
