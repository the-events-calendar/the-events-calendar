<?php

namespace TEC\Events\Installer;

use TEC\Events\StellarWP\Installer;

class Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		$this->bootstrap();

		add_filter( 'stellarwp/installer/tec_events/button_classes', [ $this, 'filter_button_classes' ] );
	}

	/**
	 * Hooked to the plugins_loaded action.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function bootstrap() {
		Installer\Config::set_hook_prefix( 'tec_events' );
		Installer\Installer::get()->register_plugin( 'event-tickets', 'Event Tickets' );
	}

	/**
	 * Filters the installer button classes.
	 *
	 * @since TBD
	 *
	 * @param array|mixed $classes The button classes.
	 *
	 * @return array
	 */
	public function filter_button_classes( $classes ) {
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		$classes[] = 'components-button';
		$classes[] = 'is-primary';
		$classes[] = 'tec-admin__notice-install-content-button';
		return $classes;
	}
}