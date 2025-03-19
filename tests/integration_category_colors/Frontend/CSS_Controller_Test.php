<?php
/**
 * Test the CSS Controller functionality.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Category_Colors\CSS\Controller as CSS_Controller;

class CSS_Controller_Test extends WPTestCase {
	/**
	 * @var CSS_Controller
	 */
	protected $css_controller;

	/**
	 * @before
	 */
	public function setup_test_environment(): void {
		$this->css_controller = tribe(CSS_Controller::class);
	}

	/**
	 * @test
	 */
	public function should_register_hooks() {
		// Register the controller
		$this->css_controller->register();

		// Check if the required hooks are added
		$this->assertTrue(has_action('wp_enqueue_scripts', [$this->css_controller, 'enqueue_styles']));
	}
} 