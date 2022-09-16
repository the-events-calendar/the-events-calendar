<?php

/**
 * Admin Home for TEC plugins.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */

namespace TEC\Events\Menus;

use TEC\Common\Menus\Abstract_Menu;
use TEC\Common\Menus\Traits\Submenu;

/**
 * Class Admin Home.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Home extends Abstract_Menu {
	use Submenu;

	public function __construct() {
		$this->page_title      = _x( 'Home', 'The title for the admin page', 'the-events-calendar');
		$this->menu_title      = _x( 'Home', 'The title for the admin menu link', 'the-events-calendar');
		$this->position        = 0;
		$this->parent_slug     = 'tec-events';
		self::$menu_slug       = 'tec-home';
		$this->capability      = 'edit_tribe_events';

		parent::__construct();
	}

	public function render() {
		echo "Welcome Home";
	}

}
