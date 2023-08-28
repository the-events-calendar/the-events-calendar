<?php

namespace Tribe\Events\Editor\Blocks;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Single_EventTest extends HtmlTestCase {
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
		echo ( new Single_Event() )->render();
		$block_content = ob_get_clean();

		$this->assertStringContainsString( 'tribe-block', $block_content );
		$this->assertStringContainsString( 'tribe-block__single-event', $block_content );
	}

	/**
	 * Test that the block is rendered with no custom classes.
	 */
	public function test_render_no_custom_classes() {
		ob_start();
		echo ( new Single_Event() )->render();
		$block_content = ob_get_clean();

		$this->assertMatchesSnapshot( $block_content );
	}
}
