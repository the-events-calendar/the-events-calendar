<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Top_Bar;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DatepickerTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/top-bar/datepicker';

	/**
	 * Test render
	 */
	public function test_render() {
		$this->markTestSkipped( 'The "today" variable is not working as expected' );

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today' => '2018-01-01',
		] ) );
	}

	/**
	 * Test render with date
	 */
	public function test_render_with_date() {
		$this->markTestSkipped( 'The "today" variable is not working as expected' );

		add_filter( 'tribe_events_template_var', function( $value, $key, $default, $view_slug ) {
			if ( 'bar-date' === implode( '-', $key ) ) {
				return '2018-06-01';
			}

			return $value;
		}, 10, 4 );

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today' => '2018-01-01',
		] ) );
	}
}
