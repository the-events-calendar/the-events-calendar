<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthDayTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$this->markTestSkipped( 'The month calendar event day event does not have the final data yet' );

		$template = $this->template->template( 'month/calendar-body/day', [ 'day' => 1, 'week' => 1 ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__day' )->count(),
			1,
			'Month Day HTML needs to contain one ".tribe-events-calendar-month__day" element'
		);

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__day-date' )->count(),
			1,
			'Month Day HTML needs to contain one ".tribe-events-calendar-month__day-date" element'
		);
	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {

		$this->markTestSkipped( 'The month calendar event day event does not have the final data yet' );

		$template = $this->template->template( 'month/calendar-body/day', [ 'day' => 1, 'week' => 1 ] );
		$html = $this->document->html( $template );
		$day = $html->find( '.tribe-events-calendar-month__day' );


		$this->assertTrue(
			$day->is( '[role="gridcell"]' ),
			'Month Day needs to be role="gridcell"'
		);

	}
}
