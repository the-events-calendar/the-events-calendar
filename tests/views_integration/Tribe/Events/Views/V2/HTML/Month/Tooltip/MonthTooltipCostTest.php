<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\Tooltip;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthTooltipCostTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$event = $this->get_mock_event( 'events/single/1.json' );
		$event->cost = '$10';
		$template = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/tooltip/cost',
			[ 'event' => $event ]
		);
		$html     = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-c-small-cta' )->count(),
			1,
			'Month Tooltip CTA HTML needs to contain one ".tribe-events-c-small-cta" element'
		);

		$this->assertTrue(
			$html->find( '.tribe-events-c-small-cta' )->children()->is( '.tribe-events-c-small-cta__price' ),
			'Month Tooltip CTA HTML needs to contain ".tribe-events-c-small-cta__price" element'
		);
	}

}
