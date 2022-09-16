<?php

/**
 * Admin Troubleshooting for TEC plugins.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */

namespace TEC\Events\Menus;

use TEC\Common\Menus\Abstract_Menu;
use TEC\Common\Menus\Traits\Submenu;

/**
 * Class Admin Troubleshooting.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Troubleshooting extends Abstract_Menu {
	use Submenu;
	//use With_Admin_Bar;

	public function __construct() {
		$this->page_title      = _x( 'Troubleshooting', 'The title for the admin page', 'the-events-calendar');
		$this->menu_title      = _x( 'Troubleshooting', 'The title for the admin menu link', 'the-events-calendar');
		$this->position        = 25;
		$this->parent_slug     = 'tec-events';
		self::$menu_slug       = 'tec-troubleshooting';
		$this->capability      = 'edit_tribe_events';
		$this->adminbar_parent = 'tribe-events-settings-group';

		parent::__construct();
	}

	public function render() {
		echo "Troubleshooting";
		//tribe_asset_enqueue( 'tribe-admin-help-page' );
		//include_once Tribe__Events__Main::instance()->plugin_path . 'content/troubleshooting.php';
	}
}
