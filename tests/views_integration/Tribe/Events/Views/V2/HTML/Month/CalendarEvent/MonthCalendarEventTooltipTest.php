<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Events\Views\V2\TestHtmlCase;

class MonthCalendarEventTooltipTest extends TestHtmlCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event = [ 'ID' => 0, 'title' => 'Lorem Ipsum', 'image' => 'test.jpg', 'featured' => true, 'recurring' => true ];
		$template = $this->template->template( 'month/calendar-event/tooltip', [ 'event' => (object) $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-tooltip__content' )->count(),
			1,
			'Month Calendar Event Tooltip HTML needs to contain one ".tribe-events-tooltip__content" element'
		);
	}

}
