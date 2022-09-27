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
use TEC\Common\Menus\Traits\With_Admin_Bar;

/**
 * Class Admin Troubleshooting.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Troubleshooting extends Abstract_Menu {
	use Submenu, With_Admin_Bar;

	/**
	 * {@inheritDoc}
	 */
	protected $capability = 'edit_tribe_events';

	/**
	 * {@inheritDoc}
	 */
	public $menu_slug = 'tec-troubleshooting';

	/**
	 * {@inheritDoc}
	 */
	protected $position = 60;

	/**
	 * {@inheritDoc}
	 */
	public function init() : void {
		$this->menu_title  = _x( 'Troubleshooting', 'The title for the admin menu link', 'the-events-calendar');
		$this->page_title  = _x( 'Troubleshooting', 'The title for the admin page', 'the-events-calendar');
		$this->parent_slug = 'tec-events';

		parent::init();
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() : void {
		tribe_asset_enqueue( 'tribe-admin-help-page' );
		$main = \Tribe__Main::instance();
		include_once $main->plugin_path . 'src/admin-views/troubleshooting.php';
	}
}
