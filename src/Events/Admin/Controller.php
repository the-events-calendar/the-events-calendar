<?php
/**
 * Controller for the Events admin pages.
 *
 * @package TEC\Events\Admin
 * @since TBD
 */

namespace TEC\Events\Admin;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Controller for the Events admin pages.
 *
 * @since TBD
 */
class Controller extends Controller_Contract {

	/**
	 * Registers the filters and actions hooks for the controller.
	 *
	 * @since TBD
	 *
	 * @return void Filters and actions hooks for the controller are registered.
	 */
	protected function do_register(): void {
		add_filter( 'tec_get_admin_region', [ $this, 'filter_tec_get_admin_region' ] );
	}

	/**
	 * Unregisters the filters and actions hooks for the controller.
	 *
	 * @since TBD
	 *
	 * @return void Filters and actions hooks for the controller are unregistered.
	 */
	public function unregister(): void {
		remove_filter( 'tec_get_admin_region', [ $this, 'filter_tec_get_admin_region' ] );
	}

	/**
	 * Filters the admin region to return 'events' on TEC admin pages.
	 *
	 * @since TBD
	 *
	 * @param string $region The current admin region.
	 * @return string The admin region.
	 */
	public function filter_tec_get_admin_region( $region ) {
		if ( str_contains( get_admin_page_parent(), 'tribe_events' ) ) {
			return 'events';
		}

		return $region;
	}
}
