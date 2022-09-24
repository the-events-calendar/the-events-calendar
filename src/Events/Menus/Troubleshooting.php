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
	use Submenu;
	use With_Admin_Bar; // not working - have to detach from \Tribe\Admin\Troubleshooting

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
	protected $position = 25;

	/**
	 * {@inheritDoc}
	 */
	public function init() : void {
		parent::init();

		$this->menu_title  = _x( 'Troubleshooting', 'The title for the admin menu link', 'the-events-calendar');
		$this->page_title  = _x( 'Troubleshooting', 'The title for the admin page', 'the-events-calendar');
		$this->parent_slug = 'tec-events';
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() : void {
		echo "Troubleshooting";
		//tribe_asset_enqueue( 'tribe-admin-help-page' );
		//include_once Tribe__Events__Main::instance()->plugin_path . 'content/troubleshooting.php';
	}
}
