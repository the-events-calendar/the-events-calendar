<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar\Search_Button;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class IconTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/search-button/icon';

	/**
	 * Test render
	 */
	public function test_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
