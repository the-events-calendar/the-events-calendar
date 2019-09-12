<?php

namespace Tribe\Events\Views\V2\Partials\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class NavTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/nav';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'prev_url' => '#',
			'next_url' => '#',
			'location' => 'calendar',
		] ) );
	}
}
