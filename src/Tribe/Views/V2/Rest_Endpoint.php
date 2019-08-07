<?php
/**
 *
 *
 * @package Tribe\Events\Views\V2
 * @since 4.9.2
 */
namespace Tribe\Events\Views\V2;

use WP_REST_Request as Request;
use WP_REST_Server as Server;

class Rest_Endpoint {

	/**
	 * Rest Endpoint namespace
	 *
	 * @since  TBD
	 *
	 * @var  string
	 */
	const NAMESPACE = 'tribe/views/v2';

	/**
	 * AJAX action for the fallback when REST is inactive.
	 *
	 * @since  TBD
	 *
	 * @var  string
	 */
	public static $ajax_action = 'tribe_events_views_v2_fallback';

	/**
	 * Returns the final REST URL for the HTML
	 *
	 * @since   4.9.2
	 *
	 * @return  string
	 */
	public function get_url() {
		if ( ! $this->is_available() ) {
			$url = admin_url( 'admin-ajax.php' );
			return add_query_arg( [ 'action' => static::$ajax_action ], $url );
		}

		return get_rest_url( null, static::NAMESPACE . '/html' );
	}

	/**
	 * Register the endpoint if available.
	 *
	 * @since  TBD
	 *
	 * @return boolean If we registered the endpoint.
	 */
	public function register() {
		if ( ! $this->is_available() ) {
			return false;
		}

		return register_rest_route( static::NAMESPACE, '/html', [
			'methods'             => Server::READABLE,
			/**
			 * @todo  Make sure we do proper handling of cache longer then 12h.
			 */
			'permission_callback' => static function ( Request $request ) {
				return wp_verify_nonce( $request->get_param( '_wpnonce' ), 'wp_rest' );
			},
			'callback' => static function ( Request $request ) {
				View::make_for_rest( $request )->send_html();
			},
			'args' => [
				'url' => [
					'required'          => true,
					'validate_callback' => static function ( $url ) {
						return is_string( $url );
					},
					'sanitize_callback' => static function ( $url ) {
						return filter_var( $url, FILTER_SANITIZE_URL );
					}
				],
			],
		] );
	}

	/**
	 * When REST is not available add ajax fallback into the correct action.
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function maybe_enable_ajax_fallback() {
		if ( $this->is_available() ) {
			return;
		}

		$action = static::$ajax_action;
		add_action( "wp_ajax_{$action}", [ $this, 'handle_ajax_request' ] );
		add_action( "wp_ajax_nopriv_{$action}", [ $this, 'handle_ajax_request' ] );
	}

	/**
	 * AJAX fallback for when REST endpoint is disabled. We try to mock a WP_REST_Request
	 * and use the same method behind the scenes to make sure we have consistency.
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	public function handle_ajax_request() {
		$request = new Request( 'GET', static::NAMESPACE . '/html' );

		$body_params = (array) $_GET;
		foreach ( $body_params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		View::make_for_rest( $request )->send_html();
	}

	/**
	 * Check if the REST endpoint is available.
	 *
	 * @since  TBD
	 *
	 * @return boolean If the REST API endpoint is available.
	 */
	public function is_available() {
		$is_available = tribe( 'tec.rest-v1.system' )->tec_rest_api_is_enabled();

		/**
		 * There is no good way to check if rest API is really disabled since `rest_enabled` is deprecated since 4.7
		 *
		 * @link https://core.trac.wordpress.org/browser/trunk/src/wp-includes/rest-api/class-wp-rest-server.php#L262
		 */
		global $wp_rest_server;
		if (
			! empty( $wp_rest_server )
			&& $wp_rest_server instanceof Server
			&& ! $wp_rest_server->check_authentication()
		) {
			$is_available = false;
		}

		/**
		 * Allows third-party deactivation of the REST Endpoint for just the view V2.
		 *
		 * @since  TBD
		 *
		 * @param boolean $is_available If the REST API endpoint is available.
		 */
		$is_available = apply_filters( 'tribe_events_views_v2_rest_endpoint_available', $is_available );

		return $is_available;
	}
}