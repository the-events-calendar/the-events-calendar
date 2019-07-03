<?php

namespace Tribe\Events\Views\V2\Partials\Month\Calendar_Body;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DayTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-body/day';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day'   => 1,
			'week'  => 2,
			'month' => [],
		] ) );
	}
}
