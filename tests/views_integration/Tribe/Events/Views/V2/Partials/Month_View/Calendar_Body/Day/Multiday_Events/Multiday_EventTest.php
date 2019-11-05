<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day\Multiday_Events;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Multiday_EventTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/calendar-body/day/multiday-events/multiday-event';

	/**
	 * Test render with multiday event
	 */
	public function test_render_with_multiday_event() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'event'            => $event,
			'is_start_of_week' => false,
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with featured multiday event
	 */
	public function test_render_with_featured_multiday_event() {
		$event = $this->mock_event( 'events/featured/1.json' )->is_multiday( 5 )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'event'            => $event,
			'is_start_of_week' => false,
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiday event is start of week
	 */
	public function test_render_with_multiday_event_is_start_of_week() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$event->this_week_duration = 3;
		$event->ends_this_week = true;
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'event'            => $event,
			'is_start_of_week' => true,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiday event start date is day date
	 */
	public function test_render_with_multiday_event_start_date_is_day_date() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$event->this_week_duration = 3;
		$event->starts_this_week = true;
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-20',
			'event'            => $event,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiday event is start of week
	 */
	public function test_render_with_multiday_event_is_start_of_week_start_date_is_day_date() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$event->this_week_duration = 5;
		$event->starts_this_week = true;
		$event->ends_this_week = true;
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-20',
			'event'            => $event,
			'is_start_of_week' => true,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiday event starts this week spans multiple weeks
	 */
	public function test_render_with_multiday_event_starts_this_week_spans_multiple_weeks() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$event->this_week_duration = 3;
		$event->starts_this_week = true;
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-20',
			'event'            => $event,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiday event ends this week spans multiple weeks
	 */
	public function test_render_with_multiday_event_ends_this_week_spans_multiple_weeks() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$event->this_week_duration = 3;
		$event->ends_this_week = true;
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-22',
			'event'            => $event,
			'is_start_of_week' => true,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiday event starts this week ends this week
	 */
	public function test_render_with_multiday_event_starts_this_week_ends_this_week() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 5 )->get();
		$event->this_week_duration = 5;
		$event->starts_this_week = true;
		$event->ends_this_week = true;
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-20',
			'event'            => $event,
			'is_start_of_week' => false,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiday event does not start this week does not end this week spans multiple weeks
	 */
	public function test_render_with_multiday_event_does_not_start_this_week_does_not_end_this_week_spans_multiple_weeks() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 20 )->get();
		$event->this_week_duration = 7;
		$event->starts_this_week = false;
		$event->ends_this_week = false;
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-30',
			'event'            => $event,
			'is_start_of_week' => true,
			'today_date'       => '2019-06-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}

	/**
	 * Test render with multiday event is past
	 */
	public function test_render_with_multiday_event_is_past() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 11 )->get();
		$event->this_week_duration = 5;
		$event->starts_this_week = false;
		$event->ends_this_week = true;
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day_date'         => '2019-06-20',
			'event'            => $event,
			'is_start_of_week' => false,
			'today_date'       => '2019-07-01',
			'grid_start_date'  => '2019-01-01',
		] ) );
	}
}
