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
}
