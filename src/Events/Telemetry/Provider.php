<?php
/**
 * Service Provider for Telemetry.
 *
 * @since   TBD
 *
 * @package TEC\Events\Telemetry
 */

namespace TEC\Events\Telemetry;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Provider
 *
 * @since   TBD

 * @package TEC\Events\Telemetry
 */
class Provider extends Service_Provider {

	/**
	 * Registers the handlers and modifiers for notifying the site
	 * that Legacy views are removed.
	 *
	 * @since 5.13.0
	 */
	public function register() {
		$this->container->bind( Telemetry::class, Telemetry::class );

		$this->add_actions();
		$this->add_filters();
	}

	public function add_actions() {
		add_action( 'tribe_plugins_loaded', [ $this, 'initialize_telemetry' ] );
		add_action( 'admin_init', [ $this, 'save_opt_in_setting_field' ] );
		add_action( 'tec-telemetry-modal', [ $this, 'do_optin_modal' ] );

	}

	public function add_filters() {}

	public function initialize_telemetry() {
		$this->container->make( Telemetry::class )->init();
	}

	public function save_opt_in_setting_field() {
		$this->container->make( Telemetry::class )->save_opt_in_setting_field();
	}

	public function do_optin_modal() {
		$this->container->make( Telemetry::class )->do_optin_modal();
	}
}
