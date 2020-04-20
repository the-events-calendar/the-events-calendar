<?php

namespace Tribe\Events\Views\V2\Partials\Latest_Past_View\Event\Date;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class MetaTest extends HtmlPartialTestCase
{

	protected $partial_path = 'latest-past/event/date/meta';

	/**
	 * Test render
	 */
	public function test_render() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
