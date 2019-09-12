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
	 * @since  4.9.7
	 *
	 * @var  string
	 */
	const ROOT_NAMESPACE = 'tribe/views/v2';

	/**
	 * AJAX action for the fallback when REST is inactive.
	 *
	 * @since  4.9.7
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

		return get_rest_url( null, static::ROOT_NAMESPACE . '/html' );
	}

	/**
	 * Get the arguments used to setup the HTML route for Views V2 in the REST API.
	 *
	 * @link  https://developer.wordpress.org/rest-api/requests/
	 *
	 * @since  4.9.7
	 *
	 * @return array $arguments Request arguments following the WP_REST API Standards [ name => options, ... ]
	 */
	public function get_request_arguments() {
		$arguments = [
			'url' => [
				'required'          => true,
				'validate_callback' => static function ( $url ) {
					return is_string( $url );
				},
				'sanitize_callback' => static function ( $url ) {
					return filter_var( $url, FILTER_SANITIZE_URL );
				},
			],
			'view' => [
				'required'          => false,
				'validate_callback' => static function ( $view ) {
					return is_string( $view );
				},
				'sanitize_callback' => static function ( $view ) {
					return filter_var( $view, FILTER_SANITIZE_STRING );
				},
			],
			'_wpnonce' => [
				'required'          => false,
				'validate_callback' => static function ( $nonce ) {
					return is_string( $nonce );
				},
				'sanitize_callback' => static function ( $nonce ) {
					return filter_var( $nonce, FILTER_SANITIZE_STRING );
				},
			],
			'view_data' => [
				'required'          => false,
				'validate_callback' => static function ( $view_data ) {
					return is_array( $view_data );
				},
				'sanitize_callback' => static function ( $view_data ) {
					return is_array( $view_data ) ? $view_data : [];
				},
			],
		];

		// Arguments specific to AJAX requests
		if ( ! $this->is_available() ) {
			$arguments['action'] = [
				'required'          => false,
				'validate_callback' => static function ( $action ) {
					return is_string( $action );
				},
				'sanitize_callback' => static function ( $action ) {
					return filter_var( $action, FILTER_SANITIZE_STRING );
				},
			];
		}

		/**
		 * Filter the arguments for the HTML REST API request.
		 * It follows the WP_REST API standards.
		 *
		 * @link  https://developer.wordpress.org/rest-api/requests/
		 *
		 * @since  4.9.7
		 *
		 * @param array $arguments Request arguments following the WP_REST API Standards [ name => options, ... ]
		 */
		return apply_filters( 'tribe_events_views_v2_request_arguments', $arguments );
	}

	/**
	 * Register the endpoint if available.
	 *
	 * @since  4.9.7
	 *
	 * @return boolean If we registered the endpoint.
	 */
	public function register() {
		if ( ! $this->is_available() ) {
			return false;
		}

		return register_rest_route( static::ROOT_NAMESPACE, '/html', [
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
			'args' => $this->get_request_arguments(),
		] );
	}

	/**
	 * When REST is not available add ajax fallback into the correct action.
	 *
	 * @since  4.9.7
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
	 * Get the mocked rest request used for the AJAX fallback used to make sure users without
	 * the REST API still have the Views V2 working.
	 *
	 * @since  4.9.7
	 *
	 * @param  array $params Associative array with the params that will be used on this mocked request
	 *
	 * @return WP_REST_Request
	 */
	public function get_mocked_rest_request( array $params ) {
		$request = new Request( 'GET', static::ROOT_NAMESPACE . '/html' );
		$arguments = $this->get_request_arguments();

		foreach ( $params as $key => $value ) {
			// Quick way to prevent un-wanted params.
			if ( ! isset( $arguments[ $key ] ) ) {
				continue;
			}

			$request->set_param( $key, $value );
		}

		$has_valid_params = $request->has_valid_params();
		if ( ! $has_valid_params || is_wp_error( $has_valid_params ) ) {
			return $has_valid_params;
		}

		$sanitize_params = $request->sanitize_params();
		if ( ! $sanitize_params || is_wp_error( $sanitize_params ) ) {
			return $sanitize_params;
		}

		return $request;
	}

	/**
	 * AJAX fallback for when REST endpoint is disabled. We try to mock a WP_REST_Request
	 * and use the same method behind the scenes to make sure we have consistency.
	 *
	 * @since  4.9.7
	 *
	 * @return void
	 */
	public function handle_ajax_request() {
		$request = $this->get_mocked_rest_request( $_GET );
		if ( is_wp_error( $request ) ) {
			/**
			 * @todo  Once we have a error handling on the new view we need to throw it here.
			 */
			return wp_send_json_error( $request );
		}

		View::make_for_rest( $request )->send_html();
	}

	/**
	 * Check if the REST endpoint is available.
	 *
	 * @since  4.9.7
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
		 * @since  4.9.7
		 *
		 * @param boolean $is_available If the REST API endpoint is available.
		 */
		$is_available = apply_filters( 'tribe_events_views_v2_rest_endpoint_available', $is_available );

		return $is_available;
	}
}