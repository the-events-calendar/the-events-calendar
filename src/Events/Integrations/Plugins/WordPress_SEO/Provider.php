<?php
/**
 * The Events Calendar Yoast SEO Integration Provider.
 *
 * @package The Events Calendar
 * @since 6.0.4
 */

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Common\Integrations\Traits\Plugin_Integration;
use TEC\Common\Asset;
use Tribe__Events__Main as TEC;

/**
 * Class Provider
 *
 * @since 6.0.4
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Provider extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * Get the slug for this integration.
	 *
	 * @since 6.0.4
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return 'wordpress-seo';
	}

	/**
	 * Load conditionals for this integration.
	 *
	 * @since 6.0.4
	 *
	 * @return bool
	 */
	public function load_conditionals(): bool {
		return function_exists( 'YoastSEO' ) && $this->version_compare( '19.0' );
	}

	/**
	 * Checks the version of Yoast SEO against a given Version.
	 *
	 * @since 6.0.8
	 *
	 * @uses version_compare()
	 *
	 * @param string $version  The version of Yoast we are comparing the installed version to.
	 * @param string $operator The comparison operator. Defaults to >=.
	 *
	 * @return bool
	 */
	public function version_compare( $version, $operator = '>=' ): bool {
		return defined( 'WPSEO_VERSION' )
			&& version_compare( WPSEO_VERSION, $version, $operator );
	}

	/**
	 * Load the integration.
	 *
	 * @since 6.0.4
	 *
	 * @return void
	 */
	protected function load(): void {
		add_filter( 'wpseo_schema_graph_pieces', [ $this, 'add_graph_pieces' ], 11, 2 );
		add_filter( 'tribe_events_add_canonical_tag', '__return_false' );

		if ( ! $this->version_compare( '19.2' ) ) {
			add_action( 'init', [ $this, 'remove_yoast_legacy_integration' ], 20 );
		}

		$this->register_custom_variables();
		$this->register_assets();
		$this->register_events_pagination();
	}

	/**
	 * Register custom variables for Yoast SEO.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	private function register_custom_variables() {
		$events_variables = new Events_Variables();
		$events_variables->register();
	}

	/**
	 * Register assets for Yoast SEO integration.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	private function register_assets() {
		Asset::add(
			'tec-yoast-events-replacevars',
			'js/yoastseo-events-replacevars.js'
		)
			->add_to_group_path( TEC::class . '-packages' )
			->add_to_group( 'tec-yoast' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'should_enqueue_yoast_assets' ] )
			->set_dependencies( 'jquery' )
			->in_footer()
			->register();
	}

	/**
	 * Check if Yoast SEO assets should be enqueued.
	 *
	 * @since 6.14.0
	 *
	 * @return bool
	 */
	public function should_enqueue_yoast_assets() {
		if ( ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || 'tribe_events' !== $screen->post_type ) {
			return false;
		}

		return $this->should_load();
	}

	/**
	 * Prevent the old Yoast plugin integration with TEC from loading.
	 *
	 * @since 6.0.4
	 *
	 * @return void
	 */
	public function remove_yoast_legacy_integration(): void {
		$tec_integration = YoastSEO()->classes->get( 'Yoast\\WP\\SEO\\Integrations\\Third_Party\\The_Events_Calendar' );
		if ( ! empty( $tec_integration ) ) {
			remove_filter( 'wpseo_schema_graph_pieces', [ $tec_integration, 'add_graph_pieces' ], 11 );
		}
	}

	/**
	 * Adds the events graph pieces to the schema collector.
	 *
	 * @since 6.0.4
	 *
	 * @param array  $pieces  The current graph pieces.
	 * @param string $context The current context.
	 *
	 * @return array Extended graph pieces.
	 */
	public function add_graph_pieces( $pieces, $context ) {
		if ( is_admin() ) {
			return $pieces;
		}

		if ( \WPSEO_Options::get( 'opengraph' ) !== true ) {
			return $pieces;
		}

		$pieces[] = new Events_Schema( $context );
		return $pieces;
	}

	/**
	 * Register the pagination disabler for TEC views.
	 *
	 * @since 6.15.9
	 *
	 * @return void
	 */
	private function register_events_pagination(): void {
		$this->container->bind( Events_Pagination::class, Events_Pagination::class );
		
		$events_pagination = tribe( Events_Pagination::class );
		$events_pagination->register();
	}
}
