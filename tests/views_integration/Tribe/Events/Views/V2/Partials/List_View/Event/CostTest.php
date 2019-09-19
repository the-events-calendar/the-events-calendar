<?php

namespace Tribe\Events\Views\V2\Partials\List_View\Event;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class CostTest extends HtmlPartialTestCase
{

	protected $partial_path = 'list/event/cost';

	/**
	 * Test render with cost
	 */
	public function test_render_with_cost() {
		$event = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
				'cost'       => '$25',
			]
		)->create();
		$event = tribe_get_event( $event );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

	/**
	 * Test render without cost
	 */
	public function test_render_without_cost() {
		$event = tribe_events()->set_args(
			[
				'start_date' => '2018-01-01 10am',
				'timezone'   => 'Europe/Paris',
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => 'Test Event - 2018-01-01 10am',
				'status'     => 'publish',
				'recurring'  => true,
			]
		)->create();
		$event = tribe_get_event( $event );

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => $event ] ) );
	}

}
