<?php

namespace Tribe\Events\Views\V2\Partials\Month\Calendar_Body\Day;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Multiday_EventsTest extends HtmlPartialTestCase {

	protected $partial_path = 'month/calendar-body/day/multiday-events';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$event_one       = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * DAY_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();
		$event_two       = tribe_events()->set_args(
			[
				'start_date' => '2018-01-02 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * DAY_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-02 10am',
				'status'     => 'publish',
			]
		)->create();
		// Mock the event IDs to get consistent results in tests.
		$event_one       = tribe_get_event( $event_one );
		$event_one->ID   = 999998;
		$event_two       = tribe_get_event( $event_two );
		$event_two->ID   = 99999;
		$multiday_events = [ $event_one, $event_two ];

		$this->assertMatchesSnapshot(
			$this->get_partial_html(
				[
					'multiday_events'  => $multiday_events,
					'day_date'         => '2018-01-02',
					'is_start_of_week' => false,
				]
			)
		);
	}
}
