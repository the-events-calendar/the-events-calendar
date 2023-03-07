<?php

namespace Tribe\Events\Integrations\Rank_Math;

/**
 * Class Service_Provider
 *
 * @since TBD
 *
 * @package Tribe\Events\Integrations\Rank_Math
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the bindings and hooks the filters required for the RankMath SEO integrations to work.
	 *
	 * @since TBD
	 */
	public function register() {
		add_filter( 'tribe_events_add_canonical_tag', '__return_false' );
	}
}
