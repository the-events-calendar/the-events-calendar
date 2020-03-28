<?php

namespace Tribe\Events\Views\V2\Partials\Month_View;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Calendar_HeaderTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-header';

	/**
	 * Test render
	 */
	public function test_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
