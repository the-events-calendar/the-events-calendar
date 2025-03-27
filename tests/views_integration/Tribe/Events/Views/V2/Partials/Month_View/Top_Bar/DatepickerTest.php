<?php

namespace Tribe\Events\Views\V2\Partials\Month_View\Top_Bar;

use Tribe\Tests\Traits\With_Uopz;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use Tribe__Date_Utils as Dates;

class DatepickerTest extends HtmlPartialTestCase {
	use With_Uopz;

	protected $partial_path = 'month/top-bar/datepicker';

		public function setUp() {
		parent::setUp();
		// Always return the same value when creating nonces.
		$this->set_fn_return( 'wp_create_nonce', '2ab7cc6b39' );
	}

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {

		$date_formats = (object) [
			'month_and_year'         => 'F Y',
			'month_and_year_compact' => 'm/Y',
			'time_range_separator'   => ' - ',
			'date_time_separator'    => ' @ ',
			'compact'                => 'm/d/Y',
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'now'                        => '2018-01-01 12:00:00',
			'today_url'                  => 'http://test.tri.be',
			'today_label'                => 'Today',
			'today_title'                => 'Click to select today\'s date',
			'is_now'                     => true,
			'show_now'                   => true,
			'formatted_grid_date'        => 'January 2018',
			'formatted_grid_date_mobile' => '1/2018',
			'the_date'                   => Dates::build_date_object( '2018-01' ),
			'date_formats'               => $date_formats,
			'past'                       => false,
		] ) );

	}

	/**
	 * Test render with context for past
	 */
	public function test_render_with_context_past() {

		$date_formats = (object) [
			'month_and_year'         => 'F Y',
			'month_and_year_compact' => 'm/Y',
			'time_range_separator'   => ' - ',
			'date_time_separator'    => ' @ ',
			'compact'                => 'm/d/Y',
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'now'                        => '2018-01-01 12:00:00',
			'today_url'                  => 'http://test.tri.be',
			'today_label'                => 'Today',
			'today_title'                => 'Click to select today\'s date',
			'is_now'                     => true,
			'show_now'                   => true,
			'formatted_grid_date'        => 'January 2018',
			'formatted_grid_date_mobile' => '1/2018',
			'the_date'                   => Dates::build_date_object( '2018-01' ),
			'date_formats'               => $date_formats,
			'past'                       => true,
		] ) );

	}

	public function tearDown(){
		$this->unset_uopz_returns();
		parent::tearDown();
	}
}
