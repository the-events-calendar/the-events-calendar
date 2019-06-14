<?php
namespace Tribe\Events\Views\V2\Views\HTML\DayView\Event;

use Tribe\Events\Views\V2\TestHtmlCase;
use Tribe\Events\Test\Factories\Event;

class DayEventDateTest extends TestHtmlCase {

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
			'Day Event HTML date shouldnt to contain ".tribe-events-calendar-day__event-datetime-featured-icon" class if not featured'
		);

	}

	/**
	 * @test
	 */
	public function it_should_contain_featured_when_featured() {

		$args  = [
			'start_date' => '2018-01-01 09:00:00',
			'end_date'   => '2018-01-01 11:00:00',
			'timezone'   => 'Europe/Paris',
			'title'      => 'A featured test event',
			'featured'   => true,
		];

		$event = tribe_events()->set_args( $args )->create();

		$template = $this->template->template( 'day/event', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$featured_icon = $html->find( '.tribe-events-calendar-day__event-datetime-featured-icon' );

		$this->assertEquals(
			$featured_icon->count(),
			1,
			'Day Event date HTML needs to contain one ".tribe-events-calendar-day__event-datetime-featured-icon" element when having a featured event'
		);

		$this->assertTrue(
			$featured_icon->is( '[aria-label="Featured"]' ),
			'Day calendar event featured icon needs to be aria-label="Featured"'
		);

		$this->assertTrue(
			$featured_icon->is( '[title="Featured"]' ),
			'Day calendar event featured icon needs to be title="Featured"'
		);

	}

}
