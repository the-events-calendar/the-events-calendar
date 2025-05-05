<?php

namespace Tribe\Events\Integrations\QR;

use Codeception\TestCase\WPTestCase;
use TEC\Events\QR\Settings;
use TEC\Events\QR\Controller;

/**
 * Tests QR Settings functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class SettingsTest extends WPTestCase {

	/**
	 * The settings instance.
	 *
	 * @var \TEC\Events\QR\Settings
	 */
	protected $settings;

	/**
	 * The option slugs.
	 *
	 * @var array
	 */
	protected $slugs;

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();

		// Get option slugs once
		$this->slugs = Settings::get_option_slugs();

		// Register the controller first
		$controller = tribe( Controller::class );
		$controller->register();

		// Initialize settings
		$this->settings = tribe( Settings::class );
	}

	/**
	 * Test that the settings class exists
	 *
	 * @test
	 */
	public function test_settings_class_exists() {
		$this->assertInstanceOf( Settings::class, $this->settings );
	}

	/**
	 * Test that the settings class has the correct option slugs
	 *
	 * @test
	 */
	public function test_settings_option_slugs() {
		$this->assertArrayHasKey( 'enabled', $this->slugs );
		$this->assertEquals( 'tribe-events-qr-code-enabled', $this->slugs['enabled'] );
		$this->assertArrayHasKey( 'size', $this->slugs );
		$this->assertEquals( 'tribe-events-qr-size', $this->slugs['size'] );
		$this->assertArrayHasKey( 'redirection', $this->slugs );
		$this->assertEquals( 'tribe-events-qr-redirection-behavior', $this->slugs['redirection'] );
		$this->assertArrayHasKey( 'event_id', $this->slugs );
		$this->assertEquals( 'tribe-events-qr-specific-event-id', $this->slugs['event_id'] );
		$this->assertArrayHasKey( 'series_id', $this->slugs );
		$this->assertEquals( 'tribe-events-qr-next-event-in-series-id', $this->slugs['series_id'] );
	}

	/**
	 * Test that the settings class has the correct default values
	 *
	 * @test
	 */
	public function test_settings_default_values() {
		$size        = tribe_get_option( $this->slugs['size'], '250x250' );
		$redirection = tribe_get_option( $this->slugs['redirection'], 'current' );
		$event_id    = tribe_get_option( $this->slugs['event_id'], '' );
		$series_id   = tribe_get_option( $this->slugs['series_id'], '' );

		$this->assertEquals( '250x250', $size );
		$this->assertEquals( 'current', $redirection );
		$this->assertEquals( '', $event_id );
		$this->assertEquals( '', $series_id );
	}
}
