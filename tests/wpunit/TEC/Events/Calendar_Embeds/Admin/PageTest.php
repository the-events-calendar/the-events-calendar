<?php

namespace TEC\Events\Calendar_Embeds;

use TEC\Events\Calendar_Embeds\Admin\Page;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class PageTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	public function testRegisterMenuItem() {
		global $submenu;

		$this->set_fn_return( 'current_user_can', true );

		// Register the main post type so the menu exists.
		tribe( 'tec.main' )->registerPostType();

		tribe( Page::class )->register_menu_item();

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
		$label = tribe( Page::class )->get_menu_label();
		$this->assertEquals('Embed Calendar', $label);
	}

	public function testGetPageTitle() {
		$title = tribe( Page::class )->get_page_title();
		$this->assertEquals('Embed Calendar', $title);
	}

	public function testKeepParentMenuOpen() {
		global $parent_file, $submenu_file;

		$page = tribe( Page::class );

		// Test when parent file is not the Calendar Embeds post type.
		$parent_file = 'edit.php?post_type=some_other_post_type';
		$submenu_file = 'some_submenu_file';
		$result = $page->keep_parent_menu_open( $submenu_file );
		$this->assertEquals( 'some_submenu_file', $result );
		$this->assertEquals( 'edit.php?post_type=some_other_post_type', $parent_file );

		// Test when parent file is the Calendar Embeds post type.
		$parent_file = 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE;
		$submenu_file = 'some_submenu_file';
		$result = $page->keep_parent_menu_open( $submenu_file );
		$this->assertEquals( 'some_submenu_file', $result );
		$this->assertEquals( 'edit.php?post_type=' . TEC::POSTTYPE, $parent_file );
	}

	public function testIsOnPage() {
		// Mock the get_current_screen function
		$this->set_fn_return('get_current_screen', (object) [ 'id' => 'edit-' . Calendar_Embeds::POSTTYPE ] );
		$this->assertTrue( Page::is_on_page() );

		// Mock the get_current_screen function to return null
		$this->set_fn_return('get_current_screen', null );
		$this->assertFalse( Page::is_on_page() );

		// Mock the get_current_screen function to return a different screen id
		$this->set_fn_return('get_current_screen', (object) [ 'id' => 'edit-other_posttype' ] );
		$this->assertFalse( Page::is_on_page() );
	}
}
