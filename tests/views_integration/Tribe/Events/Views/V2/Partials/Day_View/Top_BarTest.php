<?php

namespace Tribe\Events\Views\V2\Partials\Day_View;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use tad\FunctionMocker\FunctionMocker as Test;

class Top_BarTest extends HtmlPartialTestCase
{

	protected $partial_path = 'day/top-bar';

	public function setUp() {
		parent::setUp();
		// Start Function Mocker.
		Test::setUp();
		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );
	}

	/**
	 * Test render with context
	 */
	public function test_render_with_context() {

		$date_formats = (object) [
			'month_and_year'       => 'F Y',
			'time_range_separator' => ' - ',
			'date_time_separator'  => ' @ ',
			'compact'              => 'm/d/Y',
		];

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'now'          => '2018-01-01 12:00:00',
			'today_url'    => 'http://test.tri.be',
			'date_formats' => $date_formats,
			'prev_rel'     => 'noindex',
			'next_rel'     => 'noindex',
		] ) );
	}

	public function tearDown(){
		Test::tearDown();
		parent::tearDown();
	}
}
