<?php
namespace Tribe\Events\Views\V2\Views\HTML\DayView;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class TimeSeparatorTest extends HtmlTestCase {
	use With_Post_Remapping;

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$event = $this->get_mock_event( 'events/single/1.json' );

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
