<?php

namespace Tribe\Events\Views\V2\iCalendar;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;

/**
 * Regression coverage for snippets that hide the subscribe links by returning a non-array
 * from `tec_views_v2_subscribe_links`.
 *
 * @see https://theeventscalendar.com/knowledgebase/remove-ical-and-google-calendar-links-from-single-event-views/
 */
class Subscribe_LinksTest extends WPTestCase {

	/**
	 * @return iCalendar_Handler
	 */
	protected function handler() {
		return tribe( iCalendar_Handler::class );
	}

	public function test_get_subscribe_links_normalizes_non_array_filter_return() {
		add_filter( 'tec_views_v2_subscribe_links', '__return_false', 100 );

		$this->assertSame( [], $this->handler()->get_subscribe_links() );
	}

	/**
	 * The exact fatal reported: `count(): Argument #1 ($value) must be of type Countable|array, bool given`.
	 */
	public function test_single_event_links_does_not_fatal_when_filter_returns_false() {
		add_filter( 'tec_views_v2_subscribe_links', '__return_false', 100 );

		$this->assertSame( '', $this->handler()->single_event_links( 'original links' ) );
	}

	/**
	 * A snippet running before the links hook themselves must not make them append to a non-array.
	 */
	public function test_link_does_not_append_to_non_array_list() {
		add_filter( 'tec_views_v2_subscribe_links', '__return_false', 5 );

		$this->assertFalse( tribe( Google_Calendar::class )->filter_tec_views_v2_subscribe_links( false ) );
		$this->assertSame( [], $this->handler()->get_subscribe_links() );
	}
}
