<?php

namespace TEC\Events\Integrations\Plugins\Rank_Math;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Common\Integrations\Traits\Plugin_Integration;

/**
 * Class Provider
 *
 * @since   6.0.13
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
	 * @return bool Whether or not integrations should load.
	 */
	public function load_conditionals(): bool {
		return defined( 'RANK_MATH_FILE' ) && ! empty( RANK_MATH_FILE );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		add_filter( 'tribe_events_add_canonical_tag', '__return_false' );
	}
}
