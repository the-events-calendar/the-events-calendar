<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Timezones as Timezones;

class Day_ViewTest extends ViewTestCase {

	use MatchesSnapshots;

	public function setUp() {
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// Remove v1 filtering to have consistent results.
		remove_filter( 'tribe_events_before_html', [ TEC::instance(), 'before_html_data_wrapper' ] );
		remove_filter( 'tribe_events_after_html', [ TEC::instance(), 'after_html_data_wrapper' ] );
		tribe( 'cache' )->reset();
	}

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

	public function test_render_w_events() {
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

		add_filter( 'tribe_events_views_v2_view_day_template_vars', function ( array $vars )
		{
			$vars['events'] = [
				$this->get_mock_event( 'events/featured/id.template.json', [ 'id' => 904385349785 ] ),
				$this->get_mock_event( 'events/single/id.template.json', [ 'id' => 349589759485 ] ),
				$this->get_mock_event( 'events/single/id.template.json', [ 'id' => 340934095850 ] ),
			];

			return $vars;
		} );

		$context = tribe_context()->alter( [
				'today'      => $this->mock_date_value,
				'now'        => $this->mock_date_value,
				'event_date' => $this->mock_date_value,
			]
		);
		$day_view = View::make( Day_View::class, $context );

		$html = $day_view->get_html();

		// Let's make sure the View is displaying what events we expect it to display.
		$expected_post_ids = wp_list_pluck( $events, 'ID' );

		$this->assertEquals( $expected_post_ids, $day_view->found_post_ids() );

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
		$values          = [
			'today'              => $today,
			'now'                => $now,
			'event_date'         => $event_date,
			'event_display_mode' => $event_display_mode,
		];
		$context         = $this->get_mock_context()->alter( array_filter( $values ) );
		$mock_repository = $this->makeEmpty(
			\Tribe__Repository__Interface::class,
			[
				'count' => 23
			]
		);

		$view = View::make( Day_View::class, $context );
		$view->set_repository( $mock_repository );

		$today_url = $view->get_today_url( true );
		$this->assertEquals( home_url( $expected ), $today_url );
	}

