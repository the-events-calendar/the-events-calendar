<?php

namespace TEC\Events\Integrations\Plugins\Rank_Math;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Events\Integrations\Plugins\Plugin_Integration;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Rank_Math
 */
class Provider extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'seo-by-rank-math';
	}

	/**
	 * @inheritDoc
	 * 
	 * @return bool $should_load Whether or not integrations should load.
	 */
	public function load_conditionals(): bool {
		$should_load = true;

		if ( ! defined( 'RANK_MATH_FILE' ) || empty( RANK_MATH_FILE ) ) {
			$should_load = false;
		}

		return $should_load;
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		add_filter( 'tribe_events_add_canonical_tag', '__return_false' );
	}
}
