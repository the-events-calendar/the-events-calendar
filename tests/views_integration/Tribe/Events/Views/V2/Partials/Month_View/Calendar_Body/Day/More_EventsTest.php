<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Calendar_Body\Day;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class More_EventsTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-body/day/more-events';

	/**
	 * Test render without more events without more url
	 */
	public function test_render_without_more_events_without_more_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'more_events' => 0,
			'more_url'    => '',
		] ) );
	}

	/**
	 * Test render with more events without more url
	 */
	public function test_render_with_more_events_without_more_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'more_events' => 2,
			'more_url'    => '',
		] ) );
	}

	/**
	 * Test render without more events with more url
	 */
	public function test_render_without_more_events_with_more_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'more_events' => 0,
			'more_url'    => 'http://test.tri.be',
		] ) );
	}
	/**
	 * Test render with more events with more url
	 */
	public function test_render_with_more_events_with_more_url() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'more_events' => 2,
			'more_url'    => 'http://test.tri.be',
		] ) );
	}
}
