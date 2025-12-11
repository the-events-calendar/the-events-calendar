<?php

namespace Tribe\Events\Admin;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Main as TEC;

/**
 * This test method covers the class behavior in  scenario where Classy is active by default.
 *
 * @covers Tribe__Events__Admin__Event_Meta_Box
 */
class Event_Meta_Box_Test extends WPTestCase {
	/**
	 * @covers Tribe__Events__Admin__Event_Meta_Box::display_wp_custom_fields_metabox
	 */
	public function test_display_wp_custom_fields_metabox_when_using_classy(): void {
		tribe( 'tec.admin.event-meta-box' )->display_wp_custom_fields_metabox();
		$post_type_supports = get_post_types_by_support( 'custom-fields' );

		$this->assertContains( TEC::POSTTYPE, $post_type_supports );
	}

	/**
	 * @covers Tribe__Events__Admin__Event_Meta_Box::display_wp_custom_fields_metabox
	 */
	public function test_display_wp_custom_fields_metabox_without_classy(): void {
		add_filter( 'tec_using_classy_editor', '__return_false' );
		// Re-register the post type to make sure the filter is applied.
		TEC::instance()->registerPostType();

		tribe( 'tec.admin.event-meta-box' )->display_wp_custom_fields_metabox();
		$post_type_supports = get_post_types_by_support( 'custom-fields' );

		$this->assertNotContains( TEC::POSTTYPE, $post_type_supports );
	}
}
