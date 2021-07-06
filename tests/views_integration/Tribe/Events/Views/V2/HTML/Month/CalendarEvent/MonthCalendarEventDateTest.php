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

		$this->assertTrue(
			$featured_icon->is( '[title="Featured"]' ),
			'Month calendar event featured icon needs to be title="Featured"'
		);

		$this->assertNotEmpty(
			$featured_icon->find( 'title' ),
			'Month multiday featured icon needs to be contain a title element.'
		);

		$this->assertStringContainsStringIgnoringCase(
			$featured_icon->find( 'title' )->text(),
			'featured',
			'Month multiday featured icon title element should contain "featured" by default.'
		);
	}

}
