<?php

namespace Tribe\Events\Views\V2\Partials\Recent_Past_View;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use tad\FunctionMocker\FunctionMocker as Test;

class Top_BarTest extends HtmlPartialTestCase
{

	protected $partial_path = 'recent-past/top-bar';

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

		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'today_url'         => 'http://test.tri.be',
			'today_datetime'    => '2018-01-01',
			'today_date_mobile' => '2018-01-01',
			'today_date'        => 'January 1',
			'datepicker_date'   => '2018-01-01',
		] ) );

	}

	public function tearDown(){
		Test::tearDown();
		parent::tearDown();
	}
}
