<?php

namespace Tribe\Events\Integrations\Yoast_SEO;

/**
 * Class Service_Provider
 *
 * @since TBD
 *
 * @package Tribe\Events\Integrations\Yoast_SEO
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the bindings and hooks the filters required for the Yoast SEO integrations to work.
	 *
	 * @since TBD
	 */
	public function register() {
		add_filter( 'tribe_events_add_canonical_tag', '__return_false' );
	}
}
