<?php
/**
 * Service Provider for interfacing with TEC\Common\Admin\Settings.
 *
 * @since
 *
 * @package TEC\Events\Admin\Settings
 */

namespace TEC\Events\Admin\Settings;

use TEC\Common\Contracts\Service_Provider;
use Tribe\Events\Admin\Settings;

/**
 * Class Provider
 *
 * @since
 *
 * @package TEC\Events\Admin\Settings
 */
class Provider extends Service_Provider {

	/**
	 * Register the service provider.
	 *
	 * @since 6.7.0
	 */
	public function register() {
		$providers = [
			Filter_Bar_Upsell::class,
			Community_Upsell::class,
		];

		foreach ( $providers as $provider ) {
			tribe_register_provider( $provider );
		}

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Add actions.
	 *
	 * @since 6.7.0
	 */
	public function add_actions(): void {}

	/**
	 * Add filters.
	 *
	 * @since 6.7.0
	 */
	public function add_filters(): void {
		add_filter( 'tribe_settings_wrap_classes', [ $this, 'filter_tribe_settings_wrap_classes' ], 10, 2 );
	}

	/**
	 * Add classes to the settings wrap.
	 *
	 * @since 6.7.0
	 *
	 * @param array<string> $classes    The classes to add to the settings wrap.
	 * @param string        $admin_page The current admin page.
	 *
	 * @return array<string> The classes to add to the settings wrap.
	 */
	public function filter_tribe_settings_wrap_classes( $classes, $admin_page ): array {
		if ( $admin_page !== Settings::$settings_page_id ) {
			return $classes;
		}

		$classes[] = 'tec-events-admin-settings';

		return $classes;
	}
}
