<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;
use Tribe__Events__Main as TEC;

class Month_ViewTest extends ViewTestCase {
	use MatchesSnapshots;

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	public function setUp() {
		parent::setUp();
		tribe( 'cache' )->reset();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		$now = new \DateTime( $this->mock_date_value );

		$this->context = tribe_context()->alter(
			[
				'today'      => $this->mock_date_value,
				'now'        => $this->mock_date_value,
				'event_date' => $now->format( 'Y-m-d' ),
			]
		);

		// Remove v1 filtering to have consistent results.
		remove_filter( 'tribe_events_before_html', [ TEC::instance(), 'before_html_data_wrapper' ] );
		remove_filter( 'tribe_events_after_html', [ TEC::instance(), 'after_html_data_wrapper' ] );

		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();

		tribe( 'cache' )->reset();
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$month_view = View::make( Month_View::class, $this->context );

		$this->assertEmpty( $month_view->found_post_ids() );

		$this->assertMatchesSnapshot( $month_view->get_html() );
	}

	/**
	 * Test render with events
	 */
	public function test_render_with_events() {
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new \DateTimeImmutable( $this->mock_date_value, $timezone );

		$events    = array_map(
			static function ( $i ) use ( $now, $timezone ) {
				return tribe_events()->set_args(
					[
						'start_date' => $now->setTime( 10 + $i, 0 ),
						'timezone'   => $timezone,
						'duration'   => 3 * HOUR_IN_SECONDS,
						'title'      => 'Test Event - ' . $i,
						'status'     => 'publish',
					]
				)->create();
			},
			range( 1, 3 )
		);
		$event_ids = wp_list_pluck($events,'ID') ;
		$mock_and_insert = function($template, $id){
			$this->wp_insert_post($this->get_mock_event( $template, [ 'id' => $id ] ));

			return $id;
		};
		$remapped_post_ids = array_combine( $event_ids, [
			$mock_and_insert( 'events/featured/id.template.json', 234234234 ),
			$mock_and_insert( 'events/single/id.template.json', 2453454355 ),
			$mock_and_insert( 'events/single/id.template.json', 3094853477 ),
		] );

		add_filter(
			'tribe_events_views_v2_view_data',
			function ( array $data ) use ( $remapped_post_ids ) {
				if ( ! empty( $data['events'] ) ) {
					foreach ( $data['events'] as &$day_events_ids ) {
						$day_events_ids = $this->remap_post_id_array( $day_events_ids, $remapped_post_ids );
					}
				}

				return $data;
			}
		);
		add_filter( 'tribe_events_views_v2_view_month_template_vars', function ( $vars ) use ( $remapped_post_ids )
		{
			$vars['events']['2019-01-01']         = $this->remap_post_id_array( $vars['events']['2019-01-01'],
				$remapped_post_ids );
			$vars['days']['2019-01-01']['events'] = array_combine(
				$remapped_post_ids,
				array_map( 'tribe_get_event', $remapped_post_ids )
			);

			return $vars;
		} );

		/** @var Month_View $month_view */
		$month_view      = View::make( Month_View::class, $this->context );
		$html = $month_view->get_html();

		$this->assertEquals( $event_ids, $month_view->found_post_ids() );

		foreach ( $month_view->get_grid_days( $now->format( 'Y-m' ) ) as $date => $found_day_ids ) {
			$day          = new \DateTimeImmutable( $date, $timezone );
			$expected_ids = tribe_events()
				->where(
					'date_overlaps',
					$day->setTime( 0, 0 ),
					$day->setTime( 23, 59, 59 ),
					$timezone
				)->get_ids();

			$this->assertEquals(
				$expected_ids,
				$found_day_ids,
				sprintf(
					'Day %s event IDs mismatch, expected %s, got %s',
					$day->format( 'Y-m-d' ),
					json_encode( $expected_ids ),
					json_encode( $found_day_ids )
				)
			);
		}

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
			'no'   => '/events/month/',
			'past' => '/events/month/',
		];
		$today          = '2019-02-02 00:00:00';

