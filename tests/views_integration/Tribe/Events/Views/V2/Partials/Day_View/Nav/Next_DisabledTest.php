<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Nav;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Next_DisabledTest extends HtmlPartialTestCase
{

	protected $partial_path = 'day/nav/next-disabled';

	/**
	 * Test static render
	 */
	public function test_static_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
