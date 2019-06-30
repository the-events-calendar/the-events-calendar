<?php
namespace Tribe\Events\Views\V2\Views\HTML\DayView\Event;

use Tribe\Events\Views\V2\TestHtmlCase;
use Tribe\Events\Test\Factories\Event;

class DayEventTitleTest extends TestHtmlCase {

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
