<?php
/**
 * Service Provider for Site Health.
 *
 * @since   TBD
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Provider
 *
 * @since   TBD

 * @package TEC\Events\Site_Health
 */
class Provider extends Service_Provider {

	/**
	 * Registers the handlers and modifiers for notifying the site
	 * that Legacy views are removed.
	 *
	 * @since 5.13.0
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	public function add_actions() {}

	public function add_filters() {
		add_filter( 'debug_information', [ $this, 'filter_debug_information' ] );
	}

	public function filter_debug_information( $info ) {
		return $this->container->make( Site_Health::class )->add_data( $info );
	}
}
