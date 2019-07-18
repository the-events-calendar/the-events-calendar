<?php
namespace Tribe\Events\Views\V2\Views\HTML;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'month' );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month' )->count(),
			1,
			'Month HTML needs to contain one ".tribe-events-calendar-month" element'
		);

		$this->assertTrue(
			$html->find( '.tribe-events-calendar-month' )->children()->is( '.tribe-events-calendar-month__body' ),
			'Month HTML needs to contain ".tribe-events-calendar-month__body" element'
		);
	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {
		$template = $this->template->template( 'month' );
		$html = $this->document->html( $template );
		$month = $html->find( '.tribe-events-calendar-month' );
		$month_body = $month->find( '.tribe-events-calendar-month__body' );
		$week = $month_body->find( '.tribe-events-calendar-month__week' );

		$this->assertTrue(
			$month->is( '[role="grid"]' ),
			'Month needs to be role="grid"'
		);

		$this->assertTrue(
			$month->is( '[aria-readonly="true"]' ),
			'Month needs to be aria-readonly="true"'
		);

		$this->assertTrue(
			$week->is( '[role="row"]' ),
			'Month week needs to be role="row"'
		);

	}
}
