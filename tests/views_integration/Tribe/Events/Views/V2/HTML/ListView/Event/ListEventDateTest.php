<?php
namespace Tribe\Events\Views\V2\Views\HTML\ListView\Event;

use Tribe\Events\Views\V2\TestHtmlCase;
use Tribe\Events\Test\Factories\Event;

class ListEventDateTest extends TestHtmlCase {

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
			'List Event HTML date shouldnt contain ".tribe-events-calendar-list__event-datetime-featured-icon" class if not featured'
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

		$template = $this->template->template( 'list/event', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$featured_icon = $html->find( '.tribe-events-calendar-list__event-datetime-featured-icon' );

		$this->assertEquals(
			$featured_icon->count(),
			1,
			'List Event date HTML needs to contain one ".tribe-events-calendar-list__event-datetime-featured-icon" element when having a featured event'
		);

		$this->assertTrue(
			$featured_icon->is( '[aria-label="Featured"]' ),
			'List event featured icon needs to be aria-label="Featured"'
		);

		$this->assertTrue(
			$featured_icon->is( '[title="Featured"]' ),
			'List event featured icon needs to be title="Featured"'
		);

	}

}
