<?php

namespace Tribe\Events\Views\V2\Views\Traits;

use Tribe\Events\Views\V2\View;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe__Context as Context;

class List_BehaviorTest extends \Codeception\TestCase\WPTestCase {
	use With_Post_Remapping;

	public function setUp() {
		parent::setUp();
		/** @var \Tribe__Cache $cache */
		$cache = tribe( 'cache' );
	}

	public function setup_datepicker_template_vars_data_set() {
		$context = static function ( array $alterations = [] ) {
			return static function () use ( $alterations ) {
				return tribe_context()->alter( $alterations );
			};
		};

		$event = function ( $start_date ) {
			 return function () use ( $start_date ) {
				return $this->get_mock_event(
					'events/single/id_and_date.template.json',
					[
						'id' => 2347927 + str_replace('-','',$start_date),
						'post_content' => $start_date . ' event',
						'start_date'   => $start_date,
						'end_date'     => $start_date,
					]
				);
			 };
		};

		yield 'now_wo_events' => [
			[
				'now'    => '2019-01-01 09:00:00',
				'today'  => '2019-01-01',
				'events' => [],
			],
			$context(),
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Upcoming',
				'now_label_mobile'           => 'Upcoming',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '1/1/2019',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-01',
				'selected_end_date_mobile'   => '1/1/2019',
				'selected_end_date_label'    => 'Now',
				'datepicker_date'            => '1/1/2019',
			],
		];

