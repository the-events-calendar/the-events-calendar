<?php

namespace Tribe\Events\Views\V2\Template;

use Codeception\TestCase\WPTestCase;

class PageTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		add_shortcode( 'tec_test_shortcode', static fn() => 'SHORTCODE_EXECUTED' );
	}

	public function tearDown(): void {
		remove_shortcode( 'tec_test_shortcode' );
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function it_should_not_run_shortcodes_found_in_the_view_html() {
		$view_html = '<p>There were no results found for "zzq[tec_test_shortcode]".</p>';
		add_filter( 'tribe_events_views_v2_bootstrap_pre_get_view_html', static fn() => $view_html );

		$html = ( new Page() )->filter_hijack_page_content();

		$this->assertSame( $view_html, $html );
	}
}
