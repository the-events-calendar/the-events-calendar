<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class BreakpointsTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/breakpoints';

	/**
	 * Test render on initial load.
	 */
	public function test_render_on_initial_load() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'is_initial_load' => true,
			'breakpoint_pointer' => 'random-id',
		] ) );
	}

	/**
	 * Test render not initial load.
	 */
	public function test_render_not_initial_load() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'is_initial_load' => false,
			'breakpoint_pointer' => 'random-id',
		] ) );
	}
}
