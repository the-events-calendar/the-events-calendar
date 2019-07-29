<?php

namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarEventFeaturedImageTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$thumbnail_id = static::factory()->attachment->create_upload_object(
			codecept_data_dir( 'images/featured-image.jpg' )
		);
		$event_id     = static::factory()->event->create(
			[
				'meta_input' => [
					'_thumbnail_id' => $thumbnail_id,
					\Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => true,
				]
			]
		);
		$event        = tribe_get_event( $event_id );
		$template     = $this->template->template(
			'month/calendar-body/day/calendar-events/calendar-event/featured-image',
			[ 'event' => $event ]
		);

		$html         = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month__calendar-event-featured-image-wrapper' )->count(),
			1,
			'Month Calendar Event Featured image HTML needs to contain one ".tribe-events-calendar-month__calendar-event-featured-image-wrapper" element'
		);
	}

}
