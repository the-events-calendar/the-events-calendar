<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarHeaderTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'month/calendar-header' );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__header' )->count(),
			1,
			'Month Calendar Header HTML needs to contain one ".tribe-events-calendar-month__header" element'
		);
	}
}
