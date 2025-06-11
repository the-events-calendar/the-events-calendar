<?php

namespace Tribe\Events\Integrations\QR;

use Codeception\TestCase\WPTestCase;
use TEC\Events\QR\QR_Code;
use TEC\Events\QR\Controller;
use Tribe__Events__Main as TEC;

/**
 * Tests QR Code functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class QR_CodeTest extends WPTestCase {

	/**
	 * The QR Code instance.
	 *
	 * @var \TEC\Events\QR\QR_Code
	 */
	protected $qr_code;

  /**
   * The controller instance.
   *
   * @var \TEC\Events\QR\Controller
   */
	protected $controller;

	/**
	 * Set up the test.
	 */
	public function setUp() {
		parent::setUp();

		// Create a mock controller
		$mock_controller = $this->createMock( Controller::class );
		$mock_controller->method( 'is_active' )->willReturn( true );
		$mock_controller->method( 'register' )->willReturn( null );
		$mock_controller->method( 'unregister' )->willReturn( null );

		// Register the mock controller in the container
		tribe()->singleton( Controller::class, $mock_controller );

		// Get the controller instance
		$this->controller = tribe( Controller::class );

		// Initialize QR Code
		$this->qr_code = tribe( QR_Code::class );
	}

	/**
	 * Tear down the test.
	 */
	public function tearDown() {
		parent::tearDown();
	}

  /**
   * Test that the admin table action is added
   *
   * @test
   */
  public function test_add_admin_table_action(): void {
    // Create a mock post
    $post = $this->factory()->post->create_and_get( [
      'post_type' => TEC::POSTTYPE,
      'post_title' => 'Test Event',
    ] );

    $actions = $this->qr_code->add_admin_table_action( [], $post );

    // Check that the action is added
    $this->assertArrayHasKey( 'tec_qr_code_modal', $actions );
  }

  /**
   * Test that the QR code meta box is added
   *
   * @test
   */
  public function test_add_qr_code_meta_box(): void {
    global $wp_meta_boxes;

    // Initialize the meta boxes array structure
    $wp_meta_boxes = [
      TEC::POSTTYPE => [
        'side' => [
          'default' => []
        ]
      ]
    ];

    // Check that the meta box is not present initially
    $this->assertArrayNotHasKey( 'tec-events-qr-code', $wp_meta_boxes[ TEC::POSTTYPE ]['side']['default'] );

    // Add the meta box
    $this->qr_code->add_qr_code_meta_box();

    // Check that the meta box is added
    $this->assertArrayHasKey( 'tec-events-qr-code', $wp_meta_boxes[ TEC::POSTTYPE ]['side']['default'] );
  }

  /**
   * Test that the image is generated when QR is enabled
   *
   * @test
   */
	public function test_image_is_generated_when_qr_enabled(): void {
    // Create a mock post
    $post = $this->factory()->post->create_and_get( [
      'post_type' => TEC::POSTTYPE,
      'post_title' => 'Test Event',
    ] );

		// Try to generate the image
		$img = $this->qr_code->generate_qr_image( $post->ID, 'https://www.google.com' );

		// Check that the image is generated
		$this->assertNotNull( $img );

    // Check that is an array
    $this->assertIsArray( $img );

    // Check that array has the correct keys
    $this->assertArrayHasKey( 'file', $img );
    $this->assertArrayHasKey( 'url', $img );
    $this->assertArrayHasKey( 'type', $img );
    $this->assertArrayHasKey( 'error', $img );

    // Check that the image is in the uploads directory
    $this->assertFileExists( $img['file'] );
	}

  /**
	 * Test that the image is not generated when QR is disabled
	 *
	 * @test
	 */
	public function test_image_is_not_generated_when_qr_disabled(): void {
    // Create a mock post
    $post = $this->factory()->post->create_and_get( [
        'post_type' => TEC::POSTTYPE,
        'post_title' => 'Test Event',
    ] );

    // Update the mock controller to return false for is_active()
    $mock_controller = $this->createMock( Controller::class );
    $mock_controller->method( 'is_active' )->willReturn( false );
    $mock_controller->method( 'register' )->willReturn( null );
    $mock_controller->method( 'unregister' )->willReturn( null );

    // Register the updated mock controller
    tribe()->singleton( Controller::class, $mock_controller );

    // Try to generate the image
    $img = $this->qr_code->generate_qr_image( $post->ID, 'https://www.google.com' );

    // Check that the image is not generated
    $this->assertNull( $img );

    // Restore the original mock controller
    $mock_controller = $this->createMock( Controller::class );
    $mock_controller->method( 'is_active' )->willReturn( true );
    $mock_controller->method( 'register' )->willReturn( null );
    $mock_controller->method( 'unregister' )->willReturn( null );
    tribe()->singleton( Controller::class, $mock_controller );
	}
}
