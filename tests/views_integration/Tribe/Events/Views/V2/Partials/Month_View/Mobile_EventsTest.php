<?php

namespace Tribe\Events\Views\V2\Partials\Month_View;

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Mobile_EventsTest extends HtmlPartialTestCase
{
	use With_Post_Remapping;

	protected $partial_path = 'month/mobile-events';

	/**
	 * Test render with empty days
	 */
	public function test_render_with_empty_days() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'days'       => [],
			'today_url'  => '#',
			'prev_url'   => '#',
			'next_url'   => '#',
			'prev_label' => 'May',
			'next_label' => 'July',
		] ) );
	}

	/**
	 * Test render with days with no found events
	 */
	public function test_render_with_days_with_no_found_events() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'days'       => [
				'2018-06-01' => [
					'found_events' => 0,
				],
				'2018-06-02' => [
					'found_events' => 0,
				],
			],
			'today_url'  => '#',
			'prev_url'   => '#',
			'next_url'   => '#',
			'prev_label' => 'May',
			'next_label' => 'July',
		] ) );
	}

	/**
	 * Test render with days with found events
	 */
	public function test_render_with_days_with_found_events() {
		$event_1 = $this->mock_event( 'events/featured/1.json' )->get();
		$event_2 = $this->mock_event( 'events/single/1.json' )->get();
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'days'       => [
				'2018-06-20' => [
					'found_events'    => 2,
					'year_number'     => '2018',
					'month_number'    => '06',
					'day_number'      => '20',
					'events'          => [],
					'multiday_events' => [
						$event_1,
						$event_2,
					],
				],
			],
			'today_date'       => '2018-06-01',
			'today_url'        => '#',
			'prev_url'         => '#',
			'next_url'         => '#',
			'prev_label'       => 'May',
			'next_label'       => 'July',
		] ) );
	}
}
