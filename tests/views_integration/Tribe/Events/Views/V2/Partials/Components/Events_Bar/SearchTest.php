<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class SearchTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/search';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'url' => '#' ] ) );
	}
}
