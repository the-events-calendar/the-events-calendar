<?php

namespace TEC\Events\Calendar_Embeds;

use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class Calendar_EmbedsTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	/**
	 * @var Calendar_Embeds
	 */
	protected $calendarEmbeds;

	protected function setUp(): void {
		parent::setUp();
		$this->calendarEmbeds = new Calendar_Embeds();
	}
	
	protected function tearDown() : void {
		parent::tearDown();
		$this->calendarEmbeds = null;
		$this->unset_uopz_functions();
	}

	public function testRegisterPostType() {
		// Mock the WordPress functions
		global $wp_post_types;
		$wp_post_types = [];

		$this->calendarEmbeds->register_post_type();

		$this->assertArrayHasKey( Calendar_Embeds::POSTTYPE, $wp_post_types );
		$this->assertEquals( 'Calendar Embeds', $wp_post_types[Calendar_Embeds::POSTTYPE]->labels->name );
		$this->assertFalse( $wp_post_types[Calendar_Embeds::POSTTYPE]->public );
		$this->assertTrue( $wp_post_types[Calendar_Embeds::POSTTYPE]->show_ui );
	}

	public function testRegisterMenuItem() {
		// Mock the WordPress functions
		global $submenu, $menu;
		$submenu = [];
		$menu = [];

		$this->set_fn_return( 'get_post_type_object', function() {
			return (object) [
				'labels' => (object) [
					'name' => 'Event',
				],
				'cap' => (object) [
					'publish_posts' => 'publish_posts',
				],
			];
		}, true );
		$this->set_fn_return( 'current_user_can', true );

		// Register the main post type so the menu exists.
		$tec_main = TEC::instance();
		$tec_main->registerPostType();

		$this->calendarEmbeds->register_menu_item();

		// Check if submenu was created within main menu.
		$submenu_key = 'edit.php?post_type=' . TEC::POSTTYPE;
		$this->assertArrayHasKey( $submenu_key, $submenu );

		// Check submenu data.
		$submenu_data = $submenu[$submenu_key];
		[ $title, $cap, $url, $label ] = $submenu_data[0];

		$this->assertEquals( 'Embed Calendar', $title );
		$this->assertEquals( 'publish_posts', $cap );
		$this->assertEquals( 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE, $url );
		$this->assertEquals( 'Embed Calendar', $label );
	}

	public function testGetMenuLabel() {
		$label = $this->calendarEmbeds->get_menu_label();
		$this->assertEquals('Embed Calendar', $label);
	}

	public function testGetPageTitle() {
		$title = $this->calendarEmbeds->get_page_title();
		$this->assertEquals('Embed Calendar', $title);
	}
}
