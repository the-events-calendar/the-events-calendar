<?php

namespace Tribe\Events\Views\V2\Partials\List_View\Nav;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Next_DisabledTest extends HtmlPartialTestCase
{

	protected $partial_path = 'list/nav/next-disabled';

	/**
	 * Test render
	 */
	public function test_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
