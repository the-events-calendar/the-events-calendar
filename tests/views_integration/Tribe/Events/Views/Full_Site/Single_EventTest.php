<?php

namespace Tribe\Events\Editor\Blocks;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Blocks\Single_Event\Block;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class Single_EventTest extends HtmlTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		add_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
		tribe( \Tribe__Events__Editor__Provider::class )->register();
		add_shortcode( 'tec_test_shortcode', static fn() => 'SHORTCODE_EXECUTED' );
	}

	public function tearDown(): void {
		remove_shortcode( 'tec_test_shortcode' );
		parent::tearDown();
	}

	/**
	 * Test that the block contains 'tribe-block' class.
	 */
	public function test_block_contains_tribe_block_class() {
		$block_content = $this->renderSingleEventBlock();
		$this->assertStringContainsString( 'tec-block', $block_content );
	}

	/**
	 * Test that the block contains 'tribe-block__single-event' class.
	 */
	public function test_block_contains_tribe_block_single_event_class() {
		$block_content = $this->renderSingleEventBlock();
		$this->assertStringContainsString( 'tec-block__single-event', $block_content );
	}

	/**
	 * Test that shortcodes reflected into the view are not run by a later `do_shortcode()` pass.
	 */
	public function test_block_html_does_not_run_shortcodes_from_the_view() {
		add_filter(
			'tribe_events_views_v2_bootstrap_pre_get_view_html',
			static fn() => '<p>There were no results found for "zzq[tec_test_shortcode]" or [tec_test_shortcode]x[/tec_test_shortcode].</p><input value="zzq[tec_test_shortcode]">'
		);

		$html = do_shortcode( $this->renderSingleEventBlock() );

		$this->assertStringNotContainsString( 'SHORTCODE_EXECUTED', $html );
		$this->assertStringContainsString( '"zzq&#91;tec_test_shortcode]"', $html );
	}

	/**
	 * Test that a shortcode in the view JSON keeps its value for scripts and does not run.
	 */
	public function test_block_json_keeps_the_value_of_shortcode_text() {
		add_filter(
			'tribe_events_views_v2_bootstrap_pre_get_view_html',
			static fn() => '<script type="application/json">{"bar":{"keyword":"zz[tec_test_shortcode]"},"events":[]}</script>'
		);

		$html = do_shortcode( $this->renderSingleEventBlock() );

		preg_match( '#<script[^>]*>(.*?)</script>#s', $html, $matches );
		$this->assertStringNotContainsString( 'SHORTCODE_EXECUTED', $html );
		$this->assertSame( 'zz[tec_test_shortcode]', json_decode( $matches[1], true )['bar']['keyword'] );
	}

	/**
	 * Utility method to render the block and return the content.
	 */
	private function renderSingleEventBlock(): string {
		return ( new Block() )->render();
	}
}
