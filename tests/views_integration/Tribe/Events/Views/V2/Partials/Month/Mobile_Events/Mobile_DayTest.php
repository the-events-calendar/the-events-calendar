<?php

namespace Tribe\Events\Views\V2\Partials\Month\Mobile_Events;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Mobile_DayTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/mobile-events/mobile-day';

	/**
	 * Test static render
	 */
	public function test_static_render() {
		 $context = [
		 	'day_date' => '2018-06-01',
		 	'day' => [
		 		'year_number' => '2018',
		 		'month_number' => '06',
		 		'day_number' => '01',
		 		'events' => []
		 	]
		 ];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}
}
