<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Top_Bar;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DatepickerTest extends HtmlPartialTestCase
{

	protected $partial_path = 'day/top-bar/datepicker';

	/**
	 * Test render
	 */
	public function test_render() {
		$this->markTestSkipped( 'The "today" variable is not working as expected' );

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today' => '2018-01-01',
		] ) );
	}
}
