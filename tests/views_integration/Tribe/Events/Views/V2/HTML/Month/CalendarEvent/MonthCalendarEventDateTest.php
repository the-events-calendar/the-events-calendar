<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Events\Views\V2\TestHtmlCase;

class MonthCalendarEventDateTest extends TestHtmlCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$template = $this->template->template( 'month/calendar-event/date', 'event' => (object) [ 'ID' => 0 ] );
		$html = $this->document->html( $template );

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
		$template = $this->template->template( 'month/calendar-event/date', 'event' => (object) [ 'ID' => 0 ] );
		$html = $this->document->html( $template );

		$this->markTestSkipped( 'The month calendar event date event does not receive data yet' );

		/*
			@todo: If the event is featured we should check the following a11y classes for the icon
		*/
		$featured_icon = $html->find( '.tribe-events-calendar-month__calendar-event-datetime-featured' );
		$this->assertTrue(
			$featured_icon->is( '[aria-label="Featured"]' ),
			'Month calendar event featured icon needs to be aria-label="Featured"'
		);

		$this->assertTrue(
			$featured_icon->is( '[title="Featured"]' ),
			'Month calendar event featured icon needs to be title="Featured"'
		);

		/*
			@todo: If the event is recurring we should check the following a11y classes for the icon
		*/
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
