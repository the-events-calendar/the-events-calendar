<?php

namespace Tribe\Events\Editor\Blocks;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Archive_EventsTest extends HtmlTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		add_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
		tribe( \Tribe__Events__Editor__Provider::class )->register();
	}

	public function tearDown(): void {
		// Remove any added filters and cleanup resources.
		remove_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
		parent::tearDown();
	}

	/**
	 * Test that the block contains 'tribe-block' class.
	 */
	public function test_block_contains_tribe_block_class() {
		$block_content = $this->renderArchiveEventsBlock();
		$this->assertStringContainsString( 'tribe-block', $block_content );
	}

	/**
	 * Test that the block contains 'tribe-block__archive-events' class.
	 */
	public function test_block_contains_tribe_block_archive_events_class() {
		$block_content = $this->renderArchiveEventsBlock();
		$this->assertStringContainsString( 'tribe-block__archive-events', $block_content );
	}

	/**
	 * Test that the block matches the snapshot.
	 */
	public function test_block_matches_snapshot() {
		$block_content = $this->renderArchiveEventsBlock();
		$this->assertMatchesSnapshot( $block_content );
	}

	/**
	 * Utility method to render the block and return the content.
	 */
	private function renderArchiveEventsBlock(): string {
		ob_start();
		echo ( new Archive_Events() )->render();

		return ob_get_clean();
	}
}
