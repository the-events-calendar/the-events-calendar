<?php

namespace Tribe\Events\Views\V2\iCalendar;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;

/**
 * Regression coverage for snippets that hide the subscribe links by returning a non-array
 * from `tec_views_v2_subscribe_links`.
 *
 * A snippet registered from an mu-plugin or a theme runs before the link classes hook themselves at
 * priority 10, so the links appended to whatever it returned: a truthy scalar fataled the page with
 * `Cannot use a scalar value as an array`, and `false` was converted back into an array, putting the
 * links the snippet meant to hide back on the page.
 *
 * @see https://theeventscalendar.com/knowledgebase/remove-ical-and-google-calendar-links-from-single-event-views/
 */
class Subscribe_LinksTest extends WPTestCase {

	/**
	 * The non-array values a snippet can return from the filter.
	 *
	 * @since TBD
	 *
	 * @return array<string,array<mixed>> The data sets.
	 */
	public function non_array_returns() {
		return [
			'false'        => [ false ],
			'true'         => [ true ],
			'empty string' => [ '' ],
			'null'         => [ null ],
		];
	}

	/**
	 * A link must not append itself to a list an earlier callback replaced with a non-array.
	 *
	 * @since TBD
	 *
	 * @dataProvider non_array_returns
	 *
	 * @param mixed $value The value the snippet returned.
	 */
	public function test_link_returns_non_array_list_untouched( $value ) {
		$this->assertSame( $value, tribe( Google_Calendar::class )->filter_tec_views_v2_subscribe_links( $value ) );
	}

	/**
	 * Consumers are documented an array, whatever the last callback returned.
	 *
	 * @since TBD
	 *
	 * @dataProvider non_array_returns
	 *
	 * @param mixed $value The value the snippet returned.
	 */
	public function test_get_subscribe_links_returns_an_array( $value ) {
		$this->hide_links_with( $value, 100 );

		$this->assertSame( [], tribe( iCalendar_Handler::class )->get_subscribe_links() );
	}

	/**
	 * The reported fatal: a snippet running before the links broke the single event page.
	 *
	 * @since TBD
	 *
	 * @dataProvider non_array_returns
	 *
	 * @param mixed $value The value the snippet returned.
	 */
	public function test_single_event_links_does_not_fatal_when_a_snippet_runs_first( $value ) {
		$this->hide_links_with( $value, 5 );

		$this->assertSame( '', tribe( iCalendar_Handler::class )->single_event_links( 'original links' ) );
	}

	/**
	 * Hooks a snippet that replaces the link list with a non-array value.
	 *
	 * @since TBD
	 *
	 * @param mixed $value    The value the snippet returns.
	 * @param int   $priority The priority the snippet hooks at.
	 *
	 * @return void
	 */
	protected function hide_links_with( $value, $priority ) {
		add_filter(
			'tec_views_v2_subscribe_links',
			static function () use ( $value ) {
				return $value;
			},
			$priority
		);
	}
}
