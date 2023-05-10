<?php

namespace TEC\Events\Integrations\Plugins\Colbri_Page_Builder;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Events\Integrations\Plugins\Plugin_Integration;

/**
 * Class Provider
 *
 * @since   6.0.13
 *
 * @package TEC\Events\Integrations\Plugins\Colbri_Page_Builder
 */
class Provider extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'colibri-page-builder';
	}

	/**
	 * @inheritDoc
	 *
	 * @return bool Whether or not integrations should load.
	 */
	public function load_conditionals(): bool {
		return class_exists( '\ColibriWP\PageBuilder\PageBuilder', false );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		// Don't load the `tribe-common-gutenberg-vendor` script in the WP customizer.
		add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
			if (
				'tribe-common-gutenberg-vendor' === $handle
				&& is_customize_preview()
			) {
				return;
			}

			return $tag;
		}, 999, 3 );
	}
}
