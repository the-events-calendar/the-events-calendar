<?php

namespace Tribe\Events\Views\V2;

use Tribe\Events\Test\Factories\Event;
use Tribe__Context as Context;

// Include a Test View Class
require_once codecept_data_dir( 'Views/V2/classes/Test_View.php' );
require_once codecept_data_dir( 'Views/V2/classes/Publicly_Visible_Test_View.php' );

class ManageTest extends \Codeception\TestCase\WPTestCase {
	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
	}

	public function wpSetUpBeforeClass() {
		static::factory()->event = new Event();
	}

	/**
	 * @return Manager
	 */
	private function make_instance() {
		return new Manager();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Manager::class, $sut );
	}

	/**
	 * @test
	 */
	public function should_default_to_the_reflector_view_when_no_views_are_availble() {
		tribe_update_option( Manager::$option_default, 'test' );

		add_filter( 'tribe_events_views', '__return_empty_array' );

		$default = $this->make_instance()->get_default_view();

		$this->assertEquals( $default, 'reflector' );
	}

	/**
	 * @test
	 */
	public function should_default_to_the_first_view_available_when_requested_is_not_available() {
		tribe_update_option( Manager::$option_default, 'foo' );

		add_filter( 'tribe_events_views', function() {
			return [ 'test' => Test_View::class ];
		} );

		$default = $this->make_instance()->get_default_view();

		$this->assertEquals( $default, 'test' );
	}

	/**
	 * @test
	 */
	public function should_default_to_what_is_in_the_option_when_available() {
		tribe_update_option( Manager::$option_default, 'test' );

		add_filter( 'tribe_events_views', function() {
			return [ 'test' => Test_View::class ];
		} );

		$default = $this->make_instance()->get_default_view();

		$this->assertEquals( $default, 'test' );
	}

	/**
	 * @test
	 */
	public function should_allow_getting_the_slug_currently_associated_to_a_view() {
		$manager = $this->make_instance();

		add_filter( 'tribe_events_views', '__return_empty_array' );
		$this->assertFalse( $manager->get_view_slug( Test_View::class ) );

		add_filter( 'tribe_events_views', function () {
			return [ 'test' => Test_View::class ];
		} );
		$this->assertEquals( 'test', $manager->get_view_slug( Test_View::class ) );

		add_filter( 'tribe_events_views', function () {
			return [];
		} );
		$this->assertFalse( $manager->get_view_slug( Test_View::class ) );
	}

	/**
	 * @test
	 */
	public function should_only_return_publicly_visible_views_when_requested() {
		$manager = $this->make_instance();

		add_filter( 'tribe_events_views', '__return_empty_array' );
		$this->assertEmpty( $manager->get_publicly_visible_views() );

		add_filter( 'tribe_events_views', function () {
			return [
				'test' => Test_View::class,
				'publicly-visible-test' => Publicly_Visible_Test_View::class,
			];
		} );
		$this->assertArrayHasKey( 'publicly-visible-test', $manager->get_publicly_visible_views() );
		$this->assertArrayNotHasKey( 'test', $manager->get_publicly_visible_views() );

		add_filter( 'tribe_events_views', '__return_empty_array' );
		$this->assertEmpty( $manager->get_publicly_visible_views() );
	}

}
