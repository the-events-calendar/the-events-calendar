<?php
/**
 * Handles registering all assets for the Migration area of the dashboard.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Admin
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Admin;

class Assets extends \tad_DI52_ServiceProvider {
	/**
	 * Key for this group of assets.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $group_key = 'tec-recurrence-upgrade';

	public function register() {
		$this->container->singleton( 'icaltec.migration.admin.assets', $this );
		$this->container->singleton( static::class, $this );

		$this->hook_actions();
	}

	/**
	 * Hooks actions for Assets.
	 *
	 * @since TBD
	 */
	private function hook_actions() {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 10 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
	}

	/**
	 * Registers the scripts required by the service provider.
	 *
	 * @since TBD
	 */
	public function register_scripts() {
		wp_register_style(
			'tec-recurrence-upgrade-admin-css',
			plugins_url( 'src/resources/css/custom-tables-v1/icaltec-upgrade.css', EVENTS_CALENDAR_PRO_FILE )
		);

		wp_register_script(
			'tec-recurrence-upgrade-admin-js',
			plugins_url( 'src/resources/js/custom-tables-v1/ical-tec-settings.js', EVENTS_CALENDAR_PRO_FILE )
		);
	}

	/**
	 * Enqueues the scripts required by the service provider.
	 *
	 * @since TBD
	 */
	public function enqueue_scripts() {
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		if ( $_GET['page'] !== tribe( 'settings' )->adminSlug ) {
			return;
		}

		wp_enqueue_style( 'tec-recurrence-upgrade-admin-css' );
		wp_enqueue_script( 'tec-recurrence-upgrade-admin-js' );
	}
}
