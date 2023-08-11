<?php

namespace Tribe\Events\Views\Blocks;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;
use Tribe__Events__Editor__Blocks__Classic_Event_Details;

class Classic_Event_DetailsTest extends HtmlTestCase {
	use MatchesSnapshots;

	/**
	 * Test that the block is rendered.
	 */
	public function test_block_is_rendered() {
		$block         = new Tribe__Events__Editor__Blocks__Classic_Event_Details();
		$block_content = $block->render();

		$this->assertStringContainsString( 'tribe-events-single-section', $block_content );
		$this->assertStringContainsString( 'tribe-events-event-meta', $block_content );
		$this->assertStringContainsString( 'primary', $block_content );
		$this->assertStringContainsString( 'tribe-clearfix', $block_content );
	}

	/**
	 * Test that the block is rendered with no custom classes.
	 */
	public function test_render_no_custom_classes() {
		$block         = new Tribe__Events__Editor__Blocks__Classic_Event_Details();
		$block_content = $block->render();

		$this->assertMatchesSnapshot( $block_content );
	}

	/**
	 * Test that the block is rendered with single custom class.
	 */
	public function test_render_with_single_custom_class() {
		$block_with_custom_class = new Tribe__Events__Editor__Blocks__Classic_Event_Details();
		$custom_class_content    = $block_with_custom_class->render( [ 'className' => 'custom-class' ] );

		$this->assertMatchesSnapshot( $custom_class_content );
	}

	/**
	 * Test that the block is rendered with multiple custom classes.
	 */
	public function test_render_with_multiple_custom_classes() {
		$block_with_custom_class = new Tribe__Events__Editor__Blocks__Classic_Event_Details();
		$custom_class_content    = $block_with_custom_class->render( [ 'className' => 'custom-class custom-class-2' ] );

		$this->assertMatchesSnapshot( $custom_class_content );
	}
}
