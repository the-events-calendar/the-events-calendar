<?php
namespace Tribe\Events\Views\V2\Views\HTML\ListView\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class ListEventTitleTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$event = $this->get_mock_event( 'events/single/1.json' );

		$template = $this->template->template( 'list/event/title', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list__event-title' )->count(),
			1,
			'List Event title HTML needs to contain one ".tribe-events-calendar-list__event-title" element'
		);

		$this->assertTrue(
			$html->find( '.tribe-events-calendar-list__event-title' )->children()->is( '.tribe-events-calendar-list__event-title-link' ),
			'List Event title HTML needs to contain ".tribe-events-calendar-list__event-title-link" element'
		);

		$link = $html->find( '.tribe-events-calendar-list__event-title-link' );

		$this->assertTrue(
			$link->is( '[rel="bookmark"]' ),
			'List Event title link HTML needs to be rel="bookmark"'
		);

	}
}