		foreach ( $now_times as $now_key => $now ) {
			foreach ( $event_dates as $event_date_key => $event_date ) {
				foreach ( $event_displays as $event_display => $expected ) {
					$set_name      = "event_date_{$event_date_key}_today_w_{$now_key}_time_w_{$event_display}_display_mode";
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
		$mock_repository = $this->makeEmpty(
			\Tribe__Repository__Interface::class,
			[
				'count' => 23
			]
		);

		$view = View::make( Month_View::class, $context );
		$view->set_repository( $mock_repository );

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
				Messages::TYPE_NOTICE => [ Messages::for_key( 'month_no_results_found_w_keyword', 'cabbage' ) ],
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

		$view    = View::make( Month_View::class, $context );
		$view->set_repository( $this->makeEmpty( \Tribe__Repository__Interface::class, [
			'found'   => 0,
			'get_ids' => [],
		] ) );
		// Call this method to trigger the message population in the View.
		$view->get_template_vars();

		$this->assertEquals( $expected, $view->get_messages() );
	}

	public function day_url_data_provider() {
		return [
			// Context alterations, expected placeholder.
			'no filters'                               => [ [], '/events/{{ first_date }}/' ],
			'with category'                            => [ [ 'event_category' => 'test' ], '/events/category/test/day/{{ first_date }}/' ],
			'featured events only'                     => [ [ 'featured' => 'true' ], '/events/{{ first_date }}/featured/' ],
			'featured events only in category'         => [
				[ 'event_category' => 'test', 'featured' => 'true' ],
				'/events/category/test/day/{{ first_date }}/featured/',
			],
			'with keyword'                             => [
				[ 'keyword' => 'cabbage' ],
				'/events/{{ first_date }}/?tribe-bar-search=cabbage',
			],
			'with keyword, featured only and category' => [
				[ 'event_category' => 'test', 'featured' => 'true', 'keyword' => 'cabbage' ],
				'/events/category/test/day/{{ first_date }}/featured/?tribe-bar-search=cabbage',
			]
		];
	}

	/**
	 * It should carry over query args from View to View More link
	 *
	 * @testS
	 * @dataProvider day_url_data_provider
	 */
	public function should_carry_over_query_args_from_view_to_view_more_link( array $context_alterations, string $expected_template ) {
		// Create the category to make sure it will appear.
		static::factory()->term->create( [ 'taxonomy' => TEC::TAXONOMY, 'slug' => 'test' ] );
		$view = View::make( Month_View::class );
		$view->set_context( $this->context->alter( $context_alterations ) );
		$view_template_vars = $view->get_template_vars();

		// Sanity check.
		$this->assertNotEmpty($view_template_vars['days']);

		// We're just interested in the URL structure, the dates are tested elsewhere.
		$first_date = array_keys($view_template_vars['days'])[0];
		$day_view_more_link = $view_template_vars['days'][$first_date]['day_url'];

		$expected = home_url( str_replace( '{{ first_date }}', $first_date, $expected_template ) );
		$this->assertEquals( $expected, $day_view_more_link );
	}

	/**
	 * @test
	 */
	public function test_render_with_events_w_taxonomies() {
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );
		$cat    = $this->factory()->term->create( [ 'slug' => 'cat-1', 'taxonomy' => TEC::TAXONOMY ] );
		$cat_term = get_term( $cat, TEC::TAXONOMY  );
		$tag    = $this->factory()->tag->create( [ 'slug' => 'tag-1' ] );
		$tag_term = get_term( $tag, 'post_tag'  );
		$now = new \DateTimeImmutable( $this->mock_date_value, $timezone );

		$events    = array_map(
			static function ( $i ) use ( $now, $timezone, $cat, $tag ) {
				$new_event = tribe_events()->set_args(
					[
						'start_date' => $now->setTime( 10 + $i, 0 ),
						'timezone'   => $timezone,
						'duration'   => 2 * HOUR_IN_SECONDS,
						'title'      => 'Test Event - ' . $i,
						'status'     => 'publish',
						'category'   => $cat,
						'tag'        => $tag,
					]
				)->create();

				// Added manually addition of the taxonomies as the above coding was not adding them.
				if ( 1 === $i || 2 === $i ) {
					wp_set_object_terms( $new_event->ID, $cat, TEC::TAXONOMY, false );
					wp_set_object_terms( $new_event->ID, $tag, 'post_tag', false );
				}

				return $new_event;
			},
			range( 1, 3 )
		);
		$event_ids = wp_list_pluck($events,'ID') ;
		$mock_and_insert = function($template, $id){
			$this->wp_insert_post($this->get_mock_event( $template, [ 'id' => $id ] ));

			return $id;
		};
		$remapped_post_ids = array_combine( $event_ids, [
			$mock_and_insert( 'events/single/id.template.json', 234234234 ),
			$mock_and_insert( 'events/single/id.template.json', 2453454355 ),
			$mock_and_insert( 'events/single/id.template.json', 3094853477 ),
		] );

		add_filter(
			'tribe_events_views_v2_view_data',
			function ( array $data ) use ( $remapped_post_ids ) {
				if ( ! empty( $data['events'] ) ) {
					foreach ( $data['events'] as &$day_events_ids ) {
						$day_events_ids = $this->remap_post_id_array( $day_events_ids, $remapped_post_ids );
					}
				}

				return $data;
			}
		);
		add_filter( 'tribe_events_views_v2_view_month_template_vars', function ( $vars ) use ( $remapped_post_ids )
		{
			$vars['events']['2019-01-01']         = $this->remap_post_id_array( $vars['events']['2019-01-01'],
				$remapped_post_ids );
			$vars['days']['2019-01-01']['events'] = array_combine(
				$remapped_post_ids,
				array_map( 'tribe_get_event', $remapped_post_ids )
			);

			return $vars;
		} );

		// Category Archive.
		$context = tribe_context()->alter( [
			'today'          => $this->mock_date_value,
			'now'            => $this->mock_date_value,
			'event_date'     => $now->format( 'Y-m-d' ),
			'event_category' => $cat_term->slug
		] );

		/** @var Month_View $month_view */
		$month_view      = View::make( Month_View::class, $context );

		$html = $month_view->get_html();

		$expected_ids = wp_list_pluck( array_slice( $events, 0, 2 ), 'ID' );

		$this->assertEquals( $expected_ids, $month_view->found_post_ids() );

		foreach ( $month_view->get_grid_days( $now->format( 'Y-m' ) ) as $date => $found_day_ids ) {
			$day          = new \DateTimeImmutable( $date, $timezone );
			$expected_ids = tribe_events()
				->by_args( ['event_category' => $cat_term->slug] )
				->where(
					'date_overlaps',
					$day->setTime( 0, 0 ),
					$day->setTime( 23, 59, 59 ),
					$timezone,
				)->get_ids();

			$this->assertEquals(
				$expected_ids,
				$found_day_ids,
				sprintf(
					'Day %s event IDs mismatch, expected %s, got %s',
					$day->format( 'Y-m-d' ),
					json_encode( $expected_ids ),
					json_encode( $found_day_ids )
				)
			);
		}

		$this->assertMatchesSnapshot( $html );

		// Tag Archive.
		$context_tag = tribe_context()->alter( [
			'today'      => $this->mock_date_value,
			'now'        => $this->mock_date_value,
			'event_date' => $now->format( 'Y-m-d' ),
			'post_tag'   => $tag_term->slug
		] );

		/** @var Month_View $month_view_tag */
		$month_view_tag      = View::make( Month_View::class, $context_tag );

		$html_tag = $month_view_tag->get_html();

		$this->assertEquals( $expected_ids, $month_view_tag->found_post_ids() );

		foreach ( $month_view_tag->get_grid_days( $now->format( 'Y-m' ) ) as $date => $found_day_ids ) {
			$day          = new \DateTimeImmutable( $date, $timezone );
			$expected_ids = tribe_events()
				->by_args( ['event_category' => $cat_term->slug] )
				->where(
					'date_overlaps',
					$day->setTime( 0, 0 ),
					$day->setTime( 23, 59, 59 ),
					$timezone,
				)->get_ids();

			$this->assertEquals(
				$expected_ids,
				$found_day_ids,
				sprintf(
					'Day %s event IDs mismatch, expected %s, got %s',
					$day->format( 'Y-m-d' ),
					json_encode( $expected_ids ),
					json_encode( $found_day_ids )
				)
			);
		}

		$this->assertMatchesSnapshot( $html_tag );
	}
}
