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

	/**
	 * {@inheritDoc}
	 */
	public static $menu_slug = 'tec-troubleshooting';

	/**
	 * {@inheritDoc}
	 */
	protected $position = 25;

	/**
	 * {@inheritDoc}
	 */
	protected $capability = 'edit_tribe_events';

	/**
	 * {@inheritDoc}
	 */
	public function init() {
		parent::init();

		$this->menu_title  = _x( 'Troubleshooting', 'The title for the admin menu link', 'the-events-calendar');
		$this->page_title  = _x( 'Troubleshooting', 'The title for the admin page', 'the-events-calendar');
		$this->parent_slug = 'tec-events';
		// $this->adminbar_parent = 'tribe-events-settings-group';
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() {
		echo "Troubleshooting";
		//tribe_asset_enqueue( 'tribe-admin-help-page' );
		//include_once Tribe__Events__Main::instance()->plugin_path . 'content/troubleshooting.php';
	}
}
