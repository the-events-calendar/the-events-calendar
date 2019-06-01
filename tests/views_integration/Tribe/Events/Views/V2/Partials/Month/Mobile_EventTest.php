<?php

namespace Tribe\Events\Views\V2\Partials\Month;

use Tribe\Events\Views\V2\Partials\TestCase;

class Mobile_EventTest extends TestCase
{

	protected $partial_path = 'month/mobile-event';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		tribe_events()->set_args( [
			'start_date' => '2018-01-01 10am',
			'timezone'   => 'Europe/Paris',
			'duration'   => 3 * HOUR_IN_SECONDS,
			'title'      => 'Test Event - 2018-01-01 10am',
			'status'     => 'publish',
		] )->create();

		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'event' => tribe_events()->first() ] ) );
	}

	/**
	 * Test render with non featured event
	 */
	public function test_render_with_non_featured_event() {
		/* @todo: complete once we have dynamic views */
		$this->markTestSkipped( 'Complete once we have dynamic views.' );
	}

	/**
	 * Test render with featured event
	 */
	public function test_render_with_featured_event() {
		/* @todo: complete once we have dynamic views */
		$this->markTestSkipped( 'Complete once we have dynamic views.' );
	}

	/**
	 * Test render with recurring event
	 */
	public function test_render_with_recurring_event() {
		/* @todo: complete once we have dynamic views */
		$this->markTestSkipped( 'Complete once we have dynamic views.' );
	}

	/**
	 * Test render with featured recurring event
	 */
	public function test_render_with_featured_recurring_event() {
		/* @todo: complete once we have dynamic views */
		$this->markTestSkipped( 'Complete once we have dynamic views.' );
	}
}
