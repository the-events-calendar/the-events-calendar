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
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	const NAME_SPACE = 'tribe/views/v2';

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		add_filter( 'template_include', [ $this, 'filter_template_include' ], 50 );
		// Let's make sure to suppress query filters from the main query.
		add_filter( 'tribe_suppress_query_filters', '__return_true' );

		View::set_container( $this->container );
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
	 * Filters the `template_include` filter to return the Views router template if required..
	 *
	 * @since TBD
	 *
	 * @param string $template The template located by WordPress.
	 *
	 * @return string The Views router file if required or the input template.
	 */
	public function filter_template_include( $template ) {
		global $wp_the_query;

		if ( [ TEC::POSTTYPE ] !== (array) $wp_the_query->get( 'post_type' ) ) {
			return $template;
		}

		$index = ( new Index() )->get_template_file();

		return $index ? $index : $template;
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
	protected function is_enabled() {
		if ( defined( 'TRIBE_EVENTS_V2_VIEWS' ) ) {
			return (bool) TRIBE_EVENTS_V2_VIEWS;
		}

		$env_var = getenv( 'TRIBE_EVENTS_V2_VIEWS' );
		if ( false !== $env_var ) {
			return (bool) $env_var;
		}

		return (bool) tribe_get_option( View::$option_enabled, false );
	}
}