<?php

namespace Tribe\Events\Editor\Blocks;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Single_EventTest extends HtmlTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * Test that the Single_Event block's render matches the snapshot.
	 */
	public function test_single_event_block_render_matches_snapshot() {
		$block = new Single_Event();

		// Render the block with some attributes. Adjust as needed.
		$rendered_content = $block->render( [] );

		// Assert the rendered content matches a previously stored snapshot.
		$this->assertMatchesSnapshot( $rendered_content );
	}
}
