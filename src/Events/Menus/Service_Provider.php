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
	public $menus = [
		'TEC_Menu',
		'Event',
		//'New Event', Handled by the CPT Trait of the Event menu.
		//'Tags',
		//'Event Categories',
		//'Series', * ECP only
		'Venue',
		'Organizer',
		//'Import',
		//'Settings',
		//'Help',
		'Troubleshooting',
		// 'App Shop',
	];

	public function register() {
		foreach ( $this->menus as $menu ) {
			$menu_class = $this->get_class_object_from_name( $menu );
			// Create the singleton
			tribe_singleton( $menu_class::class, $menu_class::class );
			// Build the menu.
			tribe( $menu_class::class )->build();
		}
	}

	/**
	 * Given the "name" in the $menus array defined above, get ta class object.
	 *
	 * @since TBD
	 *
	 * @param string $name
	 */
	private function get_class_object_from_name( $name ) : \TEC\Common\Menus\Abstract_Menu {
		$name = __NAMESPACE__ . '\\' . $name;
		return new $name;
	}

}
