<?php
namespace Tribe\Events\Views\V2\Views\HTML\ListView\Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class ListEventDescriptionTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$event = $this->get_mock_event( 'events/single/1.json' );
		$event->excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';

		$template = $this->template->template( 'list/event/description', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list__event-description' )->count(),
			1,
			'List Event description HTML needs to contain one ".tribe-events-calendar-list__event-description" element'
		);

	}
}
