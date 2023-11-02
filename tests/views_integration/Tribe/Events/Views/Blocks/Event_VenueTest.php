<?php

namespace Tribe\Events\Views\Blocks;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;
use Tribe__Events__Editor__Blocks__Event_Venue;

class Event_VenueTest extends HtmlTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		add_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
		tribe( \Tribe__Events__Editor__Provider::class )->register();
	}

	/**
	 * Test that the block is rendered.
	 */
	public function test_block_is_rendered() {
		ob_start();
		echo ( new Tribe__Events__Editor__Blocks__Event_Venue() )->render();
		$block_content = ob_get_clean();

		$this->assertStringContainsString( 'tribe-block', $block_content );
		$this->assertStringContainsString( 'tribe-block__venue', $block_content );
		$this->assertStringContainsString( 'tribe-clearfix', $block_content );
	}

	/**
	 * Test that the block is rendered with no custom classes.
	 */
	public function test_render_no_custom_classes() {
		ob_start();
		echo ( new Tribe__Events__Editor__Blocks__Event_Venue() )->render();
		$block_content = ob_get_clean();

		$this->assertMatchesSnapshot( $block_content );
	}

	/**
	 * Test that the block is rendered with a single custom class.
	 */
	public function test_render_with_single_custom_class() {
		$block_with_custom_class = new Tribe__Events__Editor__Blocks__Event_Venue();
		$custom_class_content    = $block_with_custom_class->render( [ 'className' => 'custom-class' ] );

		$this->assertMatchesSnapshot( $custom_class_content );
	}

	/**
	 * Test that the block is rendered with multiple custom classes.
	 */
	public function test_render_with_multiple_custom_classes() {
		$block_with_custom_class = new Tribe__Events__Editor__Blocks__Event_Venue();
		$custom_class_content    = $block_with_custom_class->render( [ 'className' => 'custom-class custom-class-2' ] );

		$this->assertMatchesSnapshot( $custom_class_content );
	}
}
