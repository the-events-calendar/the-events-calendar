<?php

namespace Tribe\Events\Integrations\QR;

use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\QR\Routes;

/**
 * Tests QR Routes functionality
 *
 * @group   core
 * @group   qr
 *
 * @package TribeEvents
 */
class RoutesTest extends Controller_Test_Case {

	/**
	 * The controller class to test.
	 *
	 * @var string
	 */
	protected $controller_class = Routes::class;

	/**
	 * The routes instance.
	 *
	 * @var \TEC\Events\QR\Routes
	 */
	protected $routes;

	/**
	 * The test event ID.
	 *
	 * @var int
	 */
	protected $test_event_id;

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();

		// Register the routes
		$this->routes = tribe( Routes::class );
		$this->routes->do_register();

		// Create a test event
		$this->test_event_id = $this->factory->post->create(
			[
				'post_type'   => 'tribe_events',
				'post_status' => 'publish',
			]
		);
	}

	/**
	 * Test that the default route prefix is correct
	 *
	 * @test
	 */
	public function test_route_prefix() {
		$this->assertEquals( 'qr', $this->routes->get_route_prefix() );
	}

	/**
	 * Test QR code URL generation
	 *
	 * @test
	 */
	public function test_get_qr_url() {
		$url = $this->routes->get_qr_url( $this->test_event_id, 'current' );

		$this->assertIsString( $url );
		$this->assertStringContainsString( 'events/qr/', $url );
		$this->assertStringNotContainsString( '=', $url ); // Should be base64url encoded
	}

	/**
	 * Test hash generation and decoding
	 *
	 * @test
	 */
	public function test_hash_generation_and_decoding() {
		$post_id = $this->test_event_id;
		$qr_type = 'current';

		// Generate hash
		$hash = $this->routes->generate_hash( $post_id, $qr_type );

		$this->assertIsString( $hash );
		$this->assertStringNotContainsString( '=', $hash ); // Should be base64url encoded

		// Decode hash
		$decoded = $this->routes->decode_qr_hash( $hash );

		$this->assertIsArray( $decoded );
		$this->assertArrayHasKey( 'post_id', $decoded );
		$this->assertArrayHasKey( 'qr_type', $decoded );
		$this->assertEquals( $post_id, $decoded['post_id'] );
		$this->assertEquals( $qr_type, $decoded['qr_type'] );
	}

	/**
	 * Test URL decoding
	 *
	 * @test
	 */
	public function test_decode_qr_url() {
		$post_id = $this->test_event_id;
		$qr_type = 'current';

		// Generate URL
		$url = $this->routes->get_qr_url( $post_id, $qr_type );

		// Decode URL
		$decoded = $this->routes->decode_qr_url( $url );

		$this->assertIsArray( $decoded );
		$this->assertArrayHasKey( 'post_id', $decoded );
		$this->assertArrayHasKey( 'qr_type', $decoded );
		$this->assertEquals( $post_id, $decoded['post_id'] );
		$this->assertEquals( $qr_type, $decoded['qr_type'] );
	}

	/**
	 * Test invalid hash handling
	 *
	 * @test
	 */
	public function test_invalid_hash_handling() {
		$this->expectException( \InvalidArgumentException::class );

		$this->routes->decode_qr_hash( 'invalid_hash' );
	}

	/**
	 * Test invalid URL handling
	 *
	 * @test
	 */
	public function test_invalid_url_handling() {
		$this->expectException( \InvalidArgumentException::class );

		$this->routes->decode_qr_url( 'https://example.com/invalid-url' );
	}

	/**
	 * Test decode QR hash
	 *
	 * @test
	 */
	public function test_decode_qr_hash(): void {
		$post_id = 123;
		$qr_type = 'specific';

		// Get the salt using reflection
		$reflection = new \ReflectionClass( $this->routes );
		$salt_property = $reflection->getProperty( 'salt' );
		$salt_property->setAccessible( true );
		$salt_property->getValue( $this->routes );

		$hash = $this->routes->generate_hash( $post_id, $qr_type );

		$decoded = $this->routes->decode_qr_hash( $hash );

		$this->assertEquals( $post_id, $decoded['post_id'] );
		$this->assertEquals( $qr_type, $decoded['qr_type'] );
	}
}
