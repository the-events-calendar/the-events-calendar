<?php
namespace Tribe\Events\Views\V2\Views\HTML\ListView\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class ListEventDateTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$event = $this->get_mock_event( 'events/single/1.json' );

		$template = $this->template->template( 'list/event/date', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list__event-datetime-wrapper' )->count(),
			1,
			'List Event date HTML needs to contain one ".tribe-events-calendar-list__event-datetime-wrapper" element'
		);

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list__event-datetime-featured-icon' )->count(),
			0,
			'List Event HTML date should not contain ".tribe-events-calendar-list__event-datetime-featured-icon" class if not featured'
		);

	}

	/**
	 * @testListEventDateTest
	 */
	public function it_should_contain_featured_when_featured() {

		$event = $this->get_mock_event( 'events/featured/1.json' );

		$template = $this->template->template( 'list/event', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$featured_icon = $html->find( '.tribe-events-calendar-list__event-datetime-featured-icon' );

		$this->assertEquals(
			$featured_icon->count(),
			1,
			'List Event date HTML needs to contain one ".tribe-events-calendar-list__event-datetime-featured-icon" element when displaying a featured event'
		);

		$featured_text_element = $html->find( '.tribe-events-calendar-list__event-datetime-featured-icon + .tribe-common-a11y-visual-hide' );

		$this->assertNotEmpty(
			$featured_text_element,
			'List event featured icon needs to have an adjacent screen reader-only element.'
		);
	}

}
