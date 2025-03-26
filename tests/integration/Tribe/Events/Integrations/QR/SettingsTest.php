<?php

namespace Tribe\Events\Integrations\QR;

/**
 * Tests QR Settings functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class SettingsTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \TEC\Events\QR\Settings
	 */
	protected $settings;

	function setUp() {
		parent::setUp();

		// Enable QR
		$slugs = \TEC\Events\QR\Settings::get_option_slugs();
		tribe_update_option($slugs['enabled'], true);

		// Register the controller first
		$controller = tribe(\TEC\Events\QR\Controller::class);
		$controller->do_register();

		// Initialize settings
		$this->settings = tribe(\TEC\Events\QR\Settings::class);
	}

	/**
	 * Test that the settings class exists
	 *
	 * @test
	 */
	public function test_settings_class_exists() {
		$this->assertInstanceOf(\TEC\Events\QR\Settings::class, $this->settings);
	}

	/**
	 * Test that the settings class has the correct option slugs
	 *
	 * @test
	 */
	public function test_settings_option_slugs() {
		$slugs = \TEC\Events\QR\Settings::get_option_slugs();

		$this->assertArrayHasKey('enabled', $slugs);
		$this->assertEquals('tribe-events-qr-code-enabled', $slugs['enabled']);
		$this->assertArrayHasKey('prefix', $slugs);
		$this->assertEquals('tribe-events-qr-prefix', $slugs['prefix']);
		$this->assertArrayHasKey('size', $slugs);
		$this->assertEquals('tribe-events-qr-size', $slugs['size']);
		$this->assertArrayHasKey('redirection', $slugs);
		$this->assertEquals('tribe-events-qr-redirection-behavior', $slugs['redirection']);
		$this->assertArrayHasKey('specific', $slugs);
		$this->assertEquals('tribe-events-qr-specific-event-id', $slugs['specific']);
		$this->assertArrayHasKey('fallback', $slugs);
		$this->assertEquals('tribe-events-qr-fallback', $slugs['fallback']);
	}

	/**
	 * Test that the settings class has the correct default values
	 *
	 * @test
	 */
	public function test_settings_default_values() {
		$slugs = \TEC\Events\QR\Settings::get_option_slugs();
		$enabled = tribe_get_option($slugs['enabled'], true);
		$prefix = tribe_get_option($slugs['prefix'], 'qr');
		$size = tribe_get_option($slugs['size'], '250x250');
		$redirection = tribe_get_option($slugs['redirection'], 'current_event');
		$specific = tribe_get_option($slugs['specific'], '');
		$fallback = tribe_get_option($slugs['fallback'], '');

		$this->assertTrue($enabled);
		$this->assertEquals('qr', $prefix);
		$this->assertEquals('250x250', $size);
		$this->assertEquals('current_event', $redirection);
		$this->assertEquals('', $specific);
		$this->assertEquals('', $fallback);
	}

	/**
	 * Test that the settings class can be enabled
	 *
	 * @test
	 */
	public function test_settings_can_be_enabled() {
		$slugs = \TEC\Events\QR\Settings::get_option_slugs();
		tribe_update_option($slugs['enabled'], true);

		$this->assertTrue($this->settings->is_enabled());
	}

	/**
	 * Test that the settings class can be disabled
	 *
	 * @test
	 */
	public function test_settings_can_be_disabled() {
		$slugs = \TEC\Events\QR\Settings::get_option_slugs();
		tribe_update_option($slugs['enabled'], false);

		$this->assertFalse($this->settings->is_enabled());
	}

	/**
	 * Test that the settings class can be enabled through a filter
	 *
	 * @test
	 */
	public function test_settings_enabled_state_can_be_filtered() {
		add_filter('tec_events_qr_code_enabled', '__return_true');

		$this->assertTrue($this->settings->is_enabled());

		remove_filter('tec_events_qr_code_enabled', '__return_true');
	}

	/**
	 * Test that the settings class sanitizes the enabled state
	 *
	 * @test
	 */
	public function test_settings_enabled_state_is_sanitized() {
		$slugs = \TEC\Events\QR\Settings::get_option_slugs();
		tribe_update_option($slugs['enabled'], 'invalid_value');

		$this->assertFalse($this->settings->is_enabled());
	}
}
