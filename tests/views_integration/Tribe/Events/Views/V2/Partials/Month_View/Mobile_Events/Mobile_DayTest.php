<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Mobile_Events;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Mobile_DayTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/mobile-events/mobile-day';

	/**
	 * Test render with no events today date equals day date
	 */
	public function test_render_with_no_events_today_date_equals_day_date() {
		$context = [
			'today_date'       => '2018-06-01',
			'day_date'         => '2018-06-01',
			'day'              => [
				'year_number'  => '2018',
				'month_number' => '06',
				'day_number'   => '01',
				'day_url'      => 'http://test.tri.be/events/2018-06-01/',
				'events'       => [],
				'more_events'  => 0,
			],
			'mobile_messages' => [ 'notice' => [ 'raccoons' => 'Too many raccoons on this day' ] ],
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render with no events today date not day date
	 */
	public function test_render_with_no_events_today_date_not_day_date() {
		$context = [
			'today_date'       => '2018-06-01',
			'day_date'         => '2018-06-02',
			'day'              => [
				'year_number'  => '2018',
				'month_number' => '06',
				'day_number'   => '02',
				'day_url'      => 'http://test.tri.be/events/2018-06-02/',
				'events'       => [],
				'more_events'  => 0,
			],
			'mobile_messages' => [ 'notice' => [ 'raccoons' => 'Too many raccoons on this day' ] ],
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render with one event
	 */
	public function test_render_with_one_event() {
		$event = $this->get_mock_event( 'events/single/1.json' );
		$context = [
			'today_date'       => '2018-06-01',
			'day_date'         => '2018-06-20',
			'day'              => [
				'year_number'  => '2018',
				'month_number' => '06',
				'day_number'   => '20',
				'day_url'      => 'http://test.tri.be/events/2018-06-20/',
				'events'       => [ $event ],
				'more_events'  => 0,
			],
			'mobile_messages' => [ 'notice' => [ 'raccoons' => 'Too many raccoons on this day' ] ],
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render with multiple events
	 */
	public function test_render_with_multiple_events() {
		$event_1 = $this->get_mock_event( 'events/single/1.json' );
		$event_2 = $this->get_mock_event( 'events/single/2.json' );
		$context = [
			'today_date'       => '2018-06-01',
			'day_date'         => '2018-06-20',
			'day'              => [
				'year_number'  => '2018',
				'month_number' => '06',
				'day_number'   => '20',
				'day_url'      => 'http://test.tri.be/events/2018-06-20/',
				'events'       => [
					$event_1,
					$event_2,
				],
				'more_events'  => 0,
			],
			'mobile_messages' => [ 'notice' => [ 'raccoons' => 'Too many raccoons on this day' ] ],
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render with one multiday event
	 */
	public function test_render_with_one_multiday_event() {
		$event = $this->mock_event( 'events/single/1.json' )->is_multiday( 3 )->get();
		$event->this_week_duration = 3;
		$event->starts_this_week = true;
		$context = [
			'today_date'       => '2018-06-01',
			'day_date'         => '2018-06-20',
			'day'              => [
				'year_number'     => '2018',
				'month_number'    => '06',
				'day_number'      => '20',
				'day_url'         => 'http://test.tri.be/events/2018-06-20/',
				'events'          => [],
				'multiday_events' => [ $event ],
				'more_events'     => 0,
			],
			'mobile_messages' => [ 'notice' => [ 'raccoons' => 'Too many raccoons on this day' ] ],
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render with multiple multiday events
	 */
	public function test_render_with_multiple_multiday_events() {
		$event_1 = $this->mock_event( 'events/single/1.json' )->is_multiday( 3 )->get();
		$event_1->this_week_duration = 3;
		$event_1->starts_this_week = true;
		$event_2 = $this->mock_event( 'events/single/2.json' )->is_multiday( 3 )->get();
		$event_2->this_week_duration = 3;
		$event_2->starts_this_week = true;
		$context = [
			'today_date'       => '2018-06-01',
			'day_date'         => '2018-06-20',
			'day'              => [
				'year_number'     => '2018',
				'month_number'    => '06',
				'day_number'      => '20',
				'day_url'         => 'http://test.tri.be/events/2018-06-20/',
				'events'          => [],
				'multiday_events' => [
					$event_1,
					$event_2,
				],
				'more_events'     => 0,
			],
			'mobile_messages' => [ 'notice' => [ 'raccoons' => 'Too many raccoons on this day' ] ],
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	/**
	 * Test render with normal and multiday events
	 */
	public function test_render_with_normal_and_multiday_events() {
		$event_1 = $this->get_mock_event( 'events/single/1.json' );
		$event_2 = $this->mock_event( 'events/single/2.json' )->is_multiday( 3 )->get();
		$event_2->this_week_duration = 3;
		$event_2->starts_this_week = true;
		$context = [
			'today_date'       => '2018-06-01',
			'day_date'         => '2018-06-20',
			'day'              => [
				'year_number'     => '2018',
				'month_number'    => '06',
				'day_number'      => '20',
				'day_url'         => 'http://test.tri.be/events/2018-06-20/',
				'events'          => [ $event_1 ],
				'multiday_events' => [ $event_2 ],
				'more_events'     => 0,
			],
			'mobile_messages' => [ 'notice' => [ 'raccoons' => 'Too many raccoons on this day' ] ],
		];
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}
}
