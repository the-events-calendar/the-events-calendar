<?php

namespace TEC\Events\Integrations\Plugins\Elementor;

use Tribe__Events__Main as TEC;
use TEC\Events\Integrations\Plugins\Elementor\Template\Controller as Elementor_Template_Controller;

class ControllerTest extends \Codeception\TestCase\WPTestCase {
	public function setUp() {
		parent::setUp();
		tribe()->register( Elementor_Template_Controller::class );

		// Create an event using The Events Calendar ORM before each test
		$event_data = [
			'title' => 'Sample Event',
			'content' => 'This is a sample event description.',
			'start_date' => '2024-09-23 01:00:00',
			'end_date' => '2024-09-23 05:00:00',
		];

		$this->event_test = tribe_events()->set_args($event_data)->create();

	}

	public function tearDown(): void {
		tribe( Elementor_Template_Controller::class )->unregister();
		// Delete the event after each test
		if ($this->event_test) {
			tribe_events()->delete($this->event_test->ID);
		}

		parent::tearDown();
	}

	/**
	 * It should allow the Elementor template override to be bypassed.
	 *
	 * @test
	 */
	public function should_allow_bypassing_override(): void {
		// Set up filter to return true.
		add_filter('tec_events_integration_elementor_bypass_template_override', '__return true', 10);

		// Create an instance of the Elementor_Template_Controller
		$container = tribe();
		$controller = new Elementor_Template_Controller($container);

		// Assert that `is_override` returns false and is not overriding the template.
		$is_overridden = $controller->is_override($this->event_test->ID);
		$this->assertFalse($is_overridden);

		/**
		 * @after
		 */
		remove_filter( 'tec_events_integration_elementor_bypass_template_override', '__return_false', 10 );
	}

	/**
	 * It should not bypass the Elementor template override if false is returned from the filter.
	 *
	 * @test
	 */
	public function should_not_bypass_override_when_false_is_returned(): void {
		// Set up filter to return false.
		add_filter('tec_events_integration_elementor_bypass_template_override', '__return false', 10);


		// Create an instance of the Elementor_Template_Controller with the required container
		$container = tribe();
		$controller = new Elementor_Template_Controller($container);

		// Assert that `is_override` returns true and is overriding the template.
		$is_overridden = $controller->is_override($this->event_test->ID);
		$this->assertTrue($is_overridden);
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
