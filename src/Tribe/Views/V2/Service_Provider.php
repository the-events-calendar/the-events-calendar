<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as TEC;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	const NAME_SPACE = 'tribe/views/v2';

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		require_once tribe( 'tec.main' )->plugin_path . 'src/functions/views/provider.php';

		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		$this->container->singleton( 'events.views.v2.template-bootstrap', Template_Bootstrap::class, [ 'hook' ] );
		$this->container->singleton( 'events.views.v2.kitchen-sink', Kitchen_Sink::class, [ 'hook' ] );

		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );

		// Let's make sure to suppress query filters from the main query.
		add_filter( 'tribe_suppress_query_filters', '__return_true' );

		View::set_container( $this->container );

		// Initialize Views Classes and Singletons
		$this->init();
	}

	/**
	 * Initialize the classes for this Service Provider
	 *
	 * @since  TBD
	 *
	 * @return void
	 */
	protected function init() {
		tribe( 'events.views.v2.template-bootstrap' );
		tribe( 'events.views.v2.kitchen-sink' );
	}

	/**
	 * Registers the REST endpoints that will be used to return the Views HTML.
	 *
	 * @since TBD
	 */
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