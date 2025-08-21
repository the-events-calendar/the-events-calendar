<?php

namespace Tribe\Events\Admin;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Main as TEC;

/**
 * This test method covers the class behavior in the default integration scenario, where Classy might or might not be
 * activated by default.
 *
 * @covers Tribe__Events__Admin__Event_Meta_Box
 */
class Event_Meta_Box_Test extends WPTestCase {
	/**
	 * @var object
	 */
	private ?object $original_editor = null;

	/**
	 * @before
	 */
	public function setup_editor_mocks(): void {
		$this->original_editor = tribe( 'editor' );
	}

	/**
	 * @after
	 */
	public function restore_editor_binding(): void {
		if ( $this->original_editor === null ) {
			return;
		}

		tribe()->singleton( 'editor', $this->original_editor );
	}

	/**
	 * @covers Tribe__Events__Admin__Event_Meta_Box::display_wp_custom_fields_metabox
	 */
	public function test_display_wp_custom_fields_metabox_when_using_blocks(): void {
		// Depending on whether Classy is loaded by default or not, we cannot know in advance the filters controlling this.
		tribe()->singleton( 'editor',
			new class {
				public function should_load_blocks(): bool {
					return true;
				}
			}
		);
		add_filter( 'tec_using_classy_editor', '__return_false' );
		// Re-register the post type to make sure the filter is applied.
		TEC::instance()->registerPostType();

		tribe( 'tec.admin.event-meta-box' )->display_wp_custom_fields_metabox();
		$post_type_supports = get_post_types_by_support( 'custom-fields' );

		$this->assertContains( TEC::POSTTYPE, $post_type_supports );
	}

	/**
	 * @covers Tribe__Events__Admin__Event_Meta_Box::display_wp_custom_fields_metabox
	 */
	public function test_display_wp_custom_fields_metabox_when_using_classy(): void {
		// Depending on whether Classy is loaded by default or not, we cannot know in advance the filters controlling this.
		tribe()->singleton( 'editor',
			new class {
				public function should_load_blocks(): bool {
					return false;
				}
			}
		);
		add_filter( 'tec_using_classy_editor', '__return_true' );
		// Re-register the post type to make sure the filter is applied.
		TEC::instance()->registerPostType();

		tribe( 'tec.admin.event-meta-box' )->display_wp_custom_fields_metabox();
		$post_type_supports = get_post_types_by_support( 'custom-fields' );

		$this->assertContains( TEC::POSTTYPE, $post_type_supports );
	}

	/**
	 * @covers Tribe__Events__Admin__Event_Meta_Box::display_wp_custom_fields_metabox
	 */
	public function test_display_wp_custom_fields_metabox_using_neither_block_editor_nor_classy(): void {
		// Depending on whether Classy is loaded by default or not, we cannot know in advance the filters controlling this.
		tribe()->singleton( 'editor',
			new class {
				public function should_load_blocks(): bool {
					return false;
				}
			}
		);
		add_filter( 'tec_using_classy_editor', '__return_false' );
		// Re-register the post type to make sure the filter is applied.
		TEC::instance()->registerPostType();

		tribe( 'tec.admin.event-meta-box' )->display_wp_custom_fields_metabox();
		$post_type_supports = get_post_types_by_support( 'custom-fields' );

		$this->assertNotContains( TEC::POSTTYPE, $post_type_supports );
	}
}
