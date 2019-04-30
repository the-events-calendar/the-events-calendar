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

		if ( ! self::is_enabled() ) {
			return;
		}

		$this->container->singleton( 'tribe.events.views.v2.template-bootstrap', Template_Bootstrap::class, [ 'hook' ] );

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
		tribe( 'tribe.events.views.v2.template-bootstrap' );
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

	/**
	 * Checks whether v2 of the Views is enabled or not.
	 *
	 * In order the function will check the `TRIBE_EVENTS_V2_VIEWS` constant,
	 * the `TRIBE_EVENTS_V2_VIEWS` environment variable and, finally, the `static::$option_enabled` option.
	 *
	 * @since TBD
	 *
	 * @return bool Whether v2 of the Views are enabled or not.
	 */
	public static function is_enabled() {
		if ( defined( 'TRIBE_EVENTS_V2_VIEWS' ) ) {
			return (bool) TRIBE_EVENTS_V2_VIEWS;
		}

		$env_var = getenv( 'TRIBE_EVENTS_V2_VIEWS' );
		if ( false !== $env_var ) {
			return (bool) $env_var;
		}

		$enabled = (bool) tribe_get_option( View::$option_enabled, false );

		/**
		 * Allows filtering of the Events Views V2 provider, doing so will render
		 * the methods and classes no longer load-able so keep that in mind.
		 *
		 * @since  TBD
		 *
		 * @param boolean $enabled Determining if V2 Views is enabled\
		 */
		return apply_filters( 'tribe_events_views_v2_is_enabled', $enabled );
	}
}