<?php

namespace Tribe\Events\Views\V2\Partials\Month\Calendar_Body;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

class DayTest extends HtmlPartialTestCase {

	protected $partial_path = 'month/calendar-body/day';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		list( $event_one, $event_two, $event_three, $event_four ) = $this->given_some_events();

		$timezone         = Timezones::build_timezone_object( 'Europe/Paris' );
		$date_object      = Dates::build_date_object( '2019-07-01', $timezone );
		$start_of_week    = 1;
		$day_date         = $date_object->format( 'Y-m-d' );
		$the_day_events   = [ $event_one, $event_two ];
		$day_stack        = [ $event_three, false, $event_four ];
		$featured_events  = [ $event_two ];
		$day_found_events = 6;
		$more_events      = 2;
		$day_data         = [
			'date'             => $day_date,
			'is_start_of_week' => $start_of_week === $date_object->format( 'N' ),
			'year_number'      => (int) $date_object->format( 'Y' ),
			'month_number'     => (int) $date_object->format( 'm' ),
			'day_number'       => (int) $date_object->format( 'd' ),
			'events'           => $the_day_events,
			'featured_events'  => $featured_events,
			'multiday_events'  => $day_stack,
			'found_events'     => $day_found_events,
			'more_events'      => $more_events,
		];
		$this->assertMatchesSnapshot(
			$this->get_partial_html(
				[
					'today_date' => '2019-07-01',
					'day_date'   => '2019-07-01',
					'day'        => $day_data,
				]
			)
		);
	}

	protected function given_some_events() {
		$event_one   = tribe_get_event(
			tribe_events()->set_args(
				[
					'start_date' => '2018-07-01 10am',
					'timezone'   => 'Europe/Paris',
					'duration'   => 3 * HOUR_IN_SECONDS,
					'title'      => 'Test Event - 2018-07-01 11am',
					'status'     => 'publish',
				]
			)->create(),
			OBJECT,
			'2019-07-01'
		);
		$event_two   = tribe_get_event(
			tribe_events()->set_args(
				[
					'start_date' => '2018-07-01 11am',
					'timezone'   => 'Europe/Paris',
					'duration'   => 3 * HOUR_IN_SECONDS,
					'title'      => 'Test Event - 2018-07-01 11am',
					'status'     => 'publish',
				]
			)->create(),
			OBJECT,
			'2019-07-01'
		);
		$event_three = tribe_get_event(
			tribe_events()->set_args(
				[
					'start_date' => '2018-07-01 11am',
					'timezone'   => 'Europe/Paris',
					'duration'   => 3 * DAY_IN_SECONDS,
					'title'      => 'Test Multi-day Event - 2018-07-01 11am',
					'status'     => 'publish',
				]
			)->create(),
			OBJECT,
			'2019-07-01'
		);
		$event_four  = tribe_get_event(
			tribe_events()->set_args(
				[
					'start_date' => '2018-07-01 1pm',
					'timezone'   => 'Europe/Paris',
					'duration'   => 2 * DAY_IN_SECONDS,
					'title'      => 'Test Multi-day Event - 2018-07-01 1pm',
					'status'     => 'publish',
				]
			)->create(),
			OBJECT,
			'2019-07-01'
		);

		// Mock each day ID to make snapshots consistent.

		$events  = [ $event_one, $event_two, $event_three, $event_four ];
		$mock_id = 999999;
		foreach ( $events as $event ) {
			$event->ID = $mock_id --;
		}

		return $events;
	}
}
