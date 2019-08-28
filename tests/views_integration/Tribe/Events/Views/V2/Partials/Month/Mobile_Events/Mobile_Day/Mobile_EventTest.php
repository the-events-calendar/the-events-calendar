<?php

namespace Tribe\Events\Views\V2\Partials\Month\Mobile_Events\Mobile_Day;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Mobile_EventTest extends HtmlPartialTestCase {

	protected $partial_path = 'month/mobile-events/mobile-day/mobile-event';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$event = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => tribe_get_event( $event ) ] ) );
	}

	/**
	 * Test render with non featured event
	 */
	public function test_render_with_non_featured_event() {
		$event = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => tribe_get_event( $event ) ] ) );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		$event           = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();
		$event           = tribe_get_event( $event );
		$event->featured = true;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with recurring event
	 */
	public function test_render_with_recurring_event() {
		// @todo @fe test PRO features in PRO.
		$event            = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();
		$event            = tribe_get_event( $event );
		$event->recurring = true;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render with featured recurring event
	 */
	public function test_render_with_featured_recurring_event() {
		/* @todo @fe test PRO features in PRO */
		$event            = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
			]
		)->create();
		$event            = tribe_get_event( $event );
		$event->featured  = true;
		$event->recurring = true;

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}
}
