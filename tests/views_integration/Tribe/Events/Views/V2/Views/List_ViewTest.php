<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class List_ViewTest extends ViewTestCase {

	use MatchesSnapshots;

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		// @todo @be fix this when Month view PR is merged.
		$this->markTestSkipped('Due to an issue with dynamic dates; fixed in Month View PR.');
		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$context = tribe_context()->alter(
			[
				'today'      => $this->mock_date_value,
				'now'        => $this->mock_date_value,
				'event_date' => $this->mock_date_value
			]
		);

		$list_view = View::make( List_View::class, $context );
		$html      = $list_view->get_html();

		// Let's make sure the View is displaying what events we expect it to display.
		$expected_post_ids = [];
		$this->assertEquals(
			$expected_post_ids,
			$list_view->found_post_ids()
		);

		$this->assertMatchesSnapshot( $html );
	}

	/**
	 * Test render with upcoming events
	 */
	public function test_render_with_upcoming_events() {
		// @todo @be fix this when Month view PR is merged.
		$this->markTestSkipped('Due to an issue with dynamic dates; fixed in Month View PR.');
		$events = [];

		// Create the events.
		foreach (
			[
				'tomorrow 9am',
				'+1 week',
				'+9 days',
			] as $start_date
		) {
			$events[] = tribe_events()->set_args( [
				'start_date' => $start_date,
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $start_date,
				'status'     => 'publish',
			] )->create();
		}
		// Sanity check
		$this->assertEquals( 3, tribe_events()->where( 'ends_after', 'now' )->count() );

		$this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json'
		] );

		$list_view = View::make( List_View::class );
		$list_view->set_context( tribe_context()->alter( [
			'today'      => $this->mock_date_value,
			'now'        => $this->mock_date_value,
			'posts_per_page' => 2,
		] ) );
		$html = $list_view->get_html();

		// Let's make sure the View is displaying what events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 0, 2 ), 'ID' );
		$this->assertEquals(
			$expected_post_ids,
			$list_view->found_post_ids()
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
			'no'   => '/events/list/',
			'past' => '/events/list/',
		];
		$today          = '2019-02-02 00:00:00';

		foreach ( $now_times as $now_key => $now ) {
			foreach ( $event_dates as $event_date_key => $event_date ) {
				foreach ( [ 1, 2, 3 ] as $page ) {
					foreach ( $event_displays as $event_display => $expected ) {
						$set_name      = "event_date_{$event_date_key}_today_w_{$now_key}_time_w_{$event_display}_display_mode_p_{$page}";
						$event_display = 'no' === $event_display ? '' : $event_display;

						yield $set_name => [ $today, $now, $event_date, $event_display, $page, $expected ];
					}
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
	public function should_correctly_build_today_url( $today, $now, $event_date, $event_display_mode, $page, $expected ) {
		$values  = [
			'today'              => $today,
			'now'                => $now,
			'event_date'         => $event_date,
			'event_display_mode' => $event_display_mode,
			'paged'               => $page,
		];
		$context = $this->get_mock_context()->alter( array_filter( $values ) );
		$mock_repository = $this->makeEmpty(
			\Tribe__Repository__Interface::class,
			[
				'count' => 23
			]
		);

		$view = View::make( List_View::class, $context );
		$view->set_repository( $mock_repository );
		$url_args = [ 'paged' => $page, 'eventDisplay' => $event_display_mode ];
		$view->set_url( array_filter( $url_args ) );

		$this->assertEquals( home_url( $expected ), $view->get_today_url( true ) );
	}
}
