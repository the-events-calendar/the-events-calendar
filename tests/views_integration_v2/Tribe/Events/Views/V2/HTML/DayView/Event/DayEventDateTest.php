<?php
namespace Tribe\Events\Views\V2\Views\HTML\DayView\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class DayEventDateTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$event = $this->get_mock_event( 'events/single/1.json' );

		$template = $this->template->template( 'day/event/date', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day__event-datetime-wrapper' )->count(),
			1,
			'Day Event date HTML needs to contain one ".tribe-events-calendar-day__event-datetime-wrapper" element'
		);

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day__event-datetime-featured-icon' )->count(),
			0,
			'Day Event HTML date should not contain ".tribe-events-calendar-day__event-datetime-featured-icon" class if not featured'
		);

	}

	/**
	 * @test
	 */
	public function it_should_contain_featured_when_featured() {

		$event = $this->get_mock_event( 'events/featured/1.json' );

		$template = $this->template->template( 'day/event', [ 'event' => $event ] );

		$html = $this->document->html( $template );

		$featured_icon = $html->find( '.tribe-events-calendar-day__event-datetime-featured-icon' );

		$this->assertEquals(
			$featured_icon->count(),
			1,
			'Day Event date HTML needs to contain one ".tribe-events-calendar-day__event-datetime-featured-icon" element when having a featured event'
		);

		$this->assertTrue(
			$featured_icon->is( '[title="Featured"]' ),
			'Day calendar event featured icon needs to be title="Featured"'
		);

		$this->assertNotEmpty(
			$featured_icon->find( 'title' ),
			'Day calendar event featured icon needs to be contain a title element.'
		);

		$this->assertStringContainsStringIgnoringCase(
			$featured_icon->find('title')->text(),
			'featured',
			'Day calendar event featured icon title element should contain "featured" by default.'
		);
	}
}
