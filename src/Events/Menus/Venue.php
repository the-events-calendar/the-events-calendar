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
use Tribe__Events__Venue;

/**
 * Class Admin Home.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Venue extends Abstract_Menu {
	use Submenu, CPT;


	/**
	 * (@inheritDoc)
	 */
	protected $capability = 'edit_tribe_events';

	/**
	 * (@inheritDoc)
	 */
	public static $menu_slug = 'tec-events-venue';

	/**
	 * (@inheritDoc)
	 */
	protected $position = 15;

	/**
	 * (@inheritDoc)
	 */
	public function init() {
		parent::init();

		$this->menu_title  = _x( 'Venues', 'The title for the admin menu link', 'the-events-calendar');
		$this->page_title  = _x( 'Venues', 'The title for the admin page', 'the-events-calendar');
		$this->parent_file = 'tec-events';
		$this->parent_slug = 'tec-events';
		$this->post_type   = Tribe__Events__Venue::POSTTYPE;
	}

}
