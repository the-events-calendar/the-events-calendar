<?php

namespace TEC\Events\Integrations\Plugins\Elementor;

use Tribe__Events__Main as TEC;
use TEC\Events\Integrations\Plugins\Elementor\Template\Controller as Elementor_Template_Controller;
use Tribe\Tests\Traits\With_Uopz;

class ControllerTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	/**
	 * It should allow the Elementor template override to be bypassed.
	 *
	 * @test
	 */
	public function should_allow_bypassing_override(): void {
		$called = false;
		$override_callback = function() use( &$called ) {
			$called = true;
			return true;
		};
		// Set up filter to return true.
		add_filter( 'tec_events_integration_elementor_bypass_template_override', $override_callback, 10 );

		// Create an instance of the Elementor_Template_Controller
		$controller  = tribe( Elementor_Template_Controller::class );

		$is_overridden = $controller->is_override();
		$this->assertFalse( $is_overridden );
		$this->assertTrue( $called );

		remove_filter( 'tec_events_integration_elementor_bypass_template_override', $override_callback, 10 );
	}

	/**
	 * It should allow the Elementor template override to be bypassed on a specific event.
	 *
	 * @test
	 */
	public function should_allow_bypassing_override_on_specific_event(): void {
		// Set up filter to only bypass on a specific event.
		// Assert that for that event `is_override` returns false
		// Assert that for another event `is_override` returns false
	}
}
