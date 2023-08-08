<?php
namespace Tribe\Events\Views\V2;

class Rest_EndpointTest extends \Codeception\TestCase\WPTestCase {
	private function make_instance() {
		return new Rest_Endpoint();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Rest_Endpoint::class, $sut );
	}

	/**
	 * @test
	 */
	public function it_should_have_correct_url_for_available_rest_api() {
		$rest = $this->make_instance();

		$this->assertContains( 'tribe/views/v2/html', $rest->get_url() );
	}

	/**
	 * @test
	 */
	public function it_should_fallback_to_ajax_url_when_rest_not_available() {
		$rest = $this->make_instance();

		add_filter( 'tribe_events_views_v2_rest_endpoint_available', '__return_false' );

		$this->assertContains( 'admin-ajax.php', $rest->get_url() );
	}

	public function arguments_sanitize_data_provider() {
		return [
			'not_supported_param_should_be_excluded' => [
				[
					'not-support' => true,
					'url' => home_url(),
				],
				[
					'url' => home_url(),
				],
			],
		];
	}

	/**
	 * @test
	 * @dataProvider arguments_sanitize_data_provider
	 */
	public function it_should_filter_and_sanitize_params( $input, $expected ) {
		$rest = $this->make_instance();
		$request = $rest->get_mocked_rest_request( $input );

		$this->assertEquals( $expected, $request->get_params() );
	}

	/**
	 * Validate our REST nonces are generated as expected.
	 *
	 * @test
	 */
	public function it_should_generate_nonces() {
		$nonces = Rest_Endpoint::get_rest_nonces();
		$this->assertIsArray( $nonces );
		$this->assertCount( 2, $nonces );

		// Our user is not logged in, so it should only be one nonce.
		$this->assertNotEmpty( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ] );
		$this->assertEmpty( $nonces[ Rest_Endpoint::SECONDARY_NONCE_KEY ] );
		$this->assertEquals( 1, wp_verify_nonce( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], Rest_Endpoint::NONCE_ACTION ) );

		// Login a user, now should be two different nonces.
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$nonces = Rest_Endpoint::get_rest_nonces();
		$this->assertNotEmpty( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ] );
		$this->assertNotEmpty( $nonces[ Rest_Endpoint::SECONDARY_NONCE_KEY ] );
		$this->assertNotEquals( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], $nonces[ Rest_Endpoint::SECONDARY_NONCE_KEY ] );
		$valid = wp_verify_nonce( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], Rest_Endpoint::NONCE_ACTION )
		         || wp_verify_nonce( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], Rest_Endpoint::NONCE_ACTION );
		$this->assertTrue( $valid );
	}
}