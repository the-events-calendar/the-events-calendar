<?php

namespace Tribe\Events\Integrations\QR;

/**
 * Tests QR Shortcode functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class ShortcodeTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \TEC\Events\QR\Shortcode
	 */
	protected $shortcode;

	/**
	 * @var int
	 */
	protected $test_event_id;

	function setUp() {
		parent::setUp();

		// Enable QR
		$slugs = \TEC\Events\QR\Settings::get_option_slugs();
		tribe_update_option($slugs['enabled'], true);

		// Register the controller to ensure shortcode is available
		$controller = tribe(\TEC\Events\QR\Controller::class);
		$controller->do_register();

		// Initialize shortcode
		$this->shortcode = tribe(\TEC\Events\QR\Shortcode::class);

		// Create a test event that can be used across tests
		$this->test_event_id = $this->factory->post->create([
			'post_type' => 'tribe_events',
			'post_status' => 'publish'
		]);
	}

	/**
	 * Test that the shortcode slug is correct
	 *
	 * @test
	 */
	public function test_shortcode_slug() {
		$this->assertEquals('tec_event_qr', $this->shortcode->get_registration_slug());
	}

	/**
	 * Test that default arguments are set correctly
	 *
	 * @test
	 */
	public function test_default_arguments() {
		$defaults = $this->shortcode->get_default_arguments();

		$this->assertArrayHasKey('mode', $defaults);
		$this->assertArrayHasKey('id', $defaults);
		$this->assertArrayHasKey('size', $defaults);

		$this->assertEquals('current', $defaults['mode']);
		$this->assertEquals('', $defaults['id']);
		$this->assertEquals(6, $defaults['size']);
	}

	/**
	 * Test that argument validation is set up correctly
	 *
	 * @test
	 */
	public function test_argument_validation() {
		$validation_map = $this->shortcode->validate_arguments_map;

		$this->assertArrayHasKey('id', $validation_map);
		$this->assertArrayHasKey('mode', $validation_map);
		$this->assertArrayHasKey('size', $validation_map);

		$this->assertEquals('tribe_post_exists', $validation_map['id']);
		$this->assertEquals('sanitize_title_with_dashes', $validation_map['mode']);
		$this->assertEquals('absint', $validation_map['size']);
	}
}
