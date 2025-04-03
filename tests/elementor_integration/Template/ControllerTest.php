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
	 * It should pass the $bypass parameter to the filter.
	 *
	 * @test
	 */
	public function should_pass_bypass_parameter_to_filter(): void {
		// Variable to capture the passed $bypass value.
		$passed_bypass = null;

		// Callback function for the filter. Captures the $bypass parameter.
		$override_callback = function ( $bypass ) use ( &$passed_bypass ) {
			$passed_bypass = $bypass;
			return $bypass;
		};

		// Set up the filter with the callback function.
		add_filter( 'tec_events_integration_elementor_bypass_template_override', $override_callback, 10, 1 );

		// Create an instance of the Elementor_Template_Controller
		$controller = tribe( Elementor_Template_Controller::class );

		// Call the function where the filter is bound.
		$controller->is_override();

		// Assertions.
		$this->assertFalse( $passed_bypass ); // Default value of $bypass should be false.

		// Clean up.
		remove_filter( 'tec_events_integration_elementor_bypass_template_override', $override_callback, 10 );
	}

	/**
	 * It should pass the $post_id parameter to the filter.
	 *
	 * @test
	 */
	public function should_pass_post_id_parameter_to_filter(): void {
		// Variable to capture the passed $post_id value.
		$passed_post_id = null;

		// Callback function for the filter. Captures the $post_id parameter.
		$override_callback = function ( $bypass, $post_id ) use ( &$passed_post_id ) {
			$passed_post_id = $post_id;
			return $bypass;
		};

		// Set up the filter with the callback function.
		add_filter( 'tec_events_integration_elementor_bypass_template_override', $override_callback, 10, 2 );

		// Create an instance of the Elementor_Template_Controller
		$controller = tribe( Elementor_Template_Controller::class );

		// Define a specific post ID to check.
		$specific_post_id = 123;

		// Call the function where the filter is bound with the specific post ID.
		$controller->is_override( $specific_post_id );

		// Assertions.
		$this->assertEquals( $specific_post_id, $passed_post_id ); // This confirms that the specific post ID was passed to the filter.

		// Clean up.
		remove_filter( 'tec_events_integration_elementor_bypass_template_override', $override_callback, 10 );
	}

}
