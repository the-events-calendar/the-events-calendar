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
		// Variable to hold if the callback for the filter has been called yet.
		$called = false;

		// Callback function for the filter. Changes the $called variable to true and returns true for whether to bypass.
		$override_callback = function () use ( &$called ) {
			$called = true;
			return true;
		};

		// Set up the filter with the callback function.
		add_filter( 'tec_events_integration_elementor_bypass_template_override', $override_callback, 10 );

		// Create an instance of the Elementor_Template_Controller
		$controller = tribe( Elementor_Template_Controller::class );

		// Call the function where the filter is bound.
		$is_overridden = $controller->is_override();

		// Assertions.
		$this->assertFalse( $is_overridden ); // Since we returned true to bypassing the function, we expect it not to be overridden.
		$this->assertTrue( $called ); // This confirms that the filter was applied because the callback function changed the variable.

		// Clean up.
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
