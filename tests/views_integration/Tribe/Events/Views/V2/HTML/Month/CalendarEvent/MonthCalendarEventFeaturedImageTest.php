<?php
namespace Tribe\Events\Views\V2\Views\HTML\Month\CalendarEvent;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthCalendarEventFeaturedImageTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event = $this->mock_event( 'events/featured/1.json' )->with_thumbnail()->get();
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
