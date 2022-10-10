<?php

namespace Tribe\Events\Views\V2\Partials\List_View;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;
use tad\FunctionMocker\FunctionMocker as Test;

class Top_BarTest extends HtmlPartialTestCase
{

	protected $partial_path = 'list/top-bar';

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
			'today_url'                  => 'http://test.tri.be',
			'today_label'                => 'Today',
			'today_title'                => 'Click to select today\'s date',
			'is_now'                     => true,
			'show_now'                   => true,
			'now_label'                  => 'Now',
			'now_label_mobile'           => 'Now',
			'show_end'                   => true,
			'selected_start_datetime'    => '2018-01-01',
			'selected_start_date_mobile' => '2018-01-01',
			'selected_start_date_label'  => 'January 1',
			'selected_end_datetime'      => '2018-01-01',
			'selected_end_date_mobile'   => '2018-01-01',
			'selected_end_date_label'    => 'January 1',
			'datepicker_date'            => '2018-01-01',
		] ) );

	}

	public function tearDown(){
		Test::tearDown();
		parent::tearDown();
	}
}
