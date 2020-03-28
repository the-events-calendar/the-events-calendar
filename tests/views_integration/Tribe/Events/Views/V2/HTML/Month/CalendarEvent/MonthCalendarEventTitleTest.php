<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarEventTitleTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event = $this->get_mock_event( 'events/single/1.json' );
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
