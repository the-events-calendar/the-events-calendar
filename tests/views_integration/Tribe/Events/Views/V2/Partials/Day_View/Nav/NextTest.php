<?php

namespace Tribe\Events\Views\V2\Partials\Day_View\Nav;

use Tribe\Events\Views\V2\Partials\TestCase;

class NextTest extends TestCase
{

	protected $partial_path = 'day/nav/next';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'link' => '#',
		] ) );
	}
}
