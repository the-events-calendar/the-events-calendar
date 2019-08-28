<?php

namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarEventTitleTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event_id = static::factory()->event->create();
		$event    = tribe_get_event( $event_id );
		$template = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/title',
			[ 'event' => $event ]
		);
		$html     = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__calendar-event-title' )->count(),
			1,
			'Month Calendar Event Title HTML needs to contain one ".tribe-events-calendar-month__calendar-event-title" element'
		);
	}

}
