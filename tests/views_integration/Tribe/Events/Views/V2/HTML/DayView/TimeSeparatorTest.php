<?php
namespace Tribe\Events\Views\V2\Views\HTML\DayView;

use Tribe\Events\Views\V2\TestHtmlCase;

class TimeSeparatorTest extends TestHtmlCase {


	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {

		$template = $this->template->template( 'day/time-separator' );
		$html = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-day__time-separator' )->count(),
			1,
			'Day View Time Separator HTML needs to contain one ".tribe-events-calendar-day__time-separator" element'
		);

	}

}
