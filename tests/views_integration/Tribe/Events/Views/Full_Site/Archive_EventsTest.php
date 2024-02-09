<?php

namespace Tribe\Events\Editor\Full_Site;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Blocks\Archive_Events\Block;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Archive_EventsTest extends HtmlTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		add_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
		tribe( \Tribe__Events__Editor__Provider::class )->register();
	}

	/**
	 * Sorts and removed dynamic fields for consistent structures.
	 *
	 * @param $template
	 *
	 * @return array
	 */
	public static function normalize_wp_template( $template ): array {
		$array_template = (array) $template;
		asort( $array_template );
		// Dynamic fields, ditch them.
		foreach ( [ 'wp_id', 'author' ] as $field ) {
			$array_template[ $field ] = null;
		}

		return $array_template;
	}

	/**
	 * Utility method to render the block and return the content.
	 */
	private function render_archive_events_block(): string {
		return ( new Block() )->render();
	}

	/**
	 * Test that the block contains 'tribe-block' class.
	 */
	public function test_block_contains_tribe_block_class() {
		$block_content = $this->render_archive_events_block();
		$this->assertStringContainsString( 'tec-block', $block_content );
	}

	/**
	 * Test that the block contains 'tribe-block__archive-events' class.
	 */
	public function test_block_contains_tribe_block_archive_events_class() {
		$block_content = $this->render_archive_events_block();
		$this->assertStringContainsString( 'tec-block__archive-events', $block_content );
	}
}
