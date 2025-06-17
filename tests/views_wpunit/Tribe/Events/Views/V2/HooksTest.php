<?php

namespace Tribe\Events\Views\V2;


use Tribe\Events\Views\V2\Template\Title;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Context as Context;

class HooksTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	public function setUp() {
		parent::setUp();
		// Let's make sure we do not run "second" tests on a cached value.
		tribe_set_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME, null );
	}

	public function test_filter_redirect_canonical() {
		$this->set_fn_return( 'doing_filter', 'redirect_canonical' );
		$mock_context = new Context();
		$mock_context->add_locations( [
			'tec_post_type' => true,
			'view_request'  => 'month',
		] );
		$this->set_fn_return( 'tribe_context', $mock_context );

		$hooks    = new Hooks( tribe() );
		$filtered = $hooks->filter_redirect_canonical( 'http://example.com/events/month/', 'http://example.com/events/list/' );

		$this->assertEquals( 'http://example.com/events/month/', $filtered );
	}

	public function filter_redirect_canonical_data(): array {
		return [
			'not TEC post type'                           => [
				[
					'tec_post_type' => false,
					'view_request'  => 'month',
				],
				'http://example.com/some-page/',
				'http://example.com/some-page/',
			],
			'embed of TEC post type'                      => [
				[
					'tec_post_type' => true,
					'view_request'  => 'embed',
				],
				'http://example.com/some-page/',
				false
			],
			'single view of Event'                        => [
				[
					'tec_post_type' => true,
					'view_request'  => 'single-event',
				],
				'http://example.com/events/some-event/',
				'http://example.com/events/some-event/',
			],
			'TEC post type by empty view'                 => [
				[
					'tec_post_type' => true,
					'view_request'  => '',
				],
				'http://example.com/some/event/path/',
				'http://example.com/some/event/path/',
			],
			'redirected with tribe_redirected'            => [
				[
					'tec_post_type' => true,
					'view_request'  => 'month',
				],
				'http://example.com/events/list/?tribe_redirected=1',
				'http://example.com/events/list/?tribe_redirected=1',
			],
			'not redirected, eventDisplay match'          => [
				[
					'tec_post_type' => true,
					'view_request'  => 'month',
				],
				'http://example.com/events/month/',
				'http://example.com/events/month/',
			],
			'not redirected, eventDisplay does not match' => [
				[
					'tec_post_type' => true,
					'view_request'  => 'list',
				],
				'http://example.com/events/month/',
				false
			],
		];
	}

	/**
	 * @dataProvider filter_redirect_canonical_data
	 */
	public function test_filter_redirect_canonical_will_not_redirect_embed( array $context, string $redirect_url, $expected ): void {
		// Mock just the month rewreite rules to have `Rewrite::parse_request` work correctly.
		update_option( 'rewrite_rules', [
			'(?:events)/(?:month)/?$'                                                    => 'index.php?post_type=tribe_events&eventDisplay=month',
			'(?:events)/(?:month)/(?:featured)/?$'                                       => 'index.php?post_type=tribe_events&eventDisplay=month&featured=1',
			'(?:events)/(?:month)/(\\d{4}-\\d{2})/?$'                                    => 'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]',
			'(?:events)/(\\d{4}-\\d{2})/?$'                                              => 'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]',
			'(?:events)/(\\d{4}-\\d{2})/(?:featured)/?$'                                 => 'index.php?post_type=tribe_events&eventDisplay=month&eventDate=$matches[1]&featured=1',
			'(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/?$'                    => 'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month',
			'(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(?:month)/(?:featured)/?$'       => 'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&featured=1',
			'(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/?$'              => 'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]',
			'(?:events)/(?:category)/(?:[^/]+/)*([^/]+)/(\\d{4}-\\d{2})/(?:featured)/?$' => 'index.php?post_type=tribe_events&tribe_events_cat=$matches[1]&eventDisplay=month&eventDate=$matches[2]&featured=1',
			'(?:events)/(?:tag)/([^/]+)/(?:month)/?$'                                    => 'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month',
			'(?:events)/(?:tag)/([^/]+)/(?:month)/(?:featured)/?$'                       => 'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&featured=1',
			'(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/?$'                              => 'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]',
			'(?:events)/(?:tag)/([^/]+)/(\\d{4}-\\d{2})/(?:featured)/?$'                 => 'index.php?post_type=tribe_events&tag=$matches[1]&eventDisplay=month&eventDate=$matches[2]&featured=1',
		] );
		$mock_context = tribe_context()->alter( $context );
		$this->set_fn_return( 'tribe_context', $mock_context );

		$hooks    = new Hooks( tribe() );
		$filtered = $hooks->filter_redirect_canonical( $redirect_url );

		$this->assertEquals( $expected, $filtered );
	}

	/**
	 * Validate the posts in the title are stored from the view repository search.
	 * This is done via the hooks in the Views/Hooks class.
	 */
	public function test_events_for_title_stored() {
		for ( $i = 1; $i < 20; $i ++ ) {
			$date = "2020-06-$i 08:00:00";
			tribe_events()->set_args(
				[
					'start_date' => $date,
					'timezone'   => 'America/New_York',
					'duration'   => 3 * HOUR_IN_SECONDS,
					'title'      => 'Test Event',
					'status'     => 'publish',
				]
			)->create();
		}

		$title = tribe( Title::class );
		$this->assertEmpty( $title->get_posts() );
		$context = tribe_context()->alter(
			[
				'single' => false,
				'event_post_type' => true,
				'event_display' => 'list',
				'event_date'    => '2020-06-01',
			]
		);
		$view    = View::make( List_View::class );
		$view->set_context( $context );
		$view->get_html();
		$this->assertNotEmpty( $title->get_posts() );
		$this->assertEquals( $view->get_context()->get( 'events_per_page' ), count( $title->get_posts() ) );
	}

	/**
	 * Test that disabled views are properly redirected to the default view.
	 *
	 * @test
	 */
	public function test_disabled_views_redirected() {
		// Set up a disabled view
		$view_slug = 'month';
		tribe_update_option( 'tribeEnableViews', [ 'list' ] );

		// Set up the main query and context
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		$wp_query->query_vars = [
			'eventDisplay' => $view_slug,
			'post_type' => \Tribe__Events__Main::POSTTYPE,
			'tribe_view' => $view_slug,
		];
		$wp_query->tribe_is_event_query = true;
		$wp_query->is_main_query = true;

		tribe_context()->alter( [
			'event_display' => $view_slug
		] )->dangerously_set_global_context();

		// Set up redirect capture
		$store = [];
		$this->set_fn_return( 'wp_safe_redirect', function ( $url, $status = 302, $redirect_by = '' ) use (&$store) {
			$store[] = [
				'url'         => $url,
				'status'      => $status,
				'redirect_by' => $redirect_by,
			];
			return true;
		}, true );

		// Mock tribe_exit to prevent actual exit and allow test to continue
		$this->set_fn_return( 'tribe_exit', function() {
			return true;
		}, true );

		// Run the method
		$hooks = new Hooks( tribe() );
		$hooks->disabled_views_redirect();

		// Verify redirect was attempted
		$this->assertCount( 1, $store, 'Should have attempted one redirect' );
		$this->assertEquals( 301, $store[0]['status'], 'Should have attempted a 301 redirect' );
		$this->assertStringContainsString( '/events/', $store[0]['url'], 'Should redirect to default view' );
		$this->assertStringContainsString( 'tribe_redirected=1', $store[0]['url'], 'Should include tribe_redirected flag' );

		// Clean up
		tribe_update_option( 'tribeEnableViews', [] );
	}

	/**
	 * Test that enabled views are not redirected.
	 *
	 * @test
	 */
	public function test_enabled_views_not_redirected() {
		// Set up an enabled view
		$view_slug = 'list';
		tribe_update_option( 'tribeEnableViews', [ 'list', 'month' ] );

		// Set up the main query and context
		global $wp_the_query, $wp_query;
		$wp_the_query = new \WP_Query();
		$wp_query     = $wp_the_query;
		$wp_query->query_vars = [
			'eventDisplay' => $view_slug,
			'post_type' => \Tribe__Events__Main::POSTTYPE,
			'tribe_view' => $view_slug,
		];
		$wp_query->tribe_is_event_query = true;
		$wp_query->is_main_query = true;

		tribe_context()->alter( [
			'event_display' => $view_slug
		] )->dangerously_set_global_context();

		// Set up redirect capture
		$store = [];
		$this->set_fn_return( 'wp_safe_redirect', function ( $url, $status = 302, $redirect_by = '' ) use (&$store) {
			$store[] = [
				'url'         => $url,
				'status'      => $status,
				'redirect_by' => $redirect_by,
			];
			return true;
		}, true );

		// Mock tribe_exit to prevent actual exit and allow test to continue
		$this->set_fn_return( 'tribe_exit', function() {
			return true;
		}, true );

		// Run the method
		$hooks = new Hooks( tribe() );
		$hooks->disabled_views_redirect();

		// Verify no redirect was attempted
		$this->assertCount( 0, $store, 'Should not have attempted any redirects' );

		// Clean up
		tribe_update_option( 'tribeEnableViews', [] );
	}
}
