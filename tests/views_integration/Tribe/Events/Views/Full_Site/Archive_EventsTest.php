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
		add_shortcode( 'tec_test_shortcode', static fn() => 'SHORTCODE_EXECUTED' );
	}

	public function tearDown(): void {
		remove_shortcode( 'tec_test_shortcode' );
		parent::tearDown();
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

	/**
	 * Test that shortcodes reflected into the view are not run by a later `do_shortcode()` pass.
	 */
	public function test_block_html_does_not_run_shortcodes_from_the_view() {
		add_filter(
			'tribe_events_views_v2_bootstrap_pre_get_view_html',
			static fn() => '<p>There were no results found for "zzq[tec_test_shortcode]" or [[tec_test_shortcode]] or [tec_test_shortcode]x[/tec_test_shortcode].</p><input value="zzq[tec_test_shortcode]">'
		);

		$html = do_shortcode( $this->render_archive_events_block() );

		$this->assertStringNotContainsString( 'SHORTCODE_EXECUTED', $html );
		$this->assertStringContainsString( '"zzq&#91;tec_test_shortcode]"', $html );
	}

	/**
	 * Test that brackets that do not open a registered shortcode are left as they are.
	 */
	public function test_block_html_keeps_brackets_that_are_not_shortcodes() {
		$view_html = '<script type="application/json">{"events":[],"bar":{"keyword":"[unregistered] [tec_test_shortcode-suffix] [tec_test_shortcodes]"}}</script>';
		add_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', static fn() => $view_html );

		$this->assertStringContainsString( $view_html, $this->render_archive_events_block() );
	}

	/**
	 * Test that a shortcode in the view JSON keeps its value for scripts and does not run.
	 */
	public function test_block_json_keeps_the_value_of_shortcode_text() {
		add_filter(
			'tribe_events_views_v2_bootstrap_pre_get_view_html',
			static fn() => '<script type="application/json">{"bar":{"keyword":"zz[tec_test_shortcode]"},"events":[]}</script>'
		);

		$html = do_shortcode( $this->render_archive_events_block() );

		preg_match( '#<script[^>]*>(.*?)</script>#s', $html, $matches );
		$this->assertStringNotContainsString( 'SHORTCODE_EXECUTED', $html );
		$this->assertSame( 'zz[tec_test_shortcode]', json_decode( $matches[1], true )['bar']['keyword'] );
	}

	/**
	 * Test that the block renders nothing, and logs why, when the shortcode check fails.
	 */
	public function test_block_html_is_empty_when_the_shortcode_check_fails() {
		/*
		 * PHP caches compiled patterns, and a pattern compiled with JIT ignores the backtrack limit.
		 * A shortcode no other test registers gives the replace pattern a fresh, non-JIT compile.
		 */
		add_shortcode( 'tec_test_pcre_limit', static fn() => 'SHORTCODE_EXECUTED' );
		add_filter(
			'tribe_events_views_v2_bootstrap_pre_get_view_html',
			static fn() => '<p>zz[tec_test_pcre_limit]</p><script type="application/json">{"keyword":"zz[tec_test_pcre_limit]"}</script>'
		);
		$logged = [];
		add_action(
			'tribe_log',
			static function ( $level, $message ) use ( &$logged ) {
				$logged[] = [ $level, $message ];
			},
			10,
			2
		);
		$jit             = ini_set( 'pcre.jit', '0' );
		$backtrack_limit = ini_set( 'pcre.backtrack_limit', '1' );

		try {
			$html = $this->render_archive_events_block();
		} finally {
			ini_set( 'pcre.jit', $jit );
			ini_set( 'pcre.backtrack_limit', $backtrack_limit );
			remove_shortcode( 'tec_test_pcre_limit' );
		}

		$this->assertSame( '', $html );
		$this->assertSame( [ [ 'error', 'Archive Events block HTML could not be checked for shortcodes.' ] ], $logged );
	}
}
