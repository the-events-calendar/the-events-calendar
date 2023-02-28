<?php

namespace TEC\Events\Installer;

use TEC\Common\StellarWP\Installer\Installer;
use TEC\Common\lucatume\DI52\ServiceProvider;


class Provider extends ServiceProvider {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.0.9
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		Installer::get()->register_plugin( 'event-tickets', 'Event Tickets' );
	}
}
