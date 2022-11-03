<?php

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Events\Integrations\Plugins\Plugin_Integration;

/**
 * Class Provider
 *
 * @since   TBD
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
		return
			function_exists( 'YoastSEO' )
			&& defined( 'WPSEO_VERSION' )
			&& version_compare( WPSEO_VERSION, '19.0', '>=' );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		add_filter( 'wpseo_schema_graph_pieces', [ $this, 'add_graph_pieces' ], 11, 2 );
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