<?php

namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarEventDateTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event    = static::factory()->event->create();
		$template = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/date',
			[ 'event' => tribe_get_event( $event ) ]
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
		$event_id         = static::factory()->event->create();
		$event            = tribe_get_event( $event_id );
		$event->featured  = true;
		$event->recurring = true;

		$template = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/date',
			[ 'event' => $event ]
		);
		$html     = $this->document->html( $template );

		$this->markTestSkipped( 'The month calendar event date event does not receive data yet' );

		$featured_icon = $html->find( '.tribe-events-calendar-month__calendar-event-datetime-featured' );
		$this->assertTrue(
			$featured_icon->is( '[aria-label="Featured"]' ),
			'Month calendar event featured icon needs to be aria-label="Featured"'
		);

		$this->assertTrue(
			$featured_icon->is( '[title="Featured"]' ),
			'Month calendar event featured icon needs to be title="Featured"'
		);

		$recurring_icon = $html->find( '.tribe-events-calendar-month__calendar-event-datetime-featured' );
		$this->assertTrue(
			$recurring_icon->is( '[aria-label="Recurring"]' ),
			'Month calendar event recurring icon needs to be aria-label="Recurring"'
		);

		$this->assertTrue(
			$recurring_icon->is( '[title="Featured"]' ),
			'Month calendar event recurring icon needs to be title="Recurring"'
		);
	}

}
