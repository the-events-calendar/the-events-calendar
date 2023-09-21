<?php

namespace Tribe\Events\Editor\Blocks;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Editor\Full_Site\Templates;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;
use WP_Block_Template;

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
		return ( new Archive_Events() )->render();
	}

	/**
	 * Test that the block contains 'tribe-block' class.
	 */
	public function test_block_contains_tribe_block_class() {
		$block_content = $this->render_archive_events_block();
		$this->assertStringContainsString( 'tribe-block', $block_content );
	}

	/**
	 * Test that the block contains 'tribe-block__archive-events' class.
	 */
	public function test_block_contains_tribe_block_archive_events_class() {
		$block_content = $this->render_archive_events_block();
		$this->assertStringContainsString( 'tribe-block__archive-events', $block_content );
	}

	/**
	 * Testing when the template file is queried.
	 */
	public function test_get_queried_template() {
		global $wp_query;
		$old_post_type = $wp_query->get( 'post_type' );
		$wp_query->set( 'post_type', [ 'tribe_events' ] );

		// Check we find the correct template without error.
		$template = get_archive_template();
		$this->assertMatchesSnapshot( $template );

		$wp_query->set( 'post_type', $old_post_type );
	}

	/**
	 * Test the Archive Event WP Template is generated correctly.
	 */
	public function test_wp_template() {
		// Get our templates to test.
		$archive_template = tribe( Archive_Events::class );
		$templateA        = tribe( Templates::class )->get_template_events_archive();
		$templateB        = get_block_template( $archive_template->get_namespace() . '//' . $archive_template->slug() );

		$this->assertIsInt( $templateA->wp_id );
		$this->assertGreaterThan( 0, $templateA->wp_id );
		$this->assertIsInt( $templateB->wp_id );
		$this->assertGreaterThan( 0, $templateB->wp_id );

		// Normalize for comparisons.
		$normalized_templateA = self::normalize_wp_template( $templateA );
		$normalized_templateB = self::normalize_wp_template( $templateB );

		// Should have correct content, title, id, slug etc.
		$this->assertEquals( $normalized_templateA, $normalized_templateB );
		$this->assertMatchesSnapshot( $normalized_templateA );
	}
}
