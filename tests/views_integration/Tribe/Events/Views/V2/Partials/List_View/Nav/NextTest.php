<?php

namespace Tribe\Events\Views\V2\Partials\List_View\Nav;

use Tribe\Events\Views\V2\Partials\TestCase;

class NextTest extends TestCase
{

	protected $partial_path = 'list/nav/next';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'link' => '#',
		] ) );
	}
}
