<?php

namespace TEC\Events\Integrations\Plugins\Colbri_Page_Builder;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Events\Integrations\Plugins\Plugin_Integration;

/**
 * Class Provider
 *
 * @since   TBD
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
		return class_exists( '\ColibriWP\PageBuilder\PageBuilder' );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		add_filter( 'tribe_events_should_enqueue_assets', '__return_false' );
	}
}
