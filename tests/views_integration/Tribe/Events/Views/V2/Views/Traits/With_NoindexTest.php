<?php

namespace Tribe\Events\Views\V2\Views\Traits;

use TEC\Events\SEO\Controller;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Test\Testcases\TecViewTestCase;

class With_NoindexTest extends TecViewTestCase {

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	/**
	 * @var Controller
	 */
	protected $controller;

	public function setUp() {
		parent::setUp();
		tribe( 'cache' )->reset();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		$now = new \DateTime( $this->mock_date_value );

		$this->context = tribe_context()->alter( [
			'today'      => $this->mock_date_value,
			'now'        => $this->mock_date_value,
			'event_date' => $now->format( 'Y-m-d' ),
		] );

		// Ensure our controller is registered and available
		tribe_register_provider( Controller::class );
		$this->controller = tribe( Controller::class );

		tribe( 'cache' )->reset();
	}

	public function view_data_set() {
		yield 'month_view' => [ Month_View::class, true ];
		yield 'list_view' => [ List_View::class, false ];
		yield 'day_view' => [ Day_View::class, false ];
	}

	/**
	 * Test noindex render empty
	 *
	 * @dataProvider view_data_set
	 */
	public function test_noindex_render_empty( $class, $noindex_value ) {
		$tester = $this;
		add_filter( 'tec_events_seo_robots_meta_include', function ( $add_noindex ) use ( $tester, $noindex_value ) {
			$tester->assertEquals( $noindex_value, $add_noindex );

			return $add_noindex;
		} );

		$view = View::make( $class, $this->context );
		$view->get_html();

		tribe( Controller::class )->issue_noindex( $view );
	}

	/**
	 * Test noindex render with events
	 *
	 * @dataProvider view_data_set
	 */
	public function test_noindex_render_with_events( $class ) {
		$tester          = $this;
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );

		update_option( 'timezone_string', $timezone_string );

		$now    = new \DateTimeImmutable( $this->mock_date_value, $timezone );
		$events = array_map( static function ( $i ) use ( $now, $timezone ) {
			return tribe_events()->set_args( [
				'start_date' => $now->setTime( 10 + $i, 0 ),
				'timezone'   => $timezone,
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - ' . $i,
				'status'     => 'publish',
			] )->create();
		}, range( 1, 3 ) );

		$event_ids       = wp_list_pluck( $events, 'ID' );
		$mock_and_insert = function ( $template, $id ) {
			$this->wp_insert_post( $this->get_mock_event( $template, [ 'id' => $id ] ) );

			return $id;
		};

		$remapped_post_ids = array_combine( $event_ids, [
			$mock_and_insert( 'events/featured/id.template.json', 234234234 ),
			$mock_and_insert( 'events/single/id.template.json', 2453454355 ),
			$mock_and_insert( 'events/single/id.template.json', 3094853477 ),
		] );

		add_filter( 'tribe_events_views_v2_view_data', function ( array $data ) use ( $remapped_post_ids ) {
			if ( ! empty( $data['events'] ) ) {
				foreach ( $data['events'] as &$day_events_ids ) {
					$day_events_ids = $this->remap_post_id_array( (array) $day_events_ids, $remapped_post_ids );
				}
			}

			return $data;
		} );
		add_filter( 'tribe_events_views_v2_view_month_template_vars', function ( $vars ) use ( $remapped_post_ids ) {
			$vars['events']['2019-01-01'] = $this->remap_post_id_array( $vars['events']['2019-01-01'], $remapped_post_ids );

			$vars['days']['2019-01-01']['events'] = array_combine( $remapped_post_ids, array_map( 'tribe_get_event', $remapped_post_ids ) );

			return $vars;
		} );

		$view = View::make( $class, $this->context );
		$view->get_html();

		// Add our insertion on the filter so we can test the value. Month always gets noindex, so skip it.
		add_filter( 'tec_events_seo_robots_meta_include', function ( $add_noindex ) use ( $tester, $view ) {
			if ( $view->get_slug() === 'month' ) {
				return $add_noindex;
			}

			$tester->assertFalse( $add_noindex );

			return $add_noindex;
		} );

		// Make sure that month still has noindex.
		add_filter( 'tec_events_seo_robots_meta_include_month', function ( $add_noindex ) use ( $tester, $view ) {
			$tester->assertTrue( $add_noindex );

			return $add_noindex;
		} );

