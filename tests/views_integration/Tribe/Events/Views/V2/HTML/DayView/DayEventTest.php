<?php
namespace Tribe\Events\Views\V2\Views\HTML\DayView;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class DayEventTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$event = $this->get_mock_event( 'events/single/1.json' );

		$template = $this->template->template( 'day/event', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day__event' )->count(),
			1,
			'Day Event HTML needs to contain one ".tribe-events-calendar-day__event" element'
		);

		$this->assertFalse(
			$html->find( '.tribe-events-calendar-day__event' )->is( '.tribe-events-calendar-day__event--featured' ),
			'Day Event HTML shouldnt contain ".tribe-events-calendar-day__event--featured" class if not featured'
		);

	}

	/**
	 * @test
	 */
	public function it_should_contain_featured_when_featured() {

		$event = $this->get_mock_event( 'events/featured/1.json' );

		$template = $this->template->template( 'day/event', [ 'event' => $event ] );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day__event--featured' )->count(),
			1,
			'Day Event HTML needs to contain one ".tribe-events-calendar-day__event--featured" element when displaying a featured event'
		);

	}
}
