<?php

namespace Tribe\Events\Views\V2\Partials\Month\Calendar_Body\Day;

use Tribe\Events\Views\V2\Partials\TestCase;

class Multiday_EventsTest extends TestCase
{

	protected $partial_path = 'month/calendar-body/day/multiday-events';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'day'   => 0,
			'month' => [],
		] ) );
	}
}
