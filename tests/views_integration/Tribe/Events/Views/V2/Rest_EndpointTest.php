<?php

namespace Tribe\Events\Views\V2;

use Generator;
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
		Rest_Endpoint::clear_stored_user_id();
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
					'u'         => home_url(),
				],
				[
					'u' => home_url(),
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
	 * Validates wo/ user we exclude other filters when validating nonces.
	 *
	 * @test
	 */
	public function it_can_validate_no_user_generated_nonces() {
		$user = wp_get_current_user();
		wp_set_current_user( 0 );
		add_filter( 'nonce_user_logged_out', function () {
			return '127.0.0.1';
		} );

		// Get our nonces
		$nonces = Rest_Endpoint::get_rest_nonces();

		$rest_endpoint = new Rest_Endpoint();
		$request       = new WP_REST_Request( 'POST' );
		$request->set_body_params( $nonces );
		$this->assertTrue( $rest_endpoint->is_valid_request( $request ) );
		wp_set_current_user( $user->ID );
	}

	/**
	 * Validates w/ user we exclude other filters when validating nonces.
	 *
	 * @test
	 */
	public function it_can_validate_with_user_generated_nonces() {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
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

	/**
	 * Provides multiple `u` param scenarios to test merging into the context.
	 *
	 * @return Generator
	 */
	public function u_param_url_provider(): \Generator {
		// 1. Basic single param.
		yield 'Single custom field' => [
			'events/list/?tribe__ecp_custom_3[0]=Yes',
			[ 'tribe__ecp_custom_3' => [ 'Yes' ] ],
			[],
		];

		// 2. Multiple params with arrays.
		yield 'Multiple custom fields' => [
			'events/list/?tribe__ecp_custom_3[0]=Yes&tribe__ecp_custom_2[0]=no&tribe_organizers[0]=53419',
			[
				'tribe__ecp_custom_3' => [ 'Yes' ],
				'tribe__ecp_custom_2' => [ 'no' ],
				'tribe_organizers'    => [ '53419' ],
			],
			[],
		];

		// 3. Random characters allowed in values.
		yield 'Custom field with special characters' => [
			'events/list/?tribe__ecp_custom_4[0]=' . rawurlencode( '!@#$%^&*(()_+' ),
			[ 'tribe__ecp_custom_4' => [ '!@#$%^&*(()_+' ] ],
			[],
		];

		// 4. Conflicting keys → existing context should win.
		yield 'Conflicting keys should not overwrite existing ones' => [
			'events/list/?eventDisplay=month&tribe__ecp_custom_5[0]=edge',
			[ 'tribe__ecp_custom_5' => [ 'edge' ] ],
			['eventDisplay' => 'month' ],
		];

		// 5. Empty `u` param.
		yield 'Empty u param should do nothing' => [
			'',
			[],
			[],
		];

		// 6. Malformed query param.
		yield 'Malformed query string' => [
			'events/list/?&&&badparam',
			[],
			[],
		];

		// 7. Large payload (truncates after 50 params).
		yield 'Large payload trimmed after 50 params' => [
			'events/list/?' . implode(
				'&',
				array_map(
					fn( $i ) => "param{$i}={$i}",
					range( 1, 60 )
				)
			),
			array_combine(
				array_map( fn( $i ) => "param{$i}", range( 1, 50 ) ),
				array_map( fn( $i ) => (string) $i, range( 1, 50 ) )
			),
			[],
		];

		// 8. Malicious key injection (PHP code attempt).
		yield 'Malicious key injection' => [
			'events/list/?<?php=bad&safe_key=value',
			[ 'safe_key' => 'value' ],
			[],
		];

		// 9. Script tag injection (encoded XSS attempt).
		yield 'Script tag injection' => [
			'events/list/?<script>alert(1)</script>=evil&normal=value',
			[ 'normal' => 'value' ],
			[],
		];

		// 10. Encoded script injection.
		yield 'Encoded script injection' => [
			'events/list/?' . rawurlencode('<script>alert(1)</script>') . '=x&safe=yes',
			[ 'safe' => 'yes' ],
			[],
		];

		// 11. Nested bracket keys allowed.
		yield 'Nested brackets in keys' => [
			'events/list/?allowed[0][nested]=ok&safe=yes',
			[ 'safe' => 'yes' ],
			[],
		];

		// 12. Keys that look like WP internals should be stripped.
		yield 'WP internal keys stripped' => [
			'events/list/?_wpnonce=bad&action=delete&safe_key=value',
			[ 'safe_key' => 'value' ],
			[],
		];

		// 13. Duplicate keys should merge values.
		yield 'Duplicate keys merge values' => [
			'events/list/?dup[]=one&dup[]=two',
			[ 'dup' => [ 'one', 'two' ] ],
			[],
		];

		// 14. Nested arrays with multiple layers.
		yield 'Deeply nested arrays' => [
			'events/list/?field[0][subkey][inner]=deep&safe=value',
			[ 'safe' => 'value' ], // Nested keys should be stripped, only `safe` survives.
			[],
		];

		// 15. Numeric-only keys.
		yield 'Numeric keys only' => [
			'events/list/?123=value&safe=keep',
			[ 'safe' => 'keep' ], // Numeric-only keys should be dropped, `safe` remains.
			[],
		];

		// 16. Encoded malicious values.
		yield 'Encoded malicious values' => [
			'events/list/?safe=' . rawurlencode( '<script>alert("bad")</script>' ),
			[ 'safe' => ''  ], // Values removed.
			[],
		];

		// 17. Keys with invalid characters.
		yield 'Keys with invalid characters stripped' => [
			'events/list/?bad key=value&ok=value2',
			[ 'ok' => 'value2' ], // `bad key` invalid due to space, `ok` stays.
			[],
		];

		// 18. Duplicate keys preserve order.
		yield 'Duplicate keys preserve order' => [
			'events/list/?dup[]=first&dup[]=second&dup[]=third',
			[ 'dup' => [ 'first', 'second', 'third' ] ], // Ensure order preserved for duplicate values.
			[],
		];

		// 19. Mixed good and bad keys in same URL.
		yield 'Mixed good and bad keys' => [
			'events/list/?<bad>=1&good=2&another<bad>=3&safe=yes',
			[
				'good' => '2',
				'safe' => 'yes',
			], // Only valid keys survive.
			[],
		];

		// 20. Encoded brackets in keys.
		yield 'Encoded brackets in keys' => [
			'events/list/?' . rawurlencode( 'bracket[key]' ) . '=val&normal=ok',
			[ 'normal' => 'ok' ], // Encoded bracket keys dropped.
			[],
		];

	}

	/**
	 * @before
	 */
	public function reset_context_between_sets(): void {
		// Reset the Context:
		tribe_context()->refresh();
		/* Repopulate from the canonical locations file (this drops any dynamic keys
		that were merged into static::$locations in a previous dataset) */
		tribe_context()->dangerously_repopulate_locations();
	}

	/**
	 * @test
	 * @dataProvider u_param_url_provider
	 */
	public function it_should_merge_u_param_into_context( string $relative_url, array $expected_values, array $existing_context ) {
		$rest    = $this->make_instance();
		$request = new WP_REST_Request( 'GET', '/tribe/views/v2/html' );

		// Build full URL dynamically using home_url().
		$url = $relative_url ? home_url( $relative_url ) : '';

		// Snapshot original context values before merging.
		$original_context = tribe_context( [], true )->to_array();

		// Add the `u` param to the request.
		$request->set_param( 'u', $url );

		// Merge URL params into the context via public method.
		$rest->unshrink_url_components( $request );

		// Get updated context.
		$context = tribe_context( [], true );

		// Original values should remain untouched for conflicting keys.
		foreach ( $existing_context as $key => $value ) {
			$this->assertEquals(
				$value,
				$context->get( $key ),
				"Context value for {$key} should remain unchanged when conflicts exist."
			);
		}

		// Expected new values should be available in context.
		foreach ( $expected_values as $key => $expected_value ) {
			$this->assertEquals(
				$expected_value,
				$context->get( $key ),
				"Expected context key {$key} to have value " . print_r( $expected_value, true )
			);
		}

		// If no expected values, assert nothing unexpected got added.
		if ( empty( $expected_values ) ) {
			foreach ( $context->to_array() as $key => $value ) {
				$this->assertArrayNotHasKey(
					$key,
					$expected_values,
					"Unexpected key {$key} found in context."
				);
			}
		}

		// Confirm all original keys remain unchanged unless explicitly modified.
		foreach ( $original_context as $key => $original_value ) {
			// If this key was not in expected_values or existing_context, it should stay the same.
			if ( ! array_key_exists( $key, $expected_values ) && ! array_key_exists( $key, $existing_context ) ) {
				$this->assertSame(
					$original_value,
					$context->get( $key ),
					"Original context value for {$key} was unexpectedly modified."
				);
			}
		}
	}
}
