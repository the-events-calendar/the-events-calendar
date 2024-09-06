<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarHeaderTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'month/calendar-header', [], false );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__header' )->count(),
			1,
			'Month Calendar Header HTML needs to contain one ".tribe-events-calendar-month__header" element'
		);
	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {
		$template = $this->template->template( 'month/calendar-header', [], false );
		$html = $this->document->html( $template );
		$header = $html->find( 'header' );
		$month_header_row = $header->find( '.tribe-events-calendar-month__header-row' );
		$month_header_column = $month_header_row->find( '.tribe-events-calendar-month__header-column' );


		$this->assertTrue(
			$header->is( '[role="rowgroup"]' ),
			'Month calendar header needs to be role="rowgroup"'
		);

		$this->assertTrue(
			$month_header_row->is( '[role="row"]' ),
			'Month calendar header row needs to be role="row"'
		);

		$this->assertTrue(
			$month_header_column->is( '[role="columnheader"]' ),
			'Month calendar header column needs to be role="columnheader"'
		);

	}
}
