<?php

namespace Tribe\Events\Views\V2\Partials\Month;

use Tribe\Events\Views\V2\Partials\TestCase;

class Mobile_EventsTest extends TestCase
{

	protected $partial_path = 'month/mobile-events';

	/**
	 * Test static render
	 */
	public function test_static_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
