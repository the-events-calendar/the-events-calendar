<?php

namespace TEC\Events\Integrations\Plugins\Elementor;

use Tribe__Events__Main as TEC;
use TEC\Events\Integrations\Plugins\Elementor\Template\Controller as Elementor_Template_Controller;

class ControllerTest extends \Codeception\TestCase\WPTestCase {
	public function setUp() {
		parent::setUp();
		tribe()->register( Elementor_Template_Controller::class );
	}

	public function tearDown(): void {
		tribe( Elementor_Template_Controller::class )->unregister();
		parent::tearDown();
	}

	/**
	 * It should allow the Elementor template override to be bypassed.
	 *
	 * @test
	 */
	public function should_allow_bypassing_override(): void {
		// Set up filter to return true.
		// Assert that `is_override` returns false
	}

	/**
	 * It should not bypass the Elementor template override if false is returned from the filter.
	 *
	 * @test
	 */
	public function should_not_bypass_override_when_false_is_returned(): void {
		// Set up filter to return true.
		// Assert that `is_override` returns false
	}

	/**
	 * It should allow the Elementor template override to be bypassed on aspecific event.
	 *
	 * @test
	 */
	public function should_allow_bypassing_override_on_specific_event(): void {
		// Set up filter to only bypass on a specific event.
		// Assert that for that event `is_override` returns false
		// Assert that for another event `is_override` returns false
	}
}
