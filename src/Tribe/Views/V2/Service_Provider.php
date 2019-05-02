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

		$this->bind_implementations();
		$this->add_filters();
		$this->add_actions();

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
	 * Binds all the implementations required for the Views v2 module to work.
	 *
	 * @since TBD
	 */
	protected function bind_implementations(){
		$this->container->singleton( Template_Bootstrap::class, Template_Bootstrap::class );
		$this->container->singleton( Template\Event::class, Template\Event::class );
		$this->container->singleton( Template\Page::class, Template\Page::class );
		$this->container->singleton( Kitchen_Sink::class, Kitchen_Sink::class );
	}

	/**
	 * Adds the actions required by each Views v2 component.
	 *
	 * @since TBD
	 */
	protected function add_actions() {
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		add_action( 'tribe_common_loaded', function () {
			$this->container->make( Template_Bootstrap::class )->disable_v1();
		}, 1 );
		add_action( 'loop_start', function ( $query ) {
			$this->container->make( Template\Page::class )->maybe_hijack_page_template( $query );
		}, PHP_INT_MAX );
		add_action( 'wp_head', function () {
			$this->container->make( Template\Page::class )->maybe_hijack_main_query();
		}, PHP_INT_MAX );
		add_action( 'tribe_events_pre_rewrite', function () {
			$this->container->make( Kitchen_Sink::class )->generate_rules();
		} );
	}

	/**
	 * Adds the filters required by each Views v2 component.
	 *
	 * @since TBD
	 */
	protected function add_filters() {
		// Let's make sure to suppress query filters from the main query.
		add_filter( 'tribe_suppress_query_filters', '__return_true' );
		add_filter( 'template_include', function ( $template ) {
			return $this->container->make( Template_Bootstrap::class )
			                       ->filter_template_include( $template );
		}, 50 );
	}
}