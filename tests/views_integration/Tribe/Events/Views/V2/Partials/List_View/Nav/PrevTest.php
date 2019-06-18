<?php

namespace Tribe\Events\Views\V2\Partials\List_View\Nav;

use Tribe\Events\Views\V2\Partials\TestCase;

class PrevTest extends TestCase
{

	protected $partial_path = 'list/nav/prev';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'link' => '#',
		] ) );
	}
}
