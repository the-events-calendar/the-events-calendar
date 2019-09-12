<?php

namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarEventTooltipTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event_id = static::factory()->event->create();
		$event    = tribe_get_event( $event_id );
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
		$event_id = static::factory()->event->create();
		$event    = tribe_get_event( $event_id );
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
