<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarEventDateTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event = $this->get_mock_event( 'events/single/1.json' );

		$template = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/date',
			[ 'event' => $event ]
		);
		$html     = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__calendar-event-datetime' )->count(),
			1,
			'Month Calendar Event Date HTML needs to contain one ".tribe-events-calendar-month__calendar-event-datetime" element'
		);
	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {
		$event = $this->mock_event( 'events/featured/1.json' )->is_recurring()->get();

		$template = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/date',
			[ 'event' => $event ]
		);
		$html     = $this->document->html( $template );

		$featured_icon = $html->find( '.tribe-events-calendar-month__calendar-event-datetime-featured-icon' );

		$this->assertEquals(
			$featured_icon->count(),
			1,
			'Month Calendar Event Date HTML needs to contain one ".tribe-events-calendar-month__calendar-event-datetime-featured-icon" element when displaying a featured event'
		);

		$featured_text_element = $html->find( '.tribe-events-calendar-month__calendar-event-datetime-featured-icon + .tribe-common-a11y-visual-hide' );

		$this->assertNotEmpty(
			$featured_text_element,
			'Month multiday featured icon needs to have an adjacent screen reader-only element.'
		);
	}

}
