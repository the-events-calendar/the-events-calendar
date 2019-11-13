<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Calendar_Events\Calendar_Event\Tooltip;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DateTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events/calendar-event/tooltip/date';

	/**
	 * Test render with event
	 */
	public function test_render_with_event() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$event = $this->get_mock_event( 'events/featured/1.json' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with event same start/end time
	 */
	public function test_render_with_event_same_start_end_time() {
		$event_data = [
			'ID'                     => 23,
			'start_date'             => '2018-01-01',
			'start_date_hour'        => '13',
			'start_date_minutes'     => '00',
			'start_date_seconds'     => '00',
			'start_date_utc_hour'    => '17',
			'start_date_utc_minutes' => '00',
			'start_date_utc_seconds' => '00',
			'end_date'               => '2018-01-01',
			'end_date_hour'          => '13',
			'end_date_minutes'       => '00',
			'end_date_seconds'       => '00',
			'end_date_utc_hour'      => '17',
			'end_date_utc_minutes'   => '00',
			'end_date_utc_seconds'   => '00',
		];
		$event = $this->get_mock_event( 'events/single/2.template.json', $event_data );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
