<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Calendar_EventsTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/calendar-events';

	/**
	 * Test render with no events
	 */
	public function test_render_with_no_events() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_events' => [],
		] ) );
	}

	/**
	 * Test render with one event
	 */
	public function test_render_with_one_event() {
		$event_1 = $this->mock_event( 'events/single/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_events' => [ $event_1 ],
		] ) );
	}

	/**
	 * Test render with multiple events
	 */
	public function test_render_with_multiple_events() {
		$event_1 = $this->mock_event( 'events/featured/1.json' )->get();
		$event_2 = $this->mock_event( 'events/single/1.json' )->get();
		$event_3 = $this->mock_event( 'events/single/2.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_events' => [
				$event_1,
				$event_2,
				$event_3,
			],
		] ) );
	}
}
