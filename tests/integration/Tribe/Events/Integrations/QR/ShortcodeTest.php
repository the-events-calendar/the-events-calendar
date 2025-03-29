<?php

namespace Tribe\Events\Integrations\QR;

use Codeception\TestCase\WPTestCase;
use TEC\Events\QR\Settings;
use TEC\Events\QR\Controller;
use TEC\Events\QR\Shortcode;

/**
 * Tests QR Shortcode functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class ShortcodeTest extends WPTestCase {

	/**
	 * The shortcode instance.
	 *
	 * @var \TEC\Events\QR\Shortcode
	 */
	protected $shortcode;

	/**
	 * The test event ID.
	 *
	 * @var int
	 */
	protected $test_event_id;

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

		// Enable QR
		tribe_update_option( $this->slugs['enabled'], true );

		// Register the controller to ensure shortcode is available
		$controller = tribe( Controller::class );
		$controller->do_register();

		// Initialize shortcode
		$this->shortcode = tribe( Shortcode::class );

		// Create a test event that can be used across tests
		$this->test_event_id = $this->factory->post->create(
			[
				'post_type'   => 'tribe_events',
				'post_status' => 'publish',
			]
		);
	}

	/**
	 * Test that the shortcode slug is correct
	 *
	 * @test
	 */
	public function test_shortcode_slug() {
		$this->assertEquals( 'tec_event_qr', Settings::get_qr_slug() );
	}

	/**
	 * Test that default arguments are set correctly
	 *
	 * @test
	 */
	public function test_default_arguments() {
		$defaults = $this->shortcode->get_default_arguments();

		$this->assertArrayHasKey( 'mode', $defaults );
		$this->assertArrayHasKey( 'id', $defaults );
		$this->assertArrayHasKey( 'size', $defaults );

		$this->assertEquals( '', $defaults['mode'] );
		$this->assertEquals( '', $defaults['id'] );
		$this->assertEquals( '', $defaults['size'] );

		$this->assertEquals( tribe_get_option( $this->slugs['redirection'] ), '', 'Redirection should be empty' );
		$this->assertEquals( tribe_get_option( $this->slugs['size'] ), '', 'Size should be empty' );
		$this->assertEquals( tribe_get_option( $this->slugs['event_id'] ), '', 'Event ID should be empty' );
		$this->assertEquals( tribe_get_option( $this->slugs['series_id'] ), '', 'Series ID should be empty' );
	}

	/**
	 * Test that argument validation is set up correctly
	 *
	 * @test
	 */
	public function test_argument_validation() {
		$validation_map = $this->shortcode->validate_arguments_map;

		$this->assertArrayHasKey( 'id', $validation_map );
		$this->assertArrayHasKey( 'mode', $validation_map );
		$this->assertArrayHasKey( 'size', $validation_map );

		$this->assertEquals( 'tribe_post_exists', $validation_map['id'] );
		$this->assertEquals( 'sanitize_title_with_dashes', $validation_map['mode'] );
		$this->assertEquals( 'absint', $validation_map['size'] );
	}
}
