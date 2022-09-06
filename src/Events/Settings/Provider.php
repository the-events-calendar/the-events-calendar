<?php

namespace TEC\Events\Settings;

/**
 * Class Provider
 *
 * @since TBD
 *
 * Handles the registration and creation of our async process handlers.
 */
class Provider extends \tad_DI52_ServiceProvider {

	public function register() {
		$this->container->singleton( Settings::class, Settings::class );
		$this->container->singleton(
			'event.settings',
			function() {
				return $this->container->make( Settings::class );
			}
		);

		/**
		 * Allows plugins to hook into the register action to register views, etc
		 *
		 * @since TBD
		 *
		 * @param TEC\Common\Settings\Provider
		 */
		do_action( 'tec_events_settings_register', $this );

		$this->hooks();
	}

	public function hooks() {
		add_action( 'admin_menu', [ $this, 'add_events_admin_menu' ] );
		add_action( 'tec_add_settings_pages', [ $this, 'add_events_settings' ] );
	}

	public function add_events_admin_menu() {
		return $this->container->make( Settings::class )->add_menu();
	}

	public function add_events_settings() {
		return $this->container->make( Settings::class )->add_settings();
	}
}
