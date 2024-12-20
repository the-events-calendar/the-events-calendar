<?php

namespace Tribe\Events\Views\V2;

use WP_REST_Request;
use WP_Screen;
use WP_User;

class Rest_EndpointTest extends \Codeception\TestCase\WPTestCase {
	protected $old_screen;
	protected $old_user;

	public function _tearDown() {
		$this->reset_screen();
		$this->reset_user();
	}

	public function given_as_an_admin_screen() {
		global $current_screen;
		if ( ! $this->old_screen && $current_screen ) {
			$this->old_screen = $current_screen;
		}

		// Create a dummy screen object that mimics an admin screen
		$current_screen              = clone WP_Screen::get( 'nav-menus' );
		$current_screen->id          = 'admin_dummy_screen';
		$current_screen->base        = 'admin';
		$current_screen->parent_base = '';
		$current_screen->action      = 'edit';
		$current_screen->post_type   = '';
		$current_screen->taxonomy    = '';
		$current_screen->is_admin    = true;

		return $current_screen;
	}

	public function reset_screen() {
		global $current_screen;
		if ( $this->old_screen && $current_screen ) {
			$current_screen   = $this->old_screen;
			$this->old_screen = null;
		}
	}

	public function reset_user() {
		if ( $this->old_user ) {
			wp_set_current_user( $this->old_user );
			$this->old_user = null;
		}
	}

	public function given_as_an_anonymous_user() {
		wp_set_current_user( 0 );
	}

	public function given_as_an_admin_user() {
		$user = wp_get_current_user();
		if ( ! $this->old_user && $user ) {
			$this->old_user = $user->ID;
		}
		$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		return $user_id;
	}

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
					'url'         => home_url(),
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
		$rest    = $this->make_instance();
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
		$this->given_as_an_admin_user();
		$nonces = Rest_Endpoint::get_rest_nonces();
		$this->assertNotEmpty( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ] );
		$this->assertNotEmpty( $nonces[ Rest_Endpoint::SECONDARY_NONCE_KEY ] );
		$this->assertNotEquals( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], $nonces[ Rest_Endpoint::SECONDARY_NONCE_KEY ] );
		$valid = wp_verify_nonce( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], Rest_Endpoint::NONCE_ACTION )
		         || wp_verify_nonce( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], Rest_Endpoint::NONCE_ACTION );
		$this->assertTrue( $valid );
	}

	/**
	 * Validates we retain user when using our custom nonces.
	 *
	 * @see \Tribe\Events\Views\V2\Rest_Endpoint::preserve_user_for_custom_nonces() for more context.
	 * @test
	 */
	public function it_should_authenticate_with_nonces() {
		$user_id = $this->given_as_an_admin_user();

		// Sanity check.
		$this->assertEmpty( Rest_Endpoint::get_stored_user_id() );

		// Triggers storing our auth'd user.
		apply_filters( 'rest_allowed_cors_headers', null, null );

		// Get our nonces
		$nonces = Rest_Endpoint::get_rest_nonces();

		// Now clear user to mimic a valid request.
		wp_set_current_user( 0 );

		$rest_endpoint = new Rest_Endpoint();
		$request       = new WP_REST_Request( 'POST' );
		$request->set_body_params( $nonces );
		$this->assertTrue( $rest_endpoint->is_valid_request( $request ) );

		// Validate our user ID was stored.
		$this->assertEquals( $user_id, Rest_Endpoint::get_stored_user_id() );

		// These should still be valid.
		$valid = wp_verify_nonce( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], Rest_Endpoint::NONCE_ACTION )
		         || wp_verify_nonce( $nonces[ Rest_Endpoint::PRIMARY_NONCE_KEY ], Rest_Endpoint::NONCE_ACTION );

		$this->assertTrue( $valid );
	}

	/**
	 * Validates we exclude other filters when validating nonces.
	 *
	 * @test
	 */
	public function it_can_validate_generated_nonces() {
		add_filter( 'nonce_user_logged_out', function () {
			return '127.0.0.1';
		} );

		// Get our nonces
		$nonces = Rest_Endpoint::get_rest_nonces();

		$rest_endpoint = new Rest_Endpoint();
		$request       = new WP_REST_Request( 'POST' );
		$request->set_body_params( $nonces );
		$this->assertTrue( $rest_endpoint->is_valid_request( $request ) );
	}

	/**
	 * We should still auth the request with the wp rest server.
	 *
	 * @test
	 */
	public function it_should_serve_authenticated_request() {
		global $wp_rest_auth_cookie;
		$wp_rest_auth_cookie = true;
		$this->given_as_an_admin_user();

		// Check if this flags user auth after request.
		$is_logged_in = true;
		add_filter( 'rest_authentication_errors', function ( $error ) use ( &$is_logged_in ) {
			$user = wp_get_current_user();
			if ( ! $user ) {
				$is_logged_in = false;
			}
			if ( $user instanceof WP_User ) {
				$is_logged_in = ! ! $user->ID;
			}

			return $error;
		}, 99999 );


		// Faux request. User was authed above, so this won't matter.
		$_POST  = [
			'prev_url'                       => "http://localhost/events/month/2023-10/?shortcode=admin-manager",
			'url'                            => "http://localhost/events/month/2023-10/?shortcode=admin-manager",
			'should_manage_url'              => "false",
			'shortcode'                      => "admin-manager",
			'_tec_view_rest_nonce_primary'   => "79f305ca08",
			'_tec_view_rest_nonce_secondary' => "77925c8a1f"
		];
		$server = rest_get_server();
		$server->serve_request( '/tribe/views/v2/html' );

		$this->assertTrue( $is_logged_in );
	}
}
