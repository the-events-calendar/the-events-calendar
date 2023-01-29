<?php

namespace TEC\Events\Installer;

use TEC\Common\StellarWP\Installer\Installer;

class Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		Installer::get()->register_plugin( 'event-tickets', 'Event Tickets' );
	}
}