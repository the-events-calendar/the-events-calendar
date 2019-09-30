<?php
namespace Tribe\Events\Views\V2\Views\HTML\DayView\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class DayEventTitleTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$event = $this->get_mock_event( 'events/single/1.json' );

		$template = $this->template->template( 'day/event/title', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day__event-title' )->count(),
			1,
			'Day Event title HTML needs to contain one ".tribe-events-calendar-day__event-title" element'
		);

		$this->assertTrue(
			$html->find( '.tribe-events-calendar-day__event-title' )->children()->is( '.tribe-events-calendar-day__event-title-link' ),
			'Day Event title HTML needs to contain ".tribe-events-calendar-day__event-title-link" element'
		);

		$link = $html->find( '.tribe-events-calendar-day__event-title-link' );

		$this->assertTrue(
			$link->is( '[rel="bookmark"]' ),
			'Day Event title link HTML needs to be rel="bookmark"'
		);

	}
}
