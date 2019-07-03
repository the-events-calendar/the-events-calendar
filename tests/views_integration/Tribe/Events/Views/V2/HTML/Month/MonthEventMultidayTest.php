<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthEventMultidayTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		// @todo: use the Event Factory here, once the templates use a real event and we have the real keys.
		$event = [
			'ID' => 0,
			'title' => 'Lorem Ipsum',
			'image' => 'test.jpg',
			'featured' => true,
			'multiday' => true,
			'start_date' => 1,
			'start_this_week' => true,
			'end_this_week' => true,
			'duration'      => 2
		];

		$template = $this->template->template( 'month/calendar-body/day/multiday-events/multiday-event', [ 'event' => (object) $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__multiday-event' )->count(),
			1,
			'Multiday HTML needs to contain one ".tribe-events-calendar-month__multiday-event" element'
		);


		$this->assertTrue(
			$html->find( '.tribe-events-calendar-month__multiday-event' )->children()->is( '.tribe-events-calendar-month__multiday-event-inner' ),
			'Multiday HTML needs to contain ".tribe-events-calendar-month__multiday-event-inner" element'
		);


		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__multiday-event-title' )->count(),
			1,
			'Multiday HTML needs to contain one ".tribe-events-calendar-month__multiday-event-title" element'
		);

	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {

		$event = [
			'ID' => 0,
			'title' => 'Lorem Ipsum',
			'image' => 'test.jpg',
			'featured' => true,
			'multiday' => true,
			'start_date' => 1,
			'start_this_week' => true,
			'end_this_week' => true,
			'duration'      => 2
		];

		$template = $this->template->template( 'month/calendar-body/day/multiday-events/multiday-event', [ 'event' => (object) $event ] );
		$html = $this->document->html( $template );
		$html = $html->find( '.tribe-events-calendar-month__multiday-event' );
		$icon = $html->find( '.tribe-events-calendar-month__multiday-event-featured-icon' );


		$this->markTestSkipped( 'The month multi-day event does not receive data yet' );

		/*
			@todo: If the event is featured we should check the following a11y classes for the icon
		*/

		$this->assertTrue(
			$icon->is( '[aria-label="Featured"]' ),
			'Month multiday featured icon needs to be aria-label="Featured"'
		);

		$this->assertTrue(
			$icon->is( '[title="Featured"]' ),
			'Month multiday featured icon needs to be title="Featured"'
		);
	}
}
