<?php

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Events\Integrations\Plugins\Plugin_Integration;

/**
 * Class Provider
 *
 * @since   6.0.4
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Provider extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'wordpress-seo';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {
		return function_exists( 'YoastSEO' ) && $this->version_compare( '19.0' );
	}

	/**
	 * Checks the version of Yoast SEO against a given Version.
	 *
	 * @uses version_compare()
	 *
	 * @since 6.0.8
	 *
	 * @param string $version  The version of Yoast we are comparing the installed version to.
	 * @param string $operator The comparison operator. Defaults to >=
	 *
	 * @return bool
	 */
	public function version_compare( $version, $operator = '>=' ): bool {
		return defined( 'WPSEO_VERSION' )
			&& version_compare( WPSEO_VERSION, $version, $operator );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		add_filter( 'wpseo_schema_graph_pieces', [ $this, 'add_graph_pieces' ], 11, 2 );
		add_filter( 'tribe_events_add_canonical_tag', '__return_false' );

		if ( ! $this->version_compare( '19.2' ) ) {
			add_action( 'init', [ $this, 'remove_yoast_legacy_integration' ], 20 );
		}
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
}
