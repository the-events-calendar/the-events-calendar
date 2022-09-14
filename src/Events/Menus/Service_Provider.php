<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package TEC\Events\Menus
 * @since   TBD
 */

namespace TEC\Events\Menus;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Menus
 */
class Service_Provider extends \tad_DI52_ServiceProvider {
	public $top_level = [
		'TEC_Menu',
	];

	public $submenus = [
		'Venue',
	];

	public function register() {
		$this->register_menus();
		$this->register_submenus();
	}

	public function register_menus() {
		foreach ( $this->top_level as $menu ) {
			$menu = __NAMESPACE__ . '\\' . $menu;
			$menu_class = new $menu;
			tribe_singleton( $menu_class::class, $menu_class::class );
		}
	}

	public function register_submenus() {
		foreach ( $this->submenus as $submenu ) {
			$submenu = __NAMESPACE__ . '\\' . $submenu;
			$submenu_class = new $submenu;
			tribe_singleton( $submenu_class::class, $submenu_class::class );
		}
	}

}
