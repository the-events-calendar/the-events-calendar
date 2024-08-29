<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Mobile_Events\Mobile_Day;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class More_EventsTest extends HtmlPartialTestCase {

	protected $partial_path = 'month/mobile-events/mobile-day/more-events';

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [ 'more_events' => 4, 'more_url' => 'http://theeventscalendar.com' ] ) );
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [] ) );
	}
}