		// Manually run the noindex check, triggering the assertion.
		tribe( Controller::class )->issue_noindex( $view );
	}

	/**
	 * @test
	 */
	public function test_filter_robots_directives_adds_noindex() {
		$initial_robots  = [];
		$modified_robots = $this->controller->filter_robots_directives( $initial_robots );

		$this->assertArrayHasKey( 'noindex', $modified_robots );
		$this->assertTrue( $modified_robots['noindex'] );
	}

	/**
	 * @test
	 */
	public function test_filter_robots_directives_preserves_existing_directives() {
		$initial_robots = [
			'follow' => true
		];

		$modified_robots = $this->controller->filter_robots_directives( $initial_robots );

		$this->assertArrayHasKey( 'noindex', $modified_robots );
		$this->assertTrue( $modified_robots['noindex'] );
		$this->assertArrayHasKey( 'follow', $modified_robots );
		$this->assertTrue( $modified_robots['follow'] );
	}

	/**
	 * @test
	 */
	public function test_filter_robots_directives_with_filter_modification() {
		add_filter( 'tec_events_filter_wp_robots_meta_directives', function ( $robots ) {
			$robots['nofollow'] = true;

			return $robots;
		} );

		$initial_robots  = [];
		$modified_robots = $this->controller->filter_robots_directives( $initial_robots );

		$this->assertArrayHasKey( 'noindex', $modified_robots );
		$this->assertTrue( $modified_robots['noindex'] );
		$this->assertArrayHasKey( 'nofollow', $modified_robots );
		$this->assertTrue( $modified_robots['nofollow'] );

		// Clean up
		remove_all_filters( 'tec_events_filter_wp_robots_meta_directives' );
	}

	/**
	 * @test
	 */
	public function test_set_nofollow_adds_nofollow_directive() {
		$initial_robots  = [];
		$modified_robots = $this->controller->set_nofollow( $initial_robots );

		$this->assertArrayHasKey( 'nofollow', $modified_robots );
		$this->assertTrue( $modified_robots['nofollow'] );
	}

	/**
	 * @test
	 */
	public function test_set_nofollow_preserves_existing_directives() {
		$initial_robots = [
			'noindex' => true,
		];

		$modified_robots = $this->controller->set_nofollow( $initial_robots );

		$this->assertArrayHasKey( 'nofollow', $modified_robots );
		$this->assertTrue( $modified_robots['nofollow'] );
		$this->assertArrayHasKey( 'noindex', $modified_robots );
		$this->assertTrue( $modified_robots['noindex'] );
	}

	/**
	 * @test
	 */
	public function test_integration_of_nofollow_and_filter_robots_directives() {
		// First add nofollow
		$robots = $this->controller->set_nofollow( [] );

		// Then filter robots directives
		$final_robots = $this->controller->filter_robots_directives( $robots );

		$this->assertArrayHasKey( 'noindex', $final_robots );
		$this->assertTrue( $final_robots['noindex'] );
		$this->assertArrayHasKey( 'nofollow', $final_robots );
		$this->assertTrue( $final_robots['nofollow'] );
	}

	/**
	 * @test
	 */
	public function test_filter_robots_directives_with_complex_filter_chain() {
		// Add first filter
		add_filter( 'tec_events_filter_wp_robots_meta_directives', function ( $robots ) {
			$robots['nofollow'] = true;

			return $robots;
		}, 10 );

		// Add second filter with different priority
		add_filter( 'tec_events_filter_wp_robots_meta_directives', function ( $robots ) {
			$robots['follow'] = true;

			return $robots;
		}, 20 );

		$initial_robots  = [];
		$modified_robots = $this->controller->filter_robots_directives( $initial_robots );

		$this->assertArrayHasKey( 'noindex', $modified_robots );
		$this->assertTrue( $modified_robots['noindex'] );
		$this->assertArrayHasKey( 'nofollow', $modified_robots );
		$this->assertTrue( $modified_robots['nofollow'] );
		$this->assertArrayHasKey( 'follow', $modified_robots );
		$this->assertTrue( $modified_robots['follow'] );

		// Clean up
		remove_all_filters( 'tec_events_filter_wp_robots_meta_directives' );
	}

	/**
	 * @test
	 */
	public function test_hook_issue_noindex_bails_on_home() {
		$this->go_to( '/' );
		$this->assertTrue( is_home() );

		$this->controller->hook_issue_noindex();
		$this->assertFalse( has_filter( 'wp_robots', [ $this->controller, 'filter_robots_directives' ] ) );
	}

	/**
	 * @test
	 */
	public function test_hook_issue_noindex_bails_on_invalid_post_type() {
		$post_id = $this->factory()->post->create( [ 'post_type' => 'post' ] );
		$this->go_to( get_permalink( $post_id ) );

		$this->controller->hook_issue_noindex();
		$this->assertFalse( has_action( 'tec_events_before_view_html_cache', [ $this->controller, 'issue_noindex' ] ) );
	}

	/**
	 * @test
	 */
	public function test_unregister_removes_all_hooks() {
		add_action( 'wp', [ $this->controller, 'hook_issue_noindex' ] );
		add_action( 'tec_events_before_view_html_cache', [ $this->controller, 'issue_noindex' ] );

		$this->controller->unregister();

		$this->assertFalse( has_action( 'wp', [ $this->controller, 'hook_issue_noindex' ] ) );
		$this->assertFalse( has_action( 'tec_events_before_view_html_cache', [ $this->controller, 'issue_noindex' ] ) );
	}
}
