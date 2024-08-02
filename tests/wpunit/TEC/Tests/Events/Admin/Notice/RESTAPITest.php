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
		$wrapper = Closure::bind(
			function( $response ) {
				return $this->is_wp_error_response_blocking( $response );
			},
			$rest_api,
			$rest_api
		);

		$this->assertFalse( $wrapper( $request_return_timeout() ) );
		$this->assertFalse( $wrapper( $request_return_ssl_error() ) );

		// Test that the filters can change the results.
		add_filter( 'tec_events_rest_api_response_blocked_due_to_timeout', '__return_true' );
		$this->assertTrue( $wrapper( $request_return_timeout() ) );
		remove_filter( 'tec_events_rest_api_response_blocked_due_to_timeout', '__return_true' );
	}
}
