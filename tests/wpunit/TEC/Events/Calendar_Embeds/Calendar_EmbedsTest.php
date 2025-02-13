<?php

namespace TEC\Events\Calendar_Embeds;

use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class Calendar_EmbedsTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	public function testRegisterPostType() {
		// Mock the WordPress functions
		global $wp_post_types;

		tribe( Calendar_Embeds::class )->register_post_type();

		$this->assertArrayHasKey( Calendar_Embeds::POSTTYPE, $wp_post_types );
		$this->assertEquals( 'Calendar Embeds', $wp_post_types[ Calendar_Embeds::POSTTYPE ]->labels->name );
		$this->assertFalse( $wp_post_types[ Calendar_Embeds::POSTTYPE ]->public );
		$this->assertTrue( $wp_post_types[ Calendar_Embeds::POSTTYPE ]->show_ui );
	}

	public function testRegisterMenuItem() {
		// Mock the WordPress functions
		global $submenu;

		$this->set_fn_return( 'current_user_can', true );

		// Register the main post type so the menu exists.
		tribe( 'tec.main' )->registerPostType();

		tribe( Calendar_Embeds::class )->register_menu_item();

		$this->unset_uopz_returns();

		// Check if submenu was created within main menu.
		$submenu_key = 'edit.php?post_type=' . TEC::POSTTYPE;
		$this->assertArrayHasKey( $submenu_key, $submenu );

		// Check submenu data.
		$submenu_data = $submenu[$submenu_key];
		[ $title, $cap, $url, $label ] = $submenu_data[0];

		$this->assertEquals( 'Embed Calendar', $title );
		$this->assertEquals( 'publish_tribe_events', $cap );
		$this->assertEquals( 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE, $url );
		$this->assertEquals( 'Embed Calendar', $label );
	}

	public function testGetMenuLabel() {
		$label = tribe( Calendar_Embeds::class )->get_menu_label();
		$this->assertEquals('Embed Calendar', $label);
	}

	public function testGetPageTitle() {
		$title = tribe( Calendar_Embeds::class )->get_page_title();
		$this->assertEquals('Embed Calendar', $title);
	}
}
