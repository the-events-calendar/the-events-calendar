<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Multiday_EventsTest extends HtmlPartialTestCase {
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/multiday-events';

	/**
	 * Test render with no events
	 */
	public function test_render_with_no_events() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'multiday_events'  => [],
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with one multiday event
	 */
	public function test_render_with_one_multiday_event() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$event->this_week_duration = 5;
		$event->starts_this_week = true;
		$event->ends_this_week = true;

		$multiday_events = [ $event ];
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'multiday_events'  => $multiday_events,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiple multiday events
	 */
	public function test_render_with_multiple_multiday_events() {
		$event_1 = $this->mock_event( 'events/featured/1.json' )->is_multiday( 5 )->get();
		$event_1->this_week_duration = 5;
		$event_1->starts_this_week = true;
		$event_1->ends_this_week = true;

		$event_2 = $this->mock_event( 'events/single/1.json' )->is_multiday( 4 )->get();
		$event_2->this_week_duration = 4;
		$event_2->starts_this_week = true;
		$event_2->ends_this_week = true;

		$event_3 = $this->mock_event( 'events/single/2.json' )->is_multiday( 3 )->get();
		$event_3->this_week_duration = 3;
		$event_3->starts_this_week = true;
		$event_3->ends_this_week = true;

		$multiday_events = [
			$event_1,
			$event_2,
			$event_3,
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'multiday_events'  => $multiday_events,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with one multiday event and spacer
	 */
	public function test_render_with_one_multiday_event_and_spacer() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$event->this_week_duration = 5;
		$event->starts_this_week = true;
		$event->ends_this_week = true;

		$multiday_events = [ false, $event ];
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'multiday_events'  => $multiday_events,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}
	/**
	 * Test render with multiple multiday events and spacer before events
	 */
	public function test_render_with_multiple_multiday_events_and_spacer_before_events() {
		$event_1 = $this->mock_event( 'events/featured/1.json' )->is_multiday( 5 )->get();
		$event_1->this_week_duration = 5;
		$event_1->starts_this_week = true;
		$event_1->ends_this_week = true;

		$event_2 = $this->mock_event( 'events/single/1.json' )->is_multiday( 4 )->get();
		$event_2->this_week_duration = 4;
		$event_2->starts_this_week = true;
		$event_2->ends_this_week = true;

		$event_3 = $this->mock_event( 'events/single/2.json' )->is_multiday( 3 )->get();
		$event_3->this_week_duration = 3;
		$event_3->starts_this_week = true;
		$event_3->ends_this_week = true;

		$multiday_events = [
			false,
			$event_1,
			$event_2,
			$event_3,
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'multiday_events'  => $multiday_events,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiple multiday events and spacer between events
	 */
	public function test_render_with_multiple_multiday_events_and_spacer_between_events() {
		$event_1 = $this->mock_event( 'events/featured/1.json' )->is_multiday( 5 )->get();
		$event_1->this_week_duration = 5;
		$event_1->starts_this_week = true;
		$event_1->ends_this_week = true;

		$event_2 = $this->mock_event( 'events/single/1.json' )->is_multiday( 4 )->get();
		$event_2->this_week_duration = 4;
		$event_2->starts_this_week = true;
		$event_2->ends_this_week = true;

		$event_3 = $this->mock_event( 'events/single/2.json' )->is_multiday( 3 )->get();
		$event_3->this_week_duration = 3;
		$event_3->starts_this_week = true;
		$event_3->ends_this_week = true;

		$multiday_events = [
			$event_1,
			$event_2,
			false,
			$event_3,
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'multiday_events'  => $multiday_events,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiple multiday events and spacers before and between events
	 */
	public function test_render_with_multiple_multiday_events_and_spacers_before_and_between_events() {
		$event_1 = $this->mock_event( 'events/featured/1.json' )->is_multiday( 5 )->get();
		$event_1->this_week_duration = 5;
		$event_1->starts_this_week = true;
		$event_1->ends_this_week = true;

		$event_2 = $this->mock_event( 'events/single/1.json' )->is_multiday( 4 )->get();
		$event_2->this_week_duration = 4;
		$event_2->starts_this_week = true;
		$event_2->ends_this_week = true;

		$event_3 = $this->mock_event( 'events/single/2.json' )->is_multiday( 3 )->get();
		$event_3->this_week_duration = 3;
		$event_3->starts_this_week = true;
		$event_3->ends_this_week = true;

		$multiday_events = [
			false,
			$event_1,
			$event_2,
			false,
			$event_3,
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'multiday_events'  => $multiday_events,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}
}
