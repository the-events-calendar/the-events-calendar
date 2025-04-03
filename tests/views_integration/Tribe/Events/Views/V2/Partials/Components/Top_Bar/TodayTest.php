<?php

namespace Tribe\Events\Views\V2\Partials\Components\Top_Bar;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class TodayTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/top-bar/today';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today_url'   => 'http://test.tri.be',
			'today_label' => 'Today',
			'today_title' => 'Click to select today\'s date',
		] ) );
	}
}
