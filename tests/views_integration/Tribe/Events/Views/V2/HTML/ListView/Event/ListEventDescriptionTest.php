<?php
namespace Tribe\Events\Views\V2\Views\HTML\ListView\Event;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;
use Tribe\Events\Test\Factories\Event;

class ListEventDescriptionTest extends HtmlTestCase {

	public function setUp() {
		parent::setUp();
		$this->factory()->event = new Event();
	}

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$args  = [
			'start_date' => '2018-01-01 09:00:00',
			'end_date'   => '2018-01-01 11:00:00',
			'timezone'   => 'Europe/Paris',
			'title'      => 'A test event',
		];

		$event = tribe_events()->set_args( $args )->create();

		$template = $this->template->template( 'list/event/description', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-list__event-description' )->count(),
			1,
			'List Event description HTML needs to contain one ".tribe-events-calendar-list__event-description" element'
		);

	}
}
