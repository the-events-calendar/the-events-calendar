<?php

/**
 * Admin Import menu/page for TEC plugins.
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
class Import extends Abstract_Menu {
	use Submenu, With_Admin_Bar;

	/**
	 * {@inheritDoc}
	 */
	protected $capability = 'edit_tribe_events';

	/**
	 * {@inheritDoc}
	 */
	public $menu_slug = 'tec-import';

	/**
	 * {@inheritDoc}
	 */
	protected $position = 45;


	private $tec;

	private $posttype;

	/**
	 * {@inheritDoc}
	 */
	public function init() : void {
		$this->tec          = \Tribe__Events__Main::instance();
		$this->posttype     = \Tribe__Events__Main::POSTTYPE;
		$this->menu_title   = _x( 'Import', 'The title for the admin menu link', 'the-events-calendar');
		$this->page_title   = _x( 'Import', 'The title for the admin page', 'the-events-calendar');
		$this->parent_file  = 'tec-events';
		$this->parent_slug  = 'tec-events';

		parent::init();
	}

	/**
	 * {@inheritDoc}
	 */
	public function render() : void {
		?>
		<h1>Import!</h1>
		<?php
	}
}