	public function message_data_sets(  ) {
		yield 'no_results_found' => [
			[],
			[
				Messages::TYPE_NOTICE => [
					Messages::for_key(
						'no_upcoming_events',
						date_i18n( tribe_get_date_format( true ), Dates::build_date_object( '2019-09-11' )->getTimestamp() ),
						null
					)
				],
			]
		];

		yield 'no_results_found_w_keyword' => [
			[ 'keyword' => 'cabbage' ],
			[
				Messages::TYPE_NOTICE => [ Messages::for_key( 'no_results_found_w_keyword', 'cabbage' ) ],
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
			'event_date' => '2019-09-11',
		], $context_alterations );
		$context = $this->get_mock_context()->alter( array_filter( $values ) );

		$view    = View::make( Day_View::class, $context );
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

	public function server_timezone_provider() {
		return [
			'UTC'                             => [ 'UTC' ],
			'America/New_York (same as site)' => [ 'America/New_York' ],
			'Europe/Paris'                    => [ 'Europe/Paris' ],
			'Asia/Singapore'                  => [ 'Asia/Singapore' ],
			'Pacific/Midway'                  => [ 'Pacific/Midway' ],
		];
	}

	/**
	 * should correctly setup day interval
	 *
	 * @test
	 * @dataProvider server_timezone_provider
	 */
	public function should_correctly_setup_day_interval( $server_timezone) {
		// Backup the current server timezone.
		$this->date_default_timezone = date_default_timezone_get();
		// Do not check for current dates in templates inputs.
		remove_filter( 'tribe_events_views_v2_view_template_vars', [ $this, 'collect_date_dependent_values' ] );
		// Site Timezone is America, New York.
		update_option('timezone_string','America/New_York');
		// Server timezone is UTC.
		date_default_timezone_set( $server_timezone );
		// Set up a fake "now"; this simulates a Day View request done at `2019-09-11 22:00:00`.
		$date = new \DateTime( '2019-09-11 22:00:00', new \DateTimeZone( 'America/New_York' ) );
		$now = $date->getTimestamp() ;
		// Alter the concept of the `now` timestamp to return the timestamp for `2019-09-11 22:00:00` in NY timezone.
		uopz_set_return( 'strtotime', static function ( $str ) use ( $now ) {
			return $str === 'now' ? $now : strtotime( $str );
		}, true );
		// Make sure that `now` (string) will be resolved to the fake date object.
		uopz_set_return( Dates::class, 'build_date_object', $date );

		/*
		 * Given a "now" of 2019-09-11 22:00:00 the beginning of day should be `2019-09-11 00:00:00`,
		 * while the end of day should be `2019-09-11 00:00:00` w/ default cut-off.
		 * Although, `2019-09-11 22:00:00` New York time, when translated to UTC is `2019-09-12 02:00:00`;
		 * 2 AM on the next day. This would generate a beginning of day of `2019-09-12 00:00:00` and a end
		 * of day of `2019-09-12 23:59:59` which is NOT correct.
		 */
		$context = $this->get_mock_context()->alter( [
			'today'      => '2019-09-11',
			'now'        => '2019-09-11 22:00:00',
			'event_date' => 'now',
		] );

		$view = View::make( Day_View::class, $context );
		add_filter( 'tribe_events_views_v2_view_repository_args', static function ( $args ) use ( &$repository_args ) {
			$repository_args = $args;

			return $repository_args;
		} );
		$view->get_template_vars();

		$this->assertEquals(
			'2019-09-11 00:00:00',
			$repository_args['date_overlaps'][0],
			"The server timezone should not affect the start of day resolution."
		);
		$this->assertEquals(
			'2019-09-11 23:59:59',
			$repository_args['date_overlaps'][1],
			"The server timezone should not affect the end of day resolution."
		);
	}

	/**
	 * @test
	 */
	public function test_render_w_events_w_taxonomies() {
		$timezone_string = 'America/Sao_Paulo';
		$timezone        = Timezones::build_timezone_object( $timezone_string );
		$today           = Dates::build_date_object( $this->mock_date_value, $timezone )->format( 'Y-m-d' );
		update_option( 'timezone_string', $timezone_string );
		$cat    = $this->factory()->term->create( [ 'slug' => 'cat-1', 'name' => 'cat-1', 'taxonomy' => TEC::TAXONOMY ] );
		$cat_term = get_term( $cat, TEC::TAXONOMY  );
		$tag    = $this->factory()->tag->create( [ 'slug' => 'tag-1', 'name' => 'tag-1' ] );
		$tag_term = get_term( $tag, 'post_tag'  );

		$events = [];

		// Create the events.
		foreach (
			[
				'09:00',
				'12:30',
			] as $start_time
		) {
			$events[] = tribe_events()->set_args( [
				'start_date' => $today . ' ' . $start_time,
				'timezone'   => $timezone_string,
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $start_time,
				'status'     => 'publish',
				'category'   => $cat,
				'tag'        => $tag,
			] )->create();
		}

		// Create the events with taxonomies.
		foreach (
			[
				'09:00',
				'12:00',
				'16:30',

			] as $start_time
		) {
			$events[] = $new_event = tribe_events()->set_args( [
				'start_date' => $today . ' ' . $start_time,
				'timezone'   => $timezone_string,
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $start_time,
				'status'     => 'publish',
				'category'   => $cat,
				'tag'        => $tag,
			] )->create();

			// Added manually addition of the taxonomies as the above coding was not adding them.
			wp_set_object_terms( $new_event->ID, $cat, TEC::TAXONOMY, false );
			wp_set_object_terms( $new_event->ID, $tag, 'post_tag', false );
		}

		// Sanity check
		$day_start = tribe_beginning_of_day( $today );
		$day_end   = tribe_end_of_day( $today );
		$this->assertEquals( 5, tribe_events()->where( 'date_overlaps', $day_start, $day_end )->count() );

		add_filter( 'tribe_events_views_v2_view_day_template_vars', function ( array $vars ) {
			$vars['events'] = [
				$this->get_mock_event( 'events/featured/id.template.json', [ 'id' => 904385349785 ] ),
				$this->get_mock_event( 'events/single/id.template.json', [ 'id' => 349589759485 ] ),
				$this->get_mock_event( 'events/single/id.template.json', [ 'id' => 340934095850 ] ),
				$this->get_mock_event( 'events/featured/id.template.json', [ 'id' => 904385349781 ] ),
				$this->get_mock_event( 'events/single/id.template.json', [ 'id' => 349589759480 ] ),
				$this->get_mock_event( 'events/single/id.template.json', [ 'id' => 340934095823 ] ),
			];

			return $vars;
		} );

		// Category Archive.
		$context  = tribe_context()->alter( [
			'today'          => $this->mock_date_value,
			'now'            => $this->mock_date_value,
			'event_date'     => $this->mock_date_value,
			'event_category' => $cat_term->slug
		] );
		$day_view = View::make( Day_View::class, $context );

		$html = $day_view->get_html();

		// Let's make sure the View is displaying what events we expect it to display.
		$expected_post_ids = wp_list_pluck( array_slice( $events, 2, 5 ), 'ID' );

		$this->assertEquals( $expected_post_ids, $day_view->found_post_ids() );

		$this->assertMatchesSnapshot( $html );

		// Tag Archive.
		$context_tag  = tribe_context()->alter( [
			'today'      => $this->mock_date_value,
			'now'        => $this->mock_date_value,
			'event_date' => $this->mock_date_value,
			'post_tag'   => $tag_term->slug
		] );
		$day_view_tag = View::make( Day_View::class, $context_tag );

		$html_tag = $day_view_tag->get_html();

		$this->assertEquals( $expected_post_ids, $day_view_tag->found_post_ids() );

		$this->assertMatchesSnapshot( $html_tag );
	}

	public function tearDown() {
		parent::tearDown();
		if ( isset( $this->date_default_timezone ) ) {
			date_default_timezone_set( $this->date_default_timezone );
		}
		uopz_unset_return( 'strtotime' );
		uopz_unset_return( Dates::class, 'build_date_object' );
	}
}
