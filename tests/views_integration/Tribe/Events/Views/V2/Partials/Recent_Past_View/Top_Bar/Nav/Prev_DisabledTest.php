<?php

namespace Tribe\Events\Views\V2\Partials\Recent_Past_View\Top_Bar\Nav;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Prev_DisabledTest extends HtmlPartialTestCase
{

	protected $partial_path = 'recent-past/top-bar/nav/prev-disabled';

	/**
	 * Test render
	 */
	public function test_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
