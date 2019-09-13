<?php

namespace Tribe\Events\Views\V2\Partials\List_View\Top_Bar;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class TodayTest extends HtmlPartialTestCase
{

	protected $partial_path = 'list/top-bar/today';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today_url' => '#',
		] ) );
	}
}