		yield 'now_w_events_on_diff_dates' => [
			[
				'now'    => '2019-01-01 09:00:00',
				'today'  => '2019-01-01',
				'events' => [
					$event( '2019-01-02' ),
					$event( '2019-01-05' ),
				],
			],
			$context(),
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Upcoming',
				'now_label_mobile'           => 'Upcoming',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '1/1/2019',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '1/5/2019',
				'selected_end_date_label'    => 'January 5, 2019',
				'datepicker_date'            => '1/1/2019',
			],
		];

		yield 'now_w_events_on_diff_dates_w_next' => [
			[
				'now'      => '2019-01-01 09:00:00',
				'today'    => '2019-01-01',
				'events'   => [
					$event( '2019-01-02' ),
					$event( '2019-01-05' ),
				],
				'next_url' => 'something',
			],
			$context(),
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '1/1/2019',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '1/5/2019',
				'selected_end_date_label'    => 'January 5, 2019',
				'datepicker_date'            => '1/1/2019',
				'next_url'                   => 'something',
			],
		];

		yield 'now_w_events_on_diff_dates_page_2' => [
			[
				'now'    => '2019-01-01 09:00:00',
				'today'  => '2019-01-01',
				'events' => [
					$event( '2019-01-02' ),
					$event( '2019-01-05' ),
				],
			],
			$context(
				[
					'page' => 2,
				]
			),
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Upcoming',
				'now_label_mobile'           => 'Upcoming',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '1/2/2019',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '1/5/2019',
				'selected_end_date_label'    => 'January 5, 2019',
				'datepicker_date'            => '1/2/2019',
			],
		];

		yield 'now_w_events_on_diff_dates_page_2_w_next' => [
			[
				'now'      => '2019-01-01 09:00:00',
				'today'    => '2019-01-01',
				'events'   => [
					$event( '2019-01-02' ),
					$event( '2019-01-05' ),
				],
				'page'     => 2,
				'next_url' => 'something',
			],
			$context(),
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '1/1/2019',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-05',
				'selected_end_date_mobile'   => '1/5/2019',
				'selected_end_date_label'    => 'January 5, 2019',
				'datepicker_date'            => '1/1/2019',
				'page'                       => 2,
				'next_url'                   => 'something',
			],
		];

		yield 'now_w_events_on_same_dates' => [
			[
				'now'    => '2019-01-01 09:00:00',
				'today'  => '2019-01-01',
				'events' => [
					$event( '2019-01-02' ),
					$event( '2019-01-02' ),
				],
			],
			$context(),
			[
				'now'                        => '2019-01-01 09:00:00',
				'today'                      => '2019-01-01',
				'show_now'                   => true,
				'now_label'                  => 'Upcoming',
				'now_label_mobile'           => 'Upcoming',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '1/1/2019',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-02',
				'selected_end_date_mobile'   => '1/2/2019',
				'selected_end_date_label'    => 'January 2, 2019',
				'datepicker_date'            => '1/1/2019',
			],
		];

		yield 'past_page_1_events_w_diff_dates' => [
			[
				'now'      => '2019-01-07 09:00:00',
				'today'    => '2019-01-07',
				'events'   => [
					$event( '2019-01-02' ),
					$event( '2019-01-05' ),
				],
				'next_url' => 'something',
			],
			$context(
				[
					'event_display_mode' => 'past',
				]
			),
			[
				'now'                        => '2019-01-07 09:00:00',
				'today'                      => '2019-01-07',
				'next_url'                   => 'something',
				'show_now'                   => false,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '1/2/2019',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-07',
				'selected_end_date_mobile'   => '1/7/2019',
				'selected_end_date_label'    => 'Now',
				'datepicker_date'            => '1/2/2019',
			],
		];

		yield 'past_page_1_events_w_same_dates' => [
			[
				'now'      => '2019-01-07 09:00:00',
				'today'    => '2019-01-07',
				'events'   => [
					$event( '2019-01-02' ),
					$event( '2019-01-02' ),
				],
				'next_url' => 'something',
			],
			$context(
				[
					'event_display_mode' => 'past',
				]
			),
			[
				'now'                        => '2019-01-07 09:00:00',
				'today'                      => '2019-01-07',
				'next_url'                   => 'something',
				'show_now'                   => false,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-02',
				'selected_start_date_mobile' => '1/2/2019',
				'selected_start_date_label'  => 'January 2',
				'selected_end_datetime'      => '2019-01-07',
				'selected_end_date_mobile'   => '1/7/2019',
				'selected_end_date_label'    => 'Now',
				'datepicker_date'            => '1/2/2019',
			],
		];

		yield 'past_page_2_events_w_diff_dates' => [
			[
				'now'      => '2019-01-07 09:00:00',
				'today'    => '2019-01-07',
				'events'   => [
					$event( '2019-01-01' ),
					$event( '2019-01-02' ),
				],
				'next_url' => 'something',
			],
			$context(
				[
					'event_display_mode' => 'past',
					'page'               => 2,
				]
			),
			[
				'now'                        => '2019-01-07 09:00:00',
				'today'                      => '2019-01-07',
				'next_url'                   => 'something',
				'show_now'                   => false,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => true,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '1/1/2019',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-02',
				'selected_end_date_mobile'   => '1/2/2019',
				'selected_end_date_label'    => 'January 2, 2019',
				'datepicker_date'            => '1/1/2019',
			],
		];

		yield 'past_page_2_events_w_same_dates' => [
			[
				'now'      => '2019-01-07 09:00:00',
				'today'    => '2019-01-07',
				'events'   => [
					$event( '2019-01-01' ),
					$event( '2019-01-01' ),
				],
				'next_url' => 'something',
			],
			$context(
				[
					'event_display_mode' => 'past',
					'page'               => 2,
				]
			),
			[
				'now'                        => '2019-01-07 09:00:00',
				'today'                      => '2019-01-07',
				'next_url'                   => 'something',
				'show_now'                   => false,
				'now_label'                  => 'Now',
				'now_label_mobile'           => 'Now',
				'show_end'                   => false,
				'selected_start_datetime'    => '2019-01-01',
				'selected_start_date_mobile' => '1/1/2019',
				'selected_start_date_label'  => 'January 1',
				'selected_end_datetime'      => '2019-01-01',
				'selected_end_date_mobile'   => '1/1/2019',
				'selected_end_date_label'    => 'January 1, 2019',
				'datepicker_date'            => '1/1/2019',
			],
		];
	}

	/**
	 * It should not alter the template vars if the context is unset
	 *
	 * @test
	 */
	public function should_not_alter_the_template_vars_if_the_context_is_unset() {
		$view          = $this->make_view( null );
		$template_vars = [ 'foo' => 'bar' ];

		$this->assertEqualSets( [ 'foo' => 'bar' ], $view->open_setup_datepicker_template_vars( $template_vars ) );
	}

	/**
	 * Builds a View implementing the List_Behavior trait with open methods.
	 *
	 * @return View|List_Behavior
	 */
	protected function make_view( Context $context = null ): View {
		$view = new class() extends View {
			use List_Behavior;

			public function open_setup_datepicker_template_vars( array $template_vars ) {
				return $this->setup_datepicker_template_vars( $template_vars );
			}

			public function open_remove_past_query_args() {
				$this->remove_past_query_args();
			}
		};

		$view->set_context( $context );

		return $view;
	}

	/**
	 * It should correctly setup the template vars
	 *
	 * @test
	 * @dataProvider setup_datepicker_template_vars_data_set
	 */
	public function should_correctly_setup_the_template_vars( $template_vars, $context, $expected ) {
		$view = $this->make_view( $context() );
		if ( isset( $template_vars['events'] ) ) {
			$template_vars['events'] = array_map(
				static function ( $fetch ) {
					return $fetch();
				},
				$template_vars['events']
			);
		}

		$updated = $view->open_setup_datepicker_template_vars( $template_vars );

		// We're not interested in that.
		unset( $updated['events'] );

		$this->assertEquals( $expected, $updated );
	}

	/**
	 * It should correctly remove past query args
	 *
	 * @test
	 */
	public function should_correctly_remove_past_query_args() {
		$past_url = home_url( '/events/list/?eventDisplay=past' );
		$context  = tribe_context()->alter(
			[
				'event_display_mode' => 'past',
				'event_display'      => 'list',
				'url'                => $past_url,
				'view_data'          => [
					'url' => $past_url,
				],
			]
		);

		$view = $this->make_view( $context );
		$view->open_remove_past_query_args();
		$context_arr = $view->get_context()->to_array();

		$expected_url = home_url( '/events/list/' );
		$this->assertEquals( $expected_url, $context_arr['url'] );
		$this->assertEquals( 'list', $context_arr['event_display_mode'] );
		$expected_view_data = [
			'url' => $expected_url,
		];
		$this->assertEquals( $expected_view_data, $context_arr['view_data'] );
	}
}
