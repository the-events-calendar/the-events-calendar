<?php

namespace TEC\Events\Integrations\Plugins\Elementor;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Events\Integrations\Plugins\Plugin_Integration;
use Tribe__Events__Main;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor
 */
class Provider extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'elementor';
	}

	/**
	 * @inheritDoc
	 *
	 * @return bool Whether or not integrations should load.
	 */
	public function load_conditionals(): bool {
		return defined( 'ELEMENTOR_PATH' ) && ! empty( ELEMENTOR_PATH );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		add_filter( 'elementor/query/query_args', [ $this, 'tec_suppress_query_filters' ], 10, 1 );
	}

	/**
	 * Modifies the Elementor posts widget query arguments to set 'tribe_suppress_query_filters' to true for the Event post type.
	 *
	 * @param array $query_args The Elementor posts widget query arguments.
	 *
	 * @return array The modified Elementor posts widget query arguments.
	 */
	public function tec_suppress_query_filters( $query_args ): array {
		// Check if the query is for the events post type.
		if ( (array) $query_args['post_type'] !== [ Tribe__Events__Main::POSTTYPE ] ) {
			return $query_args;
		}

		// Set the 'tribe_suppress_query_filters' to true.
		$query_args['tribe_suppress_query_filters'] = true;

		return $query_args;
	}
}
