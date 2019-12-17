<?php

namespace Tribe\Events\Views\V2\Partials\List_View\Top_Bar;

use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class DatepickerTest extends HtmlPartialTestCase
{

	protected $partial_path = 'list/top-bar/datepicker';

	public function setUp() {
		parent::setUp();
		// Start Function Mocker.
		Test::setUp();
		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );
	}

	public function render_data_set() {
		yield 'now_wo_events' => [
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now onwards',
				'now_label_mobile'           => 'Now onwards',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '2019-01-01',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-01',
				'selected_end_date_mobile'   => '2019-01-01',
				'selected_end_date_label'    => 'Now',
				'datepicker_date'            => '2019-01-01',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'now_w_events_on_diff_dates' => [
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now onwards',
				'now_label_mobile'           => 'Now onwards',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '2019-01-05',
				'selected_end_date_label'    => 'January 5',
				'datepicker_date'            => '2019-01-02',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'now_w_events_on_diff_dates_w_next' => [
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '2019-01-05',
				'selected_end_date_label'    => 'January 5',
				'datepicker_date'            => '2019-01-02',
				'next_url'                   => 'something',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'now_w_events_on_diff_dates_page_2' => [
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'January 2 onwards',
				'now_label_mobile'           => '1/2/2019 onwards',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '2019-01-05',
				'selected_end_date_label'    => 'January 5',
				'datepicker_date'            => '2019-01-02',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'now_w_events_on_diff_dates_page_2_w_next' => [
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '2019-01-05',
				'selected_end_date_label'    => 'January 5',
				'datepicker_date'            => '2019-01-02',
				'page'                       => 2,
				'next_url'                   => 'something',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'now_w_events_on_same_dates' => [
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now onwards',
				'now_label_mobile'           => 'Now onwards',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-02',
				'selected_end_date_mobile'   => '2019-01-02',
				'selected_end_date_label'    => 'January 2',
				'datepicker_date'            => '2019-01-02',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'past_page_1_events_w_diff_dates' => [
			[
				'now'                        => '2019-01-07 09:00:00',
				'today'                      => '2019-01-07',
				'next_url'                   => 'something',
				'show_now'                   => false,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '2019-01-05',
				'selected_end_date_label'    => 'Now',
				'datepicker_date'            => '2019-01-02',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'past_page_1_events_w_same_dates' => [
			[
				'now'                        => '2019-01-07 09:00:00',
				'today'                      => '2019-01-07',
				'next_url'                   => 'something',
				'show_now'                   => false,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-02',
				'selected_end_date_mobile'   => '2019-01-02',
				'selected_end_date_label'    => 'Now',
				'datepicker_date'            => '2019-01-02',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'past_page_2_events_w_diff_dates' => [
			[
				'now'                        => '2019-01-07 09:00:00',
				'today'                      => '2019-01-07',
				'next_url'                   => 'something',
				'show_now'                   => false,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '2019-01-01',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-02',
				'selected_end_date_mobile'   => '2019-01-02',
				'selected_end_date_label'    => 'January 2',
				'datepicker_date'            => '2019-01-01',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'past_page_2_events_w_same_dates' => [
			[
				'now'                        => '2019-01-07 09:00:00',
				'today'                      => '2019-01-07',
				'next_url'                   => 'something',
				'show_now'                   => false,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '2019-01-01',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-01',
				'selected_end_date_mobile'   => '2019-01-01',
				'selected_end_date_label'    => 'January 1',
				'datepicker_date'            => '2019-01-01',
				'show_datepicker_submit'     => false,
			],
		];

		yield 'now_w_events_on_same_dates_w_datepicker_submit' => [
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now onwards',
				'now_label_mobile'           => 'Now onwards',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-02',
				'selected_end_date_mobile'   => '2019-01-02',
				'selected_end_date_label'    => 'January 2',
				'datepicker_date'            => '2019-01-02',
				'show_datepicker_submit'     => true,
				'url'                        => 'https://test.tri.be/events/today/',
			],
		];

		yield 'now_w_events_on_diff_dates_w_datepicker_submit' => [
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now onwards',
				'now_label_mobile'           => 'Now onwards',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '2019-01-02',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '2019-01-05',
				'selected_end_date_label'    => 'January 5',
				'datepicker_date'            => '2019-01-02',
				'show_datepicker_submit'     => true,
				'url'                        => 'https://test.tri.be/events/today/',
			],
		];
	}

	/**
	 * Test render
	 * @dataProvider render_data_set
	 */
	public function test_render($context) {
		$this->assertMatchesSnapshot( $this->get_partial_html( $context ) );
	}

	public function tearDown(){
		Test::tearDown();
		parent::tearDown();
	}
}
