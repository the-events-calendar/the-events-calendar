<?php

namespace Tribe\Events\Views\V2;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Views\V2\Views\Reflector_View;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;

require_once codecept_data_dir( 'Views/V2/classes/Test_View.php' );

class ViewTest extends \Codeception\TestCase\WPTestCase {
	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( View::class, $sut );
	}

	/**
	 * @return View
	 */
	private function make_instance() {
		return new View( new Messages() );
	}

	/**
	 * It should return instance of itself if no view is registered
	 *
	 * @test
	 */
	public function should_return_instance_of_itself_if_no_view_is_registered() {
		add_filter( 'tribe_events_views', static function () {
			return [];
		} );

		$this->assertInstanceOf( View::class, View::make( 'test' ) );
	}

	/**
	 * It should return instance of itself if no view is registered for rest request
	 *
	 * @test
	 */
	public function should_return_instance_of_itself_if_no_view_is_registered_for_rest_request() {
		add_filter( 'tribe_events_views', static function () {
			return [];
		} );

		$this->assertInstanceOf( View::class, View::make_for_rest( new \WP_REST_Request() ) );
	}

	/**
	 * It should return an instance of a specified view if provided
	 *
	 * @test
	 */
	public function should_return_an_instance_of_a_specified_view_if_provided() {
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );

		$request         = new \WP_REST_Request();
		$request['view'] = 'test';
		$view            = View::make_for_rest( $request );
		$this->assertInstanceOf( Test_View::class, $view );
	}

	/**
	 * It should print a view HTML on the page when caling send_html
	 *
	 * @test
	 */
	public function should_print_a_view_html_on_the_page_when_calling_send_html() {
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );
		add_filter( 'tribe_exit', function () {
			return '__return_true';
		} );

		$view = View::make( 'test' );
		$view->send_html();

		$this->expectOutputString( Test_View::class );
	}

	/**
	 * It should print custom HTML when specifying it.
	 *
	 * @test
	 */
	public function should_print_custom_html_when_specifying_it_() {
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );
		add_filter( 'tribe_exit', function () {
			return '__return_true';
		} );

		$view = View::make( 'test' );
		$view->send_html( 'Alice in Wonderland' );

		$this->expectOutputString( 'Alice in Wonderland' );
	}

	/**
	 * It should use the global context if not assigned one
	 *
	 * @test
	 */
	public function should_use_the_global_context_if_not_assigned_one() {
		add_filter( 'tribe_events_views', function () {
			return [ 'test' => Test_View::class ];
		} );
		$view = View::make( Test_View::class );

		$view_context = $view->get_context();
		$this->assertInstanceOf( Context::class, $view_context );
		$this->assertSame( tribe_context(), $view_context );
	}

	/**
	 * It should return the assigned context if assigned one.
	 *
	 * @test
	 */
	public function should_return_the_assigned_context_if_assigned_one() {
		add_filter( 'tribe_events_views', function () {
			return [ 'test' => Test_View::class ];
		} );
		$view = View::make( Test_View::class );

		$view->set_context( tribe_context()->alter( [
			'view_data' => [
				'venue' => '23',
			],
		] ) );
		$view_context = $view->get_context();
		$this->assertInstanceOf( Context::class, $view_context );
		$this->assertNotSame( tribe_context(), $view_context );
	}

	/**
	 * It should assign a built view instance the slug it was registered with.
	 *
	 * @test
	 */
	public function should_assign_a_built_view_instance_the_slug_it_was_registered_with() {
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );

		$view = View::make( 'test' );

		$this->assertEquals( 'test', $view::get_view_slug() );
	}

	/**
	 * It should set a default template instance on the view when building it.
	 *
	 * @test
	 */
	public function should_set_a_default_template_instance_on_the_view_when_building_it() {
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );

		$view = View::make( 'test' );

		$this->assertInstanceOf( Template::class, $view->get_template() );
	}

	/**
	 * It should correctly produce a view next URLs
	 *
	 * @test
	 */
	public function should_correctly_produce_a_view_next_url() {
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );
		$events = static::factory()->event->create_many( 3 );

		$page_1_view = View::make( 'test' );
		$page_1_view->set_has_next_event( true );
		$page_1_view->setup_the_loop( [ 'posts_per_page' => 2, 'starts_after' => 'now' ] );

		$this->assertEquals( home_url() . '?post_type=tribe_events&eventDisplay=test&paged=2', $page_1_view->next_url() );

		$page_2_view = View::make( 'test' );
		$page_2_view->set_has_next_event( false );
		$page_2_view->setup_the_loop( [ 'posts_per_page' => 2, 'starts_after' => 'now', 'page' => 2 ] );

		$this->assertEquals( '', $page_2_view->next_url() );
	}

	/**
	 * It should correctly produce a view prev URLs
	 *
	 * @test
	 */
	public function should_correctly_produce_a_view_prev_url() {
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );
		$events = static::factory()->event->create_many( 3 );

		$page_2_view = View::make( 'test' );
		$page_2_view->setup_the_loop( [ 'paged' => 2, 'posts_per_page' => 2, 'starts_after' => 'now' ] );

		$this->assertEquals( home_url() . "?post_type=tribe_events&eventDisplay=test", $page_2_view->prev_url() );

		$page_1_view = View::make( 'test' );
		$page_1_view->setup_the_loop( [ 'posts_per_page' => 2, 'starts_after' => 'now' ] );

		$this->assertEquals( '', $page_1_view->prev_url() );
	}

	/**
	 * It should correctly produce a view prev and next canonical URLs
	 *
	 * @test
	 */
	public function should_correctly_produce_a_view_prev_and_next_canonical_urls() {
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );
		$events = static::factory()->event->create_many( 3 );

		$page_2_view = View::make( 'test' );
		$page_2_view->setup_the_loop( [ 'posts_per_page' => 2, 'starts_after' => 'now', 'paged' => 2 ] );

		$this->assertEquals( home_url() . '?post_type=tribe_events&eventDisplay=test', $page_2_view->prev_url() );

		$page_1_view = View::make( 'test' );
		$page_1_view->setup_the_loop( [ 'posts_per_page' => 2, 'starts_after' => 'now' ] );

		$this->assertEquals( '', $page_1_view->prev_url() );
	}

	public static function wpSetUpBeforeClass() {
		static::factory()->event = new Event();
	}

	public function url_event_date_data_set() {
		return [
			'empty'      => [ '', false ],
			'2019-10-11' => [ '2019-10-11', '2019-10-11' ],
			'false'      => [ false, false ],
			'now'        => [ 'now', false ],
			'today'      => [ 'today', date( Dates::DBDATEFORMAT ) ],
		];
	}

	/**
	 * It should correctly set the url_event_date template var
	 *
	 * @test
	 * @dataProvider url_event_date_data_set
	 */
	public function should_correctly_set_the_url_event_date_template_var( $event_date, $expected ) {
		$view = View::make( Reflector_View::class );
		$view->set_context( tribe_context()->alter( [ 'event_date' => $event_date ] ) );
		$template_vars  = $view->get_template_vars();
		$url_event_date = $template_vars['url_event_date'];

		$this->assertEquals( $expected, $url_event_date );
	}

	/**
	 * It should return empty array if View URL is not valid
	 *
	 * @test
	 */
	public function should_return_empty_array_if_view_url_is_not_valid() {
		$invalid_url_view = new class extends View {
			public function get_url( $canonical = false, $force = false ) {
				return 'not-a-url';
			}
		};

		$this->assertEquals( [], $invalid_url_view->get_url_args() );
	}

	public function get_url_args_data_provider() {
		return [
			'no query args'      => [ 'http://wp.test', [] ],
			'one query arg'      => [ 'http://wp.test?foo=bar', [ 'foo' => 'bar' ] ],
			'two query args'     => [ 'http://wp.test/?foo=bar&bar=baz', [ 'foo' => 'bar', 'bar' => 'baz' ] ],
			'eventDisplay month' => [
				'http://wp.test/?post_type=tribe_events&eventDisplay=month',
				[ 'post_type' => 'tribe_events', 'eventDisplay' => 'month' ],
			],
			'many arguments'     => [
				'http://wp.test/?post_type=tribe_events&eventDisplay=month&tribe-bar-search=cabbage&tribe_events_cat=test',
				[ 'post_type' => 'tribe_events', 'eventDisplay' => 'month', 'tribe-bar-search' => 'cabbage', 'tribe_events_cat' => 'test' ],
			],
		];
	}

	/**
	 * It should return the correct view URL args
	 *
	 * @test
	 * @dataProvider get_url_args_data_provider
	 */
	public function should_return_the_correct_view_url_args( string $view_url, array $expected ) {
		$invalid_url_view = new class( null, $view_url ) extends View {
			protected $_view_url;

			public function __construct( Messages $messages = null, $view_url ) {
				parent::__construct( $messages );
				$this->_view_url = $view_url;
			}

			public function get_url( $canonical = false, $force = false ) {
				return $this->_view_url;
			}
		};

		$this->assertEquals( $expected, $invalid_url_view->get_url_args() );
	}

	/**
	 * It should correctly restore the loop
	 *
	 * @test
	 */
	public function should_correctly_restore_the_loop() {
		// Let's register the test view as legit View.
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );
		// Set up the pre-view render context.
		global $wp_query;
		$page_name              = 'some-test-page';
		$page_id                = static::factory()->post->create( [
			'post_type' => 'page',
			'post_name' => $page_name,
		] );
		$original_wp_query      = new \WP_Query( [ 'p' => $page_id ] );
		$wp_query               = $original_wp_query;
		$original_request_uri   = "/{$page_name}";
		$_SERVER['REQUEST_URI'] = $original_request_uri;

		$view = View::make( 'list' );
		$view->setup_the_loop();

		// We do not  care about the specifics, only that it changed.
		$this->assertNotSame( $original_wp_query, $GLOBALS['wp_query'] );
		$this->assertNotEquals( $original_request_uri, $_SERVER['REQUEST_URI'] );

		$view->restore_the_loop();

		$this->assertSame( $original_wp_query, $GLOBALS['wp_query'] );
		$this->assertEquals( $original_request_uri, $_SERVER['REQUEST_URI'] );
	}

	/**
	 * It should correctly restore the loop when set up with args
	 *
	 * @test
	 */
	public function should_correctly_restore_the_loop_when_set_up_with_args() {
		// Let's register the test view as legit View.
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );
		// Set up the pre-view render context.
		global $wp_query;
		$page_name              = 'some-test-page';
		$page_id                = static::factory()->post->create( [
			'post_type' => 'page',
			'post_name' => $page_name,
		] );
		$original_wp_query      = new \WP_Query( [ 'p' => $page_id ] );
		$wp_query               = $original_wp_query;
		$original_request_uri   = "/{$page_name}";
		$_SERVER['REQUEST_URI'] = $original_request_uri;

		$view = View::make( 'list' );
		$view->setup_the_loop( [ 'eventDate' => '2020-01-01', 'tribe-bar-search' => 'lorem' ] );

		// We do not  care about the specifics, only that it changed.
		$this->assertNotSame( $original_wp_query, $GLOBALS['wp_query'] );
		$this->assertNotEquals( $original_request_uri, $_SERVER['REQUEST_URI'] );

		$view->restore_the_loop();

		$this->assertSame( $original_wp_query, $GLOBALS['wp_query'] );
		$this->assertEquals( $original_request_uri, $_SERVER['REQUEST_URI'] );
	}


	/**
	 * Should correctly filter the two repository args.
	 *
	 * @test
	 */
	public function should_correctly_filter_repository_args() {
		$expected_primary_args = [ 'primary_test_arg' => 'fake' ];
		$expected_global_args  = [ 'global_test_arg' => 'fake' ];
		add_filter( 'tribe_events_views', static function () {
			return [ 'test' => Test_View::class ];
		} );
		// Add a global arg.
		add_filter( 'tec_events_views_v2_view_global_repository_args',
			function ( $args, $view ) use ( $expected_global_args ) {
				return array_merge( $args, $expected_global_args );
			}, 10, 2 );
		// Add a primary arg.
		add_filter( 'tribe_events_views_v2_view_repository_args',
			function ( $args, $context ) use ( $expected_primary_args ) {
				return array_merge( $args, $expected_primary_args );
			}, 10, 2 );
		$view = View::make( 'test' );

		// Primary should fetch properly.
		$primary_args = $view->_public_repository_args();
		foreach ( $expected_primary_args as $expected_key => $expected_value ) {
			$this->assertArrayHasKey( $expected_key, $primary_args );
			$this->assertEquals( $expected_value, $primary_args[ $expected_key ] );
		}
		// Primary should also fetch the global arg.
		foreach ( $expected_global_args as $expected_key => $expected_value ) {
			$this->assertArrayHasKey( $expected_key, $primary_args );
			$this->assertEquals( $expected_value, $primary_args[ $expected_key ] );
		}
		// Globals should fetch properly.
		$global_args = $view->_public_global_repository_args();
		foreach ( $expected_global_args as $expected_key => $expected_value ) {
			$this->assertArrayHasKey( $expected_key, $global_args );
			$this->assertEquals( $expected_value, $global_args[ $expected_key ] );
		}
		// Globals should not contain primary.
		foreach ( $expected_primary_args as $expected_key => $expected_value ) {
			$this->assertArrayNotHasKey( $expected_key, $global_args );
		}
	}
}
