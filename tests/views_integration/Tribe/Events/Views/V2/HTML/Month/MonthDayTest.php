<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

class MonthDayTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

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
		$day_url          = 'http://tribe.tests/events/' . $date_object->format( 'Y-m-d' );
		$day_data         = [
			'date'             => $day_date,
			'is_start_of_week' => (bool) $start_of_week === $date_object->format( 'N' ),
			'year_number'      => $date_object->format( 'Y' ),
			'month_number'     => $date_object->format( 'm' ),
			'day_number'       => $date_object->format( 'j' ),
			'events'           => $the_day_events,
			'featured_events'  => $featured_events,
			'multiday_events'  => [],
			'found_events'     => $day_found_events,
			'more_events'      => $more_events,
			'day_url'          => $day_url,
		];
		$date_formats = (object) [
			'month_and_year'       => 'F Y',
			'time_range_separator' => ' - ',
			'date_time_separator'  => ' @ ',
		];

		$template = $this->template->template( 'month/calendar-body/day',
			[
				'today_date'      => '2019-07-01',
				'day_date'        => '2019-07-01',
				'day'             => $day_data,
				'grid_start_date' => '2019-07-01',
				'date_formats'    => $date_formats,
			]
		);

		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__day' )->count(),
			1,
			'Month Day HTML needs to contain one ".tribe-events-calendar-month__day" element'
		);

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__day-date' )->count(),
			2,
			'Month Day HTML needs to contain two ".tribe-events-calendar-month__day-date" elements'
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
