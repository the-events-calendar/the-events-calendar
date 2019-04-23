<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Service_Provider
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	const NAME_SPACE = 'tribe/views/v2';

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		$enabled = (bool) tribe_get_option( View::OPTION_ENABLED, true );

		if ( ! $enabled ) {
			return;
		}

		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		View::set_container( $this->container );
	}

	public function register_rest_endpoints() {
		register_rest_route( static::NAME_SPACE, '/html', [
			'methods'             => \WP_REST_Server::READABLE,
			'permission_callback' => function ( \WP_REST_Request $request ) {
				return wp_verify_nonce( $request['nonce'], 'wp_rest' );
			},
			'callback' => function ( \WP_REST_Request $request ) {
				View::make_for_rest( $request )->send_html();
			},
		] );
	}
}