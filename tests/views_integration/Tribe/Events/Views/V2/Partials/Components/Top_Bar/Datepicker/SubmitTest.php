<?php

namespace Tribe\Events\Views\V2\Partials\Components\Top_Bar\Datepicker;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class SubmitTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/top-bar/datepicker/submit';

	/**
	 * Test render w show datepicker submit
	 */
	public function test_render_w_show_datepicker_submit() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'show_datepicker_submit' => true,
		] ) );
	}

	/**
	 * Test render wo show datepicker submit
	 */
	public function test_render_wo_show_datepicker_submit() {
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'show_datepicker_submit' => false,
		] ) );
	}
}
