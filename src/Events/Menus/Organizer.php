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
use TEC\Common\Menus\Traits\CPT;
use TEC\Common\Menus\Traits\Submenu;
use Tribe__Events__Organizer;

/**
 * Class Admin Home.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Organizer extends Abstract_Menu {
	use Submenu;
	use CPT;

	public function __construct() {
		$this->page_title      = _x( 'Organizers', 'The title for the admin page', 'the-events-calendar');
		$this->menu_title      = _x( 'Organizers', 'The title for the admin menu link', 'the-events-calendar');
		$this->position        = 20;
		$this->parent_slug     = 'tec-events';
		$this->capability      = 'edit_tribe_events';
		static::$post_type       = Tribe__Events__Organizer::POSTTYPE;

		parent::__construct();
	}

}
