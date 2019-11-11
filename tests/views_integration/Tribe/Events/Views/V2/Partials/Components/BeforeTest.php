<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class BeforeTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/before';

	public function test_render_with_text() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'before_events' => 'With some <strong>Before Events</strong>!' ] ) );
	}

	public function test_render_without_text() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'before_events' => null ] ) );
	}
}
