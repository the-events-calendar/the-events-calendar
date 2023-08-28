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

	public function tearDown(): void {
		// Remove any added filters, cleanup resources, etc.
		remove_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
		parent::tearDown();
	}

	/**
	 * Test that the block contains 'tribe-block' class.
	 */
	public function test_block_contains_tribe_block_class() {
		$block_content = $this->renderSingleEventBlock();
		$this->assertStringContainsString( 'tribe-block', $block_content );
	}

	/**
	 * Test that the block contains 'tribe-block__single-event' class.
	 */
	public function test_block_contains_tribe_block_single_event_class() {
		$block_content = $this->renderSingleEventBlock();
		$this->assertStringContainsString( 'tribe-block__single-event', $block_content );
	}

	/**
	 * Utility method to render the block and return the content.
	 */
	private function renderSingleEventBlock(): string {
		ob_start();
		echo ( new Single_Event() )->render();

		return ob_get_clean();
	}
}
