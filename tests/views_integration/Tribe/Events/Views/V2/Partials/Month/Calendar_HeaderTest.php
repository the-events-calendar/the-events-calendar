<?php

namespace Tribe\Events\Views\V2\Partials\Month;

use Tribe\Events\Views\V2\Partials\TestCase;

class Calendar_HeaderTest extends TestCase
{

	protected $partial_path = 'month/calendar-header';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
