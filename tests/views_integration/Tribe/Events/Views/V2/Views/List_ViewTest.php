<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;
use Tribe__Utils__Post_Collection as Collection;
use Tribe__Events__Main as TEC;

class List_ViewTest extends ViewTestCase {

	use MatchesSnapshots;

	public function setUp() {
		parent::setUp();
		\Tribe__Rewrite::instance()->setup();
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		// Sanity check
		$this->assertEmpty( tribe_events()->found() );

		$context = tribe_context()->alter(
			[
				'event_display'      => 'list',
				'event_display_mode' => 'list',
				'today'              => $this->mock_date_value,
				'now'                => $this->mock_date_value,
				'event_date'         => $this->mock_date_value,
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
		$context = tribe_context()->alter( [
			'event_display'      => 'list',
			'event_display_mode' => 'list',
			'today'              => $this->mock_date_value,
			'now'                => $this->mock_date_value,
			'event_date'         => $this->mock_date_value,
			'events_per_page'    => 2,
		] );

		$list_view->set_context( $context );
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

	public function message_data_sets(  ) {
		yield 'no_results_found' => [
			[],
			[
				Messages::TYPE_NOTICE => [ Messages::for_key( 'no_upcoming_events' ) ],
			]
		];

		yield 'no_results_found_w_keyword' => [
			[ 'keyword' => 'cabbage' ],
			[
				Messages::TYPE_NOTICE => [ Messages::for_key( 'no_results_found_w_keyword', 'cabbage' ) ],
			]
		];
		yield 'no_results_found_w_category' => [
			[ 'event_category' => 'test' ],
			[
				Messages::TYPE_NOTICE => [ Messages::for_key( 'no_upcoming_events' ) ],
			]
		];
		yield 'no_results_found_w_tag' => [
			[ 'post_tag' => 'test' ],
			[
				Messages::TYPE_NOTICE => [ Messages::for_key( 'no_upcoming_events' ) ],
			]
		];
	}

	/**
	 * It should display the correct messages to the user
	 *
	 * @test
	 * @dataProvider message_data_sets
	 */
	public function should_display_the_correct_messages_to_the_user( $context_alterations, $expected ) {
		$values  = array_merge( [
			'today'      => '2019-09-11',
			'now'        => '2019-09-11 09:00:00',
			'event_date' => '2019-09',
		], $context_alterations );
		$context = $this->get_mock_context()->alter( array_filter( $values ) );

		$view    = View::make( List_View::class, $context );
		$view->set_repository( $this->makeEmpty( \Tribe__Repository__Interface::class, [
			'found'   => 0,
			'get_ids' => [],
			'all' => [],
			'prev' => $this->makeEmpty(\Tribe__Repository__Interface::class),
			'next' => $this->makeEmpty(\Tribe__Repository__Interface::class),
		] ) );
		// Call this method to trigger the message population in the View.
		$view->get_template_vars();

		$this->assertEquals( $expected, $view->get_messages() );
	}

	public function pages_data_set() {
		return [
			'page 1' => [ 1 ],
			'page 2' => [ 2 ],
			'page 3' => [ 3 ],
		];
	}

	/**
	 * @dataProvider pages_data_set
	 */
	public function test_past_events_show_asc_order( $page ) {
		$values  = [
			'today'              => '2019-09-11',
			'now'                => '2019-09-11 09:00:00',
			'event_date'         => '2019-09-11',
			'event_display_mode' => 'past',
			'paged'              => $page,
			'events_per_page'    => 2,
		];
		$context = $this->get_mock_context()->alter( array_filter( $values ) );
		// Create 6 events, from 2019-09-01 to 2019-09-06. All before "today" date of 2019-09-11.
		$mock_events = array_map( static function ( $i ) {
			return static::factory()->event->create( [ 'when' => '2019-09-0' . $i . ' 09:00:00' ] );
		}, range( 1, 6 ) );
		// Reverse the order to ease the expectation.
		$mock_events = array_reverse( $mock_events );

		$view = View::make( List_View::class, $context );

		// Call this method to trigger the message population in the View.
		$template_vars = $view->get_template_vars();
		$this->assertArrayHasKey( 'events', $template_vars );
		$this->assertCount( 2, $template_vars['events'] );
		$found_event_ids = wp_list_pluck( $template_vars['events'], 'ID' );
		// Invert the slice as events should show in ASC date order.
		$slice = array_reverse( array_slice( $mock_events, $page * 2 - 2, 2 ) );
		$this->assertEquals( $slice, $found_event_ids );
	}

	public function test_render_with_events_all_starting_on_same_date_time(  ) {
		$context_alterations = [
			'today'      => '2020-01-01',
			'now'        => '2020-01-01 00:00:00',
			'event_date' => '2020-01-01',
		];
		$context             = $this->get_mock_context()->alter( array_filter( $context_alterations ) );
		// Shuffle to ensure the post date does not have a role in the output order.
		$durations = [
			'8am to 1pm'  => 5 * HOUR_IN_SECONDS,
			'8am to 11am' => 3 * HOUR_IN_SECONDS,
			'8am to 3pm'  => 7 * HOUR_IN_SECONDS,
			'8am to 12pm' => 4 * HOUR_IN_SECONDS,
			'8am to 10am' => 2 * HOUR_IN_SECONDS,
		];
		foreach ( $durations as $title => $duration ) {
			tribe_events()
				->set_args( [
					'title'      => $title,
					'status'     => 'publish',
					'start_date' => '2020-01-01 08:00:00',
					'duration'   => $duration,
				] )->create();
		}

		$view    = View::make( List_View::class, $context );
		$template_vars = $view->get_template_vars();

		$collection = new Collection($template_vars['events']);
		$expected   =[
			'8am to 10am',
			'8am to 11am',
			'8am to 12pm',
			'8am to 1pm',
			'8am to 3pm',
		];
		$this->assertEquals($expected, $collection->pluck('post_title'))	;
	}

	/**
	 * @test
	 */
	public function test_render_with_upcoming_taxonomy_events() {
		$events = [];
		$cat    = $this->factory()->term->create( [ 'slug' => 'cat-1', 'name' => 'cat-1', 'taxonomy' => TEC::TAXONOMY ] );
		$cat_term = get_term( $cat, TEC::TAXONOMY  );
		$tag    = $this->factory()->tag->create( [ 'slug' => 'tag-1', 'name' => 'tag-1' ] );
		$tag_term = get_term( $tag, 'post_tag'  );

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

		// Create the events with taxonomies.
		foreach (
			[
				'tomorrow 9am',
				'+4 days',
				'+2 week',
			] as $start_date
		) {
			$events[] = $new_event = tribe_events()->set_args( [
				'start_date' => $start_date,
				'timezone'   => 'America/New_York',
				'duration'   => 2 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $start_date,
				'status'     => 'publish',
				'category'   => $cat,
				'tag'        => $tag,
			] )->create();

			// Added manually addition of the taxonomies as the above coding was not adding them.
			wp_set_object_terms( $new_event->ID, $cat, TEC::TAXONOMY, false );
			wp_set_object_terms( $new_event->ID, $tag_term->slug, 'post_tag', false );
		}

		// Sanity check
		$this->assertEquals( 6, tribe_events()->where( 'ends_after', 'now' )->count() );

		$this->remap_posts( $events, [
			'events/featured/1.json',
			'events/single/1.json',
			'events/single/2.json',
			'events/featured/2.json',
			'events/single/3.json',
			'events/single/4.json'
		] );

		// Category Archive.
		$context   = tribe_context()->alter( [
			'event_display'      => 'list',
			'event_display_mode' => 'list',
			'today'              => $this->mock_date_value,
			'now'                => $this->mock_date_value,
			'event_date'         => $this->mock_date_value,
			'event_category'     => $cat_term->slug
		] );

		$list_view = View::make( List_View::class, $context );

		$html = $list_view->get_html();

		// Let's make sure the View is displaying what events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 3, 5 ), 'ID' );
		$this->assertEquals(
			$expected_post_ids,
			$list_view->found_post_ids()
		);

		$this->assertMatchesSnapshot( $html );

		// Tag Archive.
		$context_tag   = tribe_context()->alter( [
			'event_display'      => 'list',
			'event_display_mode' => 'list',
			'today'              => $this->mock_date_value,
			'now'                => $this->mock_date_value,
			'event_date'         => $this->mock_date_value,
			'post_tag'           => $tag_term->slug
		] );

		$list_view_tag = View::make( List_View::class, $context_tag );

		$html_tag = $list_view_tag->get_html();

		$this->assertEquals(
			$expected_post_ids,
			$list_view_tag->found_post_ids()
		);

		$this->assertMatchesSnapshot( $html_tag );
	}
}
