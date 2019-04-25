<?php

namespace Tribe\Events\Views\V2;

use Tribe__Context as Context;

require_once codecept_data_dir( 'Views/V2/classes/Test_View.php' );

class ViewTest extends \Codeception\TestCase\WPTestCase {

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

	/**
	 * It should allow getting the slug currently associated to a View
	 *
	 * @test
	 */
	public function should_allow_getting_the_slug_currently_associated_to_a_view() {
		add_filter( 'tribe_events_views', function () {
			return [];
		} );
		$this->assertFalse( View::get_view_slug( Test_View::class ) );

		add_filter( 'tribe_events_views', function () {
			return [ 'test' => Test_View::class ];
		} );
		$this->assertEquals( 'test', View::get_view_slug( Test_View::class ) );

		add_filter( 'tribe_events_views', function () {
			return [];
		} );
		$this->assertFalse( View::get_view_slug( Test_View::class ) );
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

		$this->assertEquals( 'test', $view->get_slug() );
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
}