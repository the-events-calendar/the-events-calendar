<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Events\Views\V2\TestHtmlCase;

class MonthCalendarEventFeaturedImageTest extends TestHtmlCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		// @todo: Re-check how we send the `image` once we make this dynamic
		$template = $this->template->template( 'month/calendar-event/featured-image', [ 'event' => (object) [ 'ID' => 0, 'image' => 'test.jpg' ] ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__calendar-event-featured-image-wrapper' )->count(),
			1,
			'Month Calendar Event Featured image HTML needs to contain one ".tribe-events-calendar-month__calendar-event-featured-image-wrapper" element'
		);
	}

}
