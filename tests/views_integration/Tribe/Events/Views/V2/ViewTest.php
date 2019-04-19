<?php

namespace Tribe\Events\Views\V2;

require_once codecept_data_dir( 'Views/V2/classes/Test_View.php' );

class ViewTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
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
		return new View();
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

		$request = new \WP_REST_Request();
		$request['view'] = 'test';
		$view = View::make_for_rest( $request );
		$this->assertInstanceOf( Test_View::class, $view );
	}

	/**
	 * It should print a view HTML on the page when caling send_html
	 *
	 * @test
	 */
	public function should_print_a_view_html_on_the_page_when_caling_send_html() {
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
}