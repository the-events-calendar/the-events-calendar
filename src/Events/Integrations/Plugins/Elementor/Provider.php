<?php

namespace TEC\Events\Integrations\Plugins\Elementor;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Common\Integrations\Traits\Plugin_Integration;
use Tribe__Events__Main;

/**
 * Class Provider
 *
 * @since   6.0.13
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
		add_filter( 'elementor/query/query_args', [ $this, 'suppress_query_filters' ], 10, 1 );
	}

	/**
	 * Modifies the Elementor posts widget query arguments to set 'tribe_suppress_query_filters' to true for the Event post type.
	 *
	 * @param array $query_args The Elementor posts widget query arguments.
	 *
	 * @return array The modified Elementor posts widget query arguments.
	 */
	public function suppress_query_filters( $query_args ): array {
		/**
		 * Checks if the 'tribe_events' post type is present in the query arguments.
		 * If not, it returns the query arguments unmodified.
		 */
		if ( ! in_array( Tribe__Events__Main::POSTTYPE, (array) $query_args['post_type'], true ) ) {
			return $query_args;
		}

		// Set the 'tribe_suppress_query_filters' to true.
		$query_args['tribe_suppress_query_filters'] = true;

		return $query_args;
	}
}
