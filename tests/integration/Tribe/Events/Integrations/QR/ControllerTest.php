<?php

namespace Tribe\Events\Integrations\QR;

use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\QR\Controller;
use TEC\Events\QR\Routes;
use TEC\Events\QR\Redirections;
use TEC\Events\QR\Shortcode;

/**
 * Tests QR Controller functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class ControllerTest extends Controller_Test_Case {

	/**
	 * The controller class to test.
	 *
	 * @var string
	 */
	protected $controller_class = \TEC\Events\QR\Controller::class;

	/**
	 * The controller instance.
	 *
	 * @var \TEC\Events\QR\Controller
	 */
	protected $controller;
	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
		$this->controller = tribe( Controller::class );

		// Register the controller by default
		$this->controller->register();
	}

	/**
	 * Test that the controller slug is correct
	 *
	 * @test
	 */
	public function test_controller_slug() {
		$this->assertEquals( 'tec_event_qr', $this->controller->get_slug() );
	}

	/**
	 * Test that the controller registers the shortcode
	 *
	 * @test
	 */
	public function test_controller_registers_shortcode() {
		// Check that the shortcode is registered
		$shortcodes = apply_filters( 'tribe_shortcodes', [] );
		$this->assertArrayHasKey( 'tec_event_qr', $shortcodes );
		$this->assertEquals( Shortcode::class, $shortcodes['tec_event_qr'] );
	}

	/**
	 * Test that the controller adds valid pages for QR notices
	 *
	 * @test
	 */
	public function test_controller_adds_valid_pages() {
		$valid_pages = apply_filters( 'tec_qr_notice_valid_pages', [] );

		$this->assertContains( 'tec-events-settings', $valid_pages );
		$this->assertContains( 'tec-events-help-hub', $valid_pages );
		$this->assertContains( 'tec-troubleshooting', $valid_pages );
	}

	/**
	 * Test that the controller registers the needed classes
	 *
	 * @test
	 */
	public function test_controller_registers_classes() {
		$routes = tribe( Routes::class );
		$this->assertInstanceOf( Routes::class, $routes );

		$redirections = tribe( Redirections::class );
		$this->assertInstanceOf( Redirections::class, $redirections );
	}

	/**
	 * Test that the controller properly unregisters hooks
	 *
	 * @test
	 */
	public function test_controller_unregisters_hooks() {
		// Then unregister them
		$this->controller->unregister();

		// Check that the shortcode is no longer registered
		$shortcodes = apply_filters( 'tribe_shortcodes', []);
		$this->assertArrayNotHasKey( 'tec_event_qr', $shortcodes);

		// Check that the valid pages filter is removed
		$valid_pages = apply_filters( 'tec_qr_notice_valid_pages', [] );
		$this->assertNotContains( 'tec-events-settings', $valid_pages );
		$this->assertNotContains( 'tec-events-help-hub', $valid_pages );
		$this->assertNotContains( 'tec-troubleshooting', $valid_pages );
	}
}
