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

	public function register() {
		// Filter Bar upsell tab.
		tribe_register_provider( Filter_Bar_Upsell::class );

		// Community upsell tab.
		tribe_register_provider( Community_Upsell::class );

		$this->add_actions();
		$this->add_filters();
	}

	public function add_actions() {}

	public function add_filters() {
		add_filter( 'tribe_settings_wrap_classes', [ $this, 'filter_tribe_settings_wrap_classes' ], 10, 2 );
	}

	public function filter_tribe_settings_wrap_classes( $classes, $admin_page ) {
		if ( $admin_page !== Settings::$settings_page_id ) {
			return $classes;

		}

		$classes[] = 'tec-events-admin-settings';

		return $classes;
	}

}
