<?php
/**
 * REST API Notice test.
 */

declare( strict_types=1 );

namespace TEC\Tests\Events\Admin\Notice;

use Codeception\TestCase\WPTestCase;
use TEC\Events\Admin\Notice\Rest_Api;
use Tribe\Tests\Traits\With_Uopz;
use WP_Error;

/**
 * Class RESTAPITest
 *
 * @since TBD
 */
class RESTAPITest extends WPTestCase {

	use With_Uopz;

	/**
	 * Clean up after this test class finishes.
	 *
	 * @return void
	 */
	public function wpTearDownAfterClass() {
		remove_all_filters( 'tec_events_site_is_development_mode' );
	}

	/**
	 * Test scenarios that should report whether the API is blocked.
	 *
	 * @test
	 * @return void
	 */
	public function should_correctly_determine_if_the_api_is_blocked() {
		$request_return_success = function() {
			return [ 'response' => [ 'code' => 200 ] ];
		};

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

		// A timeout should not be blocking, unless we filter it to true.
		$this->set_fn_return( 'wp_safe_remote_get', $request_return_success, true );
		$this->assertFalse( $rest_api->is_rest_api_blocked( true ) );
		$this->unset_uopz_returns();

		$this->set_fn_return( 'wp_safe_remote_get', $request_return_timeout, true );
		$this->assertTrue( $rest_api->is_rest_api_blocked( true ) );
		$this->unset_uopz_returns();

		// An SSL error should be blocking, unless we are in development mode.
		$this->set_fn_return( 'wp_safe_remote_get', $request_return_ssl_error, true );
		$this->assertTrue( $rest_api->is_rest_api_blocked( true ) );
		add_filter( 'tec_events_site_is_development_mode', '__return_true' );
		$this->assertFalse( $rest_api->is_rest_api_blocked( true ) );
		remove_filter( 'tec_events_site_is_development_mode', '__return_true' );
		$this->unset_uopz_returns();
	}
}
