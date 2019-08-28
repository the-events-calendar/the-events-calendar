<?php

namespace Tribe\Events\Views\V2\Partials\Month;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Mobile_EventsTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/mobile-events';

	/**
	 * Test static render
	 */
	public function test_static_render() {
		// @todo @fe complete this test with dynamic content.
		$this->markTestSkipped('Not complete yet!');
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}
}
