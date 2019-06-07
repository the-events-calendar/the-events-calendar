<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Events\Views\V2\TestHtmlCase;

class MonthCalendarEventTitleTest extends TestHtmlCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		// @todo: use the Event Factory here, once the templates use a real event and we have the real keys.
		$event = [ 'ID' => 0, 'title' => 'Lorem Ipsum', 'image' => 'test.jpg', 'featured' => true, 'recurring' => true ];
		$template = $this->template->template( 'month/calendar-event/title', [ 'event' => (object) $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__calendar-event-title' )->count(),
			1,
			'Month Calendar Event Title HTML needs to contain one ".tribe-events-calendar-month__calendar-event-title" element'
		);
	}

}
