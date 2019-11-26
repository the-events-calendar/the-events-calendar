<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Calendar_Events\Calendar_Event;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DateTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/date';

	/**
	 * Test render with event
	 */
	public function test_render_with_event() {
		$date_formats = (object) [
			'month_and_year'       => 'F Y',
			'time_range_separator' => ' - ',
			'date_time_separator'  => ' @ ',
		];
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event, 'date_formats' => $date_formats ] ) );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$date_formats = (object) [
			'month_and_year'       => 'F Y',
			'time_range_separator' => ' - ',
			'date_time_separator'  => ' @ ',
		];
		$event = $this->get_mock_event( 'events/featured/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event, 'date_formats' => $date_formats ] ) );
	}
}
