<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Events\Views\V2\TestHtmlCase;

class MonthCalendarEventTitleTest extends TestHtmlCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'month/calendar-event/title', 'event' => (object) [ 'ID' => 0 ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__calendar-event-title' )->count(),
			1,
			'Month Calendar Event Title HTML needs to contain one ".tribe-events-calendar-month__calendar-event-title" element'
		);
	}

}
