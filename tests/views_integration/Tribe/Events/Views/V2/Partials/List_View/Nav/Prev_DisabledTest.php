<?php

namespace Tribe\Events\Views\V2\Partials\List_View\Nav;

use Tribe\Events\Views\V2\Partials\TestCase;

class Prev_DisabledTest extends TestCase
{

	protected $partial_path = 'list/nav/prev-disabled';

	/**
	 * Test static render
	 */
	public function test_static_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
