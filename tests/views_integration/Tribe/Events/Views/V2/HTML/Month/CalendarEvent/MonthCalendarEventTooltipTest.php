<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarEventTooltipTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$template = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/tooltip',
			[ 'event' => $event ]
		);
		$html     = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__calendar-event-tooltip' )->count(),
			1,
			'Month Calendar Event Tooltip HTML needs to contain one ".tribe-events-tooltip__content" element'
		);
	}

	public function it_should_contain_correct_html_attributes() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$template = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/tooltip',
			[ 'event' =>  $event ]
		);
		$html     = $this->document->html( $template );

		$tooltip = $html->find( '.tribe-events-calendar-month__calendar-event-tooltip' );

		$this->assertTrue(
			$tooltip->is( '[data-js="tribe-events-tooltip-content"]' ),
			'Month calendar tooltip needs to be data-js="tribe-events-tooltip-content"'
		);

		$this->assertTrue(
			$tooltip->is( '[role="tooltip"]' ),
			'Month calendar tooltip needs to be role="tooltip"'
		);
	}

}
