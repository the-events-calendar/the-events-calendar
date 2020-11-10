<?php

namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as Main;
use Tribe__Settings_Manager as Settings;

class Widget_BootstrapTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
		// Let's make sure we do not run "second" tests on cached value(s).
		tribe_set_var( Settings::OPTION_CACHE_VAR_NAME, null );
	}

	public function make_v1() {
		add_filter( 'tribe_events_views_v2_is_enabled', '__return_false' );
	}

	public function make_v2() {
		add_filter( 'tribe_events_views_v2_is_enabled', '__return_true' );
	}

	public function make_widget_v1() {
		if ( ! defined( 'TRIBE_EVENTS_WIDGETS_V2_ENABLED' ) ) {
			define( 'TRIBE_EVENTS_WIDGETS_V2_ENABLED', false );
		}

	}

	public function make_widget_v2() {
		if ( ! defined( 'TRIBE_EVENTS_WIDGETS_V2_ENABLED' ) ) {
			define( 'TRIBE_EVENTS_WIDGETS_V2_ENABLED', true );
		}
	}

	/**
	 * @test
	 *
	 * It should return true by default.
	 */
	public function it_should_return_true_by_default() {
		$this->assertTrue( tribe_events_widgets_v2_is_enabled() );
	}

	/**
	 * @test
	 *
	 * It should return true when v2 view is enabled.
	 */
	public function it_should_return_true_when_view_enabled() {
		$this->make_v2();

		$this->assertTrue( tribe_events_widgets_v2_is_enabled() );
	}

	/**
	 * @test
	 *
	 * It should return true when v2 widget is enabled.
	 */
	public function it_should_return_true_when_widget_enabled() {
		$this->make_widget_v2();

		$this->assertTrue( tribe_events_widgets_v2_is_enabled() );
	}

	/**
	 * @test
	 *
	 * It should return true when v2 view and widget are enabled.
	 */
	public function it_should_return_true_when_view_and_widget_enabled() {
		$this->make_v2();
		$this->make_widget_v2();

		$this->assertTrue( tribe_events_widgets_v2_is_enabled() );
	}

	/**
	 * @test
	 *
	 * It should return false when v2 view is disabled.
	 */
	public function it_should_return_false_when_view_disabled() {
		$this->make_v1();

		$this->assertFalse( tribe_events_widgets_v2_is_enabled() );
	}

	/**
	 * @test
	 *
	 * It should return false when v2 widget is disabled.
	 */
	public function it_should_return_false_when_widget_disabled() {
		$this->make_widget_v1();

		$this->assertFalse( tribe_events_widgets_v2_is_enabled() );
	}

	/**
	 * @test
	 *
	 * It should return false when v2 view and widget are disabled.
	 */
	public function it_should_return_false_when_view_and_widget_disabled() {
		$this->make_v1();
		$this->make_widget_v1();

		$this->assertFalse( tribe_events_widgets_v2_is_enabled() );
	}

	/**
	 * @test
	 *
	 * It should return false when v2 view is disabled and v2 widget is enabled.
	 */
	public function it_should_return_false_when_view_disabled_and_widget_enabled() {
		$this->make_v1();
		$this->make_widget_v2();

		$this->assertFalse( tribe_events_widgets_v2_is_enabled() );
	}

	/**
	 * @test
	 *
	 * It should return false when v2 view is enabled and v2 widget is disabled.
	 */
	public function it_should_return_false_when_view_enabled_and_widget_disabled() {
		$this->make_v2();
		$this->make_widget_v1();

		$this->assertFalse( tribe_events_widgets_v2_is_enabled() );
	}

}
