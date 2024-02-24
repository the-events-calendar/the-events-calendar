<?php

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Reflector_View;
use Tribe\Events\Test\Factories\Event;

// Include a Test View Class
require_once codecept_data_dir( 'Views/V2/classes/Test_View.php' );
require_once codecept_data_dir( 'Views/V2/classes/Publicly_Visible_Test_View.php' );

class ManagerTest extends \Codeception\TestCase\WPTestCase {
	public function setUp() {
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		static::factory()->event = new Event();
	}

	public static function wpSetUpBeforeClass() {
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

		$this->assertEquals( $default, Reflector_View::class );
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

		$this->assertEquals( $default, Test_View::class );
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

		$this->assertEquals( $default, Test_View::class );
	}

	/**
	 * @test
	 */
	public function should_allow_getting_the_slug_currently_associated_to_a_view() {
		$manager = $this->make_instance();

		add_filter( 'tribe_events_views', '__return_empty_array' );
		$this->assertFalse( $manager->get_view_slug_by_class( Test_View::class ) );

		add_filter( 'tribe_events_views', function () {
			return [ 'test' => Test_View::class ];
		}, 11 );
		$this->assertEquals( 'test', $manager->get_view_slug_by_class( Test_View::class ) );

		add_filter( 'tribe_events_views', '__return_empty_array', 12 );
		$this->assertFalse( $manager->get_view_slug_by_class( Test_View::class ) );
	}

	/**
	 * @test
	 */
	public function should_only_return_publicly_visible_views_when_requested() {
		// Set publicly-visible-test as an enabled view.
		add_filter( 'tribe_get_option', function ( $value, $optionName, $default ) {

			if ( 'tribeEnableViews' !== $optionName ) {
				return $value;
			}

			return [ 'publicly-visible-test' ];
		}, 10, 3 );

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

		add_filter( 'tribe_events_views', '__return_empty_array', 11 );
		$this->assertEmpty( $manager->get_publicly_visible_views() );
	}

	/**
	 * @test
	 */
	public function should_create_view_register_objects_when_registering_a_view() {
		$manager = $this->make_instance();
		$manager->register_view( 'test', 'Test View', List_View::class, 10 );
		$view_register_objects = $manager->get_view_registration_objects();

		$this->assertCount( 1, $view_register_objects );
		$this->assertEquals( View_Register::class, get_class( reset( $view_register_objects ) ) );

		$registered_views = $manager->get_registered_views();
		$this->assertContains( 'test', array_keys( $registered_views ) );
	}
}
