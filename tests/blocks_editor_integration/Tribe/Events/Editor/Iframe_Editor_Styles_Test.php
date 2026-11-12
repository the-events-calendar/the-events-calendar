<?php

namespace Tribe\Events\Editor;

use Codeception\TestCase\WPTestCase;
use Generator;
use TEC\Common\StellarWP\Assets\Assets;
use Tribe\Events\Test\Factories\Event as Event_Factory;
use Tribe__Events__Main as TEC;

/**
 * Editor stylesheets load in both the editor page and the iframed editor canvas, never on the front end.
 */
class Iframe_Editor_Styles_Test extends WPTestCase {
	/**
	 * @before
	 */
	public function reset_editor_styles(): void {
		foreach ( $this->editor_style_handles() as [ $handle ] ) {
			wp_dequeue_style( $handle );
			$asset = Assets::init()->get( $handle );
			if ( $asset ) {
				$asset->set_as_unenqueued();
			}
		}

		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		tribe( 'events.editor' )->assets();
	}

	/**
	 * @after
	 */
	public function reset_screen(): void {
		unset( $GLOBALS['current_screen'] );
	}

	public function editor_style_handles(): Generator {
		yield 'tec_widget_blocks' => [ 'tec-widget-blocks-styles' ];
		yield 'tec_block_editor_main' => [ 'tribe-block-editor-main' ];
		yield 'tec_admin_v2_single_blocks' => [ 'tribe-admin-v2-single-blocks' ];
		yield 'common_gutenberg_main' => [ 'tribe-common-gutenberg-main-styles' ];
	}

	/**
	 * @test
	 * @dataProvider editor_style_handles
	 */
	public function it_should_load_the_style_in_the_editor_page_and_the_editor_canvas( string $handle ): void {
		set_current_screen( TEC::POSTTYPE );
		get_current_screen()->is_block_editor( true );

		/*
		 * The same order as wp-admin/edit-form-blocks.php: the editor page assets first, then the
		 * canvas assets core collects for the iframe, then the admin page head.
		 */
		do_action( 'enqueue_block_editor_assets' );
		$iframe_assets = _wp_get_iframed_editor_assets();
		do_action( 'admin_enqueue_scripts', 'post-new.php' );

		$this->assertStringContainsString(
			"id='{$handle}-css'",
			$iframe_assets['styles'],
			"{$handle} must be collected by core for the editor iframe, not cloned from the editor page."
		);
		$this->assertTrue(
			wp_style_is( $handle, 'enqueued' ),
			"{$handle} must stay on the editor page: popovers and the sidebar render outside the iframe."
		);
	}

	/**
	 * @test
	 * @dataProvider editor_style_handles
	 */
	public function it_should_not_load_the_style_on_the_front_end( string $handle ): void {
		set_current_screen( 'front' );
		$this->go_to( get_permalink( ( new Event_Factory() )->create() ) );

		do_action( 'enqueue_block_assets' );

		$this->assertFalse( wp_style_is( $handle, 'enqueued' ), "{$handle} is editor-only CSS." );
	}
}
