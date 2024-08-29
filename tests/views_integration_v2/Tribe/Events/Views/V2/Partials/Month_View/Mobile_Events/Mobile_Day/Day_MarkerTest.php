<?php
namespace Tribe\Events\Views\V2\Partials\Month_View\Mobile_Events\Mobile_Day;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Day_MarkerTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/mobile-events/mobile-day/day-marker';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'event'    => null,
			'day_date' => '2018-05-06',
		] ) );
	}
}
