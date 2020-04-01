<?php

namespace Tribe\Events\Views\V2\Partials\Past_Events_View\Top_Bar;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class NavTest extends HtmlPartialTestCase
{

	protected $partial_path = 'past-events/top-bar/nav';

	/**
	 * Test render with all links
	 */
	public function test_render_with_all_links() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'prev_url' => 'http://test.tri.be',
			'next_url' => 'http://test.tri.be',
		] ) );
	}

	/**
	 * Test render without prev url
	 */
	public function test_render_without_prev_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'next_url' => 'http://test.tri.be',
		] ) );
	}

	/**
	 * Test render without next url
	 */
	public function test_render_without_next_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'prev_url' => 'http://test.tri.be',
		] ) );
	}

	/**
	 * Test render without prev and next url
	 */
	public function test_render_without_prev_and_next_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
