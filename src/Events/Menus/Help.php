<?php

/**
 * Admin Help menu/page for TEC plugins.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */

namespace TEC\Events\Menus;

use TEC\Common\Menus\Abstract_Menu;
use TEC\Common\Menus\Traits\Submenu;
use TEC\Common\Menus\Traits\With_Admin_Bar;

/**
 * Class Add_Ons admin/menu.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Help extends Abstract_Menu {
	use Submenu, With_Admin_Bar;

	/**
	 * {@inheritDoc}
	 */
	protected $capability = 'edit_tribe_events';

	/**
	 * {@inheritDoc}
	 */
	public $menu_slug = 'tec-help';

	/**
	 * {@inheritDoc}
	 */
	protected $position = 55;

	/**
	 * {@inheritDoc}
	 */
	public function init() : void {
		parent::init();

		$this->menu_title   = _x( 'Help', 'The title for the admin menu link', 'the-events-calendar');
		$this->page_title   = _x( 'Help', 'The title for the admin page', 'the-events-calendar');
		$this->parent_file  = 'tec-events';
		$this->parent_slug  = 'tec-events';
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() : void {
		include_once \Tribe__Main::instance()->plugin_path . 'src/admin-views/help.php';
	}
}
