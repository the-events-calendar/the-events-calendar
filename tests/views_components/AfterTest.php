<?php

namespace Tribe\Events\Views\V2\Partials\Components;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class AfterTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/after';

	public function test_render_with_text() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'after_events' => 'With some <strong>After Events</strong>!' ] ) );
	}

	public function test_render_without_text() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'after_events' => null ] ) );
	}
}
