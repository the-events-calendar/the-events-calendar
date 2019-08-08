<?php
namespace Tribe\Events\Views\V2\Views\HTML\DayView;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class TimeSeparatorTest extends HtmlTestCase {


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


		$args = [
			'events' => [ $event ],
			'event' => $event,
		];

		$template = $this->template->template( 'day/time-separator', $args );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day__time-separator' )->count(),
			1,
			'Day View Time Separator HTML needs to contain one ".tribe-events-calendar-day__time-separator" element'
		);
	}
}
