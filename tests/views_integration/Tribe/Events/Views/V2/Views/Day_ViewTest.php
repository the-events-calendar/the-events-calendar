<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

class Day_ViewTest extends ViewTestCase {

	use MatchesSnapshots;

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$context = tribe_context()->alter( [
			'today'      => $this->mock_date_value,
			'now'        => $this->mock_date_value,
			'event_date' => $this->mock_date_value
		] );

		$day_view = View::make( Day_View::class, $context );
		$html     = $day_view->get_html();

		$this->assertMatchesSnapshot( $html );
	}

	public function test_render_multiple_events_on_day() {
		$timezone_string = 'America/Sao_Paulo';
		$timezone        = Timezones::build_timezone_object( $timezone_string );
		$today           = Dates::build_date_object( $this->mock_date_value, $timezone )->format( 'Y-m-d' );
		update_option( 'timezone_string', $timezone_string );

		$events = [];

		// Create the events.
		foreach (
			[
				'09:00',
				'12:00',
				'12:30',
			] as $start_time
		) {
			$events[]        = tribe_events()->set_args( [
				'start_date' => $today . ' ' . $start_time,
				'timezone'   => $timezone_string,
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $start_time,
				'status'     => 'publish',
			] )->create();
		}

		// Sanity check
		$day_start = tribe_beginning_of_day( $today );
		$day_end   = tribe_end_of_day( $today );
		$this->assertEquals( 3, tribe_events()->where( 'date_overlaps', $day_start, $day_end )->count() );

		$this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json'
		] );

		$context = tribe_context()->alter( [
				'today'      => $this->mock_date_value,
				'now'        => $this->mock_date_value,
				'events_per_page' => 2,
				'event_date' => $this->mock_date_value,
			]
		);
		$day_view = View::make( Day_View::class, $context );

		$html = $day_view->get_html();

		// Let's make sure the View is displaying what events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 0, 2 ), 'ID' );

		$this->assertEquals(
			$expected_post_ids,
			$day_view->found_post_ids()
		);

		$this->assertMatchesSnapshot( $html );
	}

	public function today_url_data_sets() {
		$event_dates    = [
			'lt' => '2019-02-01',
			'eq' => '2019-02-02',
			'gt' => '2019-02-03',
		];
		$now_times      = [
			'eq' => '2019-02-02 00:00:00',
			'gt' => '2019-02-02 09:00:00',
		];
		$event_displays = [
			'no'   => '/events/today/',
			'past' => '/events/today/',
		];
		$today          = '2019-02-02 00:00:00';

		foreach ( $now_times as $now_key => $now ) {
			foreach ( $event_dates as $event_date_key => $event_date ) {
				foreach ( $event_displays as $event_display => $expected ) {
					$set_name = "event_date_{$event_date_key}_today_w_{$now_key}_time_w_{$event_display}_display_mode";
					$event_display = 'no' === $event_display ? '' : $event_display;

					yield $set_name => [ $today, $now, $event_date, $event_display, $expected ];
				}
			}
		}
	}

	/**
	 * It should correctly build today_url
	 *
	 * @test
	 * @dataProvider today_url_data_sets
	 */
	public function should_correctly_build_today_url( $today, $now, $event_date, $event_display_mode, $expected ) {
		$values  = [
			'today'              => $today,
			'now'                => $now,
			'event_date'         => $event_date,
			'event_display_mode' => $event_display_mode,
		];
		$context = $this->get_mock_context()->alter( array_filter( $values ) );

		$view = View::make( Day_View::class, $context );

		$this->assertEquals( home_url( $expected ), $view->get_today_url( true ) );
	}
}
