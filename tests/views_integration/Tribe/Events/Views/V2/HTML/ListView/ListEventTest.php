<?php
namespace Tribe\Events\Views\V2\Views\HTML\ListView;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class ListEventTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$event = $this->get_mock_event( 'events/single/1.json' );

		$template = $this->template->template( 'list/event', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list__event-row' )->count(),
			1,
			'List Event HTML needs to contain one ".tribe-events-calendar-list__event-row" element'
		);

		$this->assertFalse(
			$html->find( '.tribe-events-calendar-list__event-row' )->is( '.tribe-events-calendar-list__event-row--featured' ),
			'List Event HTML shouldnt contain ".tribe-events-calendar-list__event-row--featured" class if not featured'
		);

	}

	/**
	 * @test
	 */
	public function it_should_contain_featured_when_featured() {

		$event = $this->get_mock_event( 'events/featured/1.json' );

		$template = $this->template->template( 'list/event', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list__event-row--featured' )->count(),
			1,
			'List Event HTML needs to contain one ".tribe-events-calendar-list__event-row--featured" element when displaying a featured event'
		);

	}
}
