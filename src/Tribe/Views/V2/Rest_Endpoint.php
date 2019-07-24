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
	 * Check if the REST endpoint is available.
	 *
	 * @since  TBD
	 *
	 * @return boolean If the REST API endpoint is available.
	 */
	public function is_available() {
		$is_available = tribe( 'tec.rest-v1.system' )->tec_rest_api_is_enabled();

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