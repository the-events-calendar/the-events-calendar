<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\Tooltip;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthTooltipCTATest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'month/calendar-body/day/calendar-events/calendar-event/tooltip/cta' );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-c-small-cta' )->count(),
			1,
			'Month Tooltip CTA HTML needs to contain one ".tribe-events-c-small-cta" element'
		);

		$this->assertTrue(
			$html->find( '.tribe-events-c-small-cta' )->children()->is( '.tribe-events-c-small-cta__link' ),
			'Month Tooltip CTA HTML needs to contain ".tribe-events-c-small-cta__link" element'
		);

		$this->assertTrue(
			$html->find( '.tribe-events-c-small-cta' )->children()->is( '.tribe-events-c-small-cta__price' ),
			'Month Tooltip CTA HTML needs to contain ".tribe-events-c-small-cta__price" element'
		);
	}

}
