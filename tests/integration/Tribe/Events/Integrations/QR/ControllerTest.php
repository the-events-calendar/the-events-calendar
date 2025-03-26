<?php

namespace Tribe\Events\Integrations\QR;

use Codeception\TestCase\WPTestCase;
use TEC\Events\QR\Settings;
use TEC\Events\QR\Controller;
use TEC\Events\QR\Shortcode;

/**
 * Tests QR Controller functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class ControllerTest extends WPTestCase {

	/**
	 * @var \TEC\Events\QR\Controller
	 */
	protected $controller;

	/**
	 * @var array
	 */
	protected $slugs;

	function setUp() {
		parent::setUp();
		$this->controller = tribe(Controller::class);

		// Get option slugs once
		$this->slugs = Settings::get_option_slugs();

		// Enable QR by default
		tribe_update_option($this->slugs['enabled'], true);

		// Register the controller by default
		$this->controller->do_register();
	}

	/**
	 * Test that the controller slug is correct
	 *
	 * @test
	 */
	public function test_controller_slug() {
		$this->assertEquals('tec_event_qr', $this->controller->get_slug());
	}

	/**
	 * Test that the controller registers the shortcode
	 *
	 * @test
	 */
	public function test_controller_registers_shortcode() {
		// Check that the shortcode is registered
		$shortcodes = apply_filters('tribe_shortcodes', []);
		$this->assertArrayHasKey('tec_event_qr', $shortcodes);
		$this->assertEquals(Shortcode::class, $shortcodes['tec_event_qr']);
	}

	/**
	 * Test that the controller adds valid pages for QR notices
	 *
	 * @test
	 */
	public function test_controller_adds_valid_pages() {
		$valid_pages = apply_filters('tec_qr_notice_valid_pages', []);

		$this->assertContains('tec-events-settings', $valid_pages);
		$this->assertContains('tec-events-help-hub', $valid_pages);
		$this->assertContains('tec-troubleshooting', $valid_pages);
	}

	/**
	 * Test that the controller registers settings
	 *
	 * @test
	 */
	public function test_controller_registers_settings() {
		// Check that the settings are registered
		$settings = tribe(Settings::class);
		$this->assertInstanceOf(Settings::class, $settings);
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
		$shortcodes = apply_filters('tribe_shortcodes', []);
		$this->assertArrayNotHasKey('tec_event_qr', $shortcodes);

		// Check that the valid pages filter is removed
		$valid_pages = apply_filters('tec_qr_notice_valid_pages', []);
		$this->assertNotContains('tec-events-settings', $valid_pages);
		$this->assertNotContains('tec-events-help-hub', $valid_pages);
		$this->assertNotContains('tec-troubleshooting', $valid_pages);
	}

	/**
	 * Test that the controller can be used when QR is enabled
	 *
	 * @test
	 */
	public function test_controller_can_use_when_qr_enabled() {
		// Check that the shortcode is registered
		$shortcodes = apply_filters('tribe_shortcodes', []);
		$this->assertArrayHasKey('tec_event_qr', $shortcodes);
	}

	/**
	 * Test that the controller cannot be used when QR is disabled
	 *
	 * @test
	 */
	public function test_controller_cannot_use_when_qr_disabled() {
		tribe_update_option($this->slugs['enabled'], false);

		// Re-register the controller to test disabled state
		$this->controller->do_register();

		// Check that the shortcode is not registered
		$shortcodes = apply_filters('tribe_shortcodes', []);
		$this->assertArrayNotHasKey('tec_event_qr', $shortcodes);
	}
}
