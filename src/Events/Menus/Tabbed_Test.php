<?php

/**
 * Admin Tabbed menu/page for TEC plugins.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */

namespace TEC\Events\Menus;

use TEC\Common\Menus\Abstract_Menu;
use TEC\Common\Menus\Traits\Submenu;
use TEC\Common\Menus\Traits\Tabbed;
use Tribe__Events__Main;

/**
 * Class Add_Ons admin/menu.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Tabbed_Test extends Abstract_Menu {
	use Submenu, Tabbed;

	/**
	 * {@inheritDoc}
	 */
	protected $capability = 'edit_tribe_events';

	/**
	 * {@inheritDoc}
	 */
	protected $position = 70;

	/**
	 * {@inheritDoc}
	 */
	public $menu_slug = 'tec-tabs';


	/**
	 * {@inheritDoc}
	 */
	public function init() : void {
		$this->menu_title   = _x( 'Tabbed Test', 'The title for the admin menu link', 'the-events-calendar');
		$this->page_title   = _x( 'Tabbed Test', 'The title for the admin page', 'the-events-calendar');
		$this->parent_file  = 'tec-events';
		$this->parent_slug  = 'tec-events';
		$this->tabs         = $this->do_tabs();

		parent::init();
	}

	protected function tabbed_hooks() {
		add_action( 'tec_menus_before_tab_nav_' . $this->get_slug(), [ $this, 'before_nav' ] );
		add_action( 'tec_menus_after_tab_nav_' . $this->get_slug(), [ $this, 'after_nav' ] );
		add_action( 'tec_menus_before_tab_content_' . $this->get_slug(), [ $this, 'before_content' ] );
		add_action( 'tec_menus_after_tab_content_' . $this->get_slug(), [ $this, 'after_content' ] );
	}

	protected function do_tabs() {
		return [
			[
				'id' => 'one',
				'title' => 'One',
				'class' => 'some-class',
				'content' => '<p>Fnord</p>',
			],
			[
				'id' => 'two',
				'title' => 'Two',
				'class' => [
					'some-class',
					'some-other-class',
				],
				'content' => '<ul>
				<li>Yep,</li>
				<li>HTML</li>
				<li>works</li>
				<li>too!</li>
				</ul>
				<ul>
				<li>Container</li>
				<li>stretches</li>
				<li>to</li>
				<li>fit!</li>
				</ul>',
			],
			[
				'id' => 'three',
				'title' => 'Three',
				'class' => [
					'some-class',
					'some-other-class',
					'yet-another-class',
				],
				'content' => [ $this, 'tab_three_content' ],
			],
		];
	}

	protected function tab_three_content() {
		echo '<marquee>Yep, callables work too!</marquee>';
	}

	public function before_nav() {
		echo "<p>something before the nav (header?)</p>";
	}

	public function after_nav () {
		echo "<p>something after the nav (divider?)</p>";
	}

	public function before_content() {
		echo "<p>something before the content (static content?)</p>";
	}

	public function after_content() {
		echo "<p>something after the content (footer?)</p>";
	}
}
