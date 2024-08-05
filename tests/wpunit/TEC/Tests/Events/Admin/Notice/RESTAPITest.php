<?php
/**
 * REST API Notice test.
 */

declare( strict_types=1 );

namespace TEC\Tests\Events\Admin\Notice;

use Closure;
use Codeception\TestCase\WPTestCase;
use TEC\Events\Admin\Notice\Rest_Api;
use WP_Error;

/**
 * Class RESTAPITest
 *
 * @since TBD
 */
class RESTAPITest extends WPTestCase {

	function test_response_blocked_from_wp_error() {
		$request_return_timeout = function() {
			return new WP_Error(
				'http_request_failed',
				'cURL error 28: Operation timed out after 10000 milliseconds with 0 bytes received'
			);
		};

		$request_return_ssl_error = function() {
			return new WP_Error(
				'http_request_failed',
				'cURL error 60: SSL certificate problem: unable to get local issuer certificate'
			);
		};

		$rest_api = new Rest_Api();

		// Set up a wrapper to test the private method.
		$is_response_blocking = Closure::bind(
			function( $response ) {
				return $this->is_wp_error_response_blocking( $response );
			},
			$rest_api,
			$rest_api
		);

		// A timeout should not be blocking, unless we filter it to true.
		$this->assertFalse( $is_response_blocking( $request_return_timeout() ) );
		add_filter( 'tec_events_rest_api_response_blocked_due_to_timeout', '__return_true' );
		$this->assertTrue( $is_response_blocking( $request_return_timeout() ) );
		remove_filter( 'tec_events_rest_api_response_blocked_due_to_timeout', '__return_true' );

		// An SSL error should be blocking, unless we are in development mode.
		$this->assertTrue( $is_response_blocking( $request_return_ssl_error() ) );
		add_filter( 'tec_events_site_is_development_mode', '__return_true' );
		$this->assertFalse( $is_response_blocking( $request_return_ssl_error() ) );
		remove_filter( 'tec_events_site_is_development_mode', '__return_true' );
	}
}
