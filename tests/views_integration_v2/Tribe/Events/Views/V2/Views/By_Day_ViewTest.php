<?php

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Views\V2\Messages;
use Tribe\Events\Views\V2\Utils\Stack;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;
use Tribe__Date_Utils as Dates;

class By_Day_ViewTest extends ViewTestCase {
	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
	}

	public function multi_day_backfill_data_set() {
		yield 'no_events_on_27' => [
				[ 28, 29, 30 ],
				static function ( $date, array $events, $md_event ) {
					$map = [
						'2019-10-27' => [],
						// Back-filled here.
						'2019-10-28' => array_merge( $events['2019-10-28'], [ $md_event ] ),
						'2019-10-29' => array_merge( $events['2019-10-29'], [ $md_event ] ),
						'2019-10-30' => array_merge( $events['2019-10-30'], [ $md_event ] ),
					];

					return $map[ $date ];
				}
			];

		yield 'no_events_on_28' => [
			[ 27, 29, 30 ],
			static function ( $date, array $events, $md_event ) {
				$map = [
					'2019-10-27' => $events['2019-10-27'],
					// Back-filled here.
					'2019-10-28' => [ $md_event ],
					'2019-10-29' => array_merge( $events['2019-10-29'], [ $md_event ] ),
					'2019-10-30' => array_merge( $events['2019-10-30'], [ $md_event ] ),
				];

				return $map[ $date ];
			}
		];

		yield 'no_events_on_29' => [
			[ 27, 28, 30 ],
			static function ( $date, array $events, $md_event ) {
				$map = [
					'2019-10-27' => $events['2019-10-27'],
					// Back-filled here.
					'2019-10-28' => array_merge( $events['2019-10-28'], [ $md_event ] ),
					'2019-10-29' => [ $md_event ],
					'2019-10-30' => array_merge( $events['2019-10-30'], [ $md_event ] ),
				];

				return $map[ $date ];
			}
		];

		yield 'no_events_on_30' => [
			[ 27, 28, 29 ],
			static function ( $date, array $events, $md_event ) {
				$map = [
					'2019-10-27' => $events['2019-10-27'],
					// Back-filled here.
					'2019-10-28' => array_merge( $events['2019-10-28'], [ $md_event ] ),
					'2019-10-29' => array_merge( $events['2019-10-29'], [ $md_event ] ),
					'2019-10-30' => [ $md_event ],
				];

				return $map[ $date ];
			}
		];

		yield 'two_events_on_each_day' => [
			[ 27, 28, 29, 30 ],
			static function ( $date, array $events, $md_event ) {
				$map = [
					'2019-10-27' => $events['2019-10-27'],
					// Back-filled here.
					'2019-10-28' => array_merge( $events['2019-10-28'], [ $md_event ] ),
					'2019-10-29' => array_merge( $events['2019-10-29'], [ $md_event ] ),
					'2019-10-30' => array_merge( $events['2019-10-30'], [ $md_event ] ),
				];

				return $map[ $date ];
			}
		];
	}

	/**
	 * It should correctly back-fill multi-day events
	 *
	 * The 2 events per day, given the 2 events per day limit, will add the multi-day event to the correct days.
	 * Multi-day events are not trimmed in get_grid_days(), and the limit doesn't apply to the the same way,
	 * so they'll just be added to the list(s).
	 *
	 * @test
	 * @dataProvider multi_day_backfill_data_set
	 */
	public function should_correctly_back_fill_multi_day_events( array $days, callable $expected ) {
		$events = $this->make_two_events_per_day( $days );
		$md_event = static::factory()->event->create(
			[
				'when'     => '2019-10-28 17:00:00',
				'duration' => 2 * DAY_IN_SECONDS,
			]
		);

		$expected = [
			'2019-10-27' => $expected( '2019-10-27', $events, $md_event ),
			'2019-10-28' => $expected( '2019-10-28', $events, $md_event ),
			'2019-10-29' => $expected( '2019-10-29', $events, $md_event ),
			'2019-10-30' => $expected( '2019-10-30', $events, $md_event ),
		];

		$end_date = '2019-10-30';

		$period = new \DatePeriod(
			Dates::build_date_object( '2019-10-27' ),
			new \DateInterval( 'P1D' ),
			Dates::build_date_object( '2019-10-30' )
		);

		$view      = $this->make_view();
		$grid_days = $view->get_grid_days( $end_date );

		/** @var \DateTime $day */
		foreach ( $period as $day ) {
			$day_string = $day->format( Dates::DBDATEFORMAT );
			$this->assertArrayHasKey( $day_string, $grid_days );
			$this->assertEqualsCanonicalizing(
				$expected[ $day_string ],
				$grid_days[ $day_string ],
				'Days on day ' . $day_string . ' do not match expectation.'
			);
		}
	}

	protected function make_two_events_per_day( array $days ) {
		$events = [];
		foreach ( $days as $day ) {
			$events["2019-10-{$day}"]   = [];
			$events["2019-10-{$day}"][] = static::factory()->event->create( [ 'when' => "2019-10-{$day} 06:00:00" ] );
			$events["2019-10-{$day}"][] = static::factory()->event->create( [ 'when' => "2019-10-{$day} 07:00:00" ] );
		}

		return $events;
	}

	protected function make_view(): By_Day_View {
		return new class( new Messages(), new Stack() ) extends By_Day_View {

			public function __construct( Messages $messages, Stack $stack ) {
				parent::__construct( $messages, $stack );
				$this->context = tribe_context()->alter(
					[
						'paged' => 1,
						'events_per_page' => 2
					]
				);
			}

			protected function calculate_grid_start_end( $date ) {
				$end   = Dates::build_date_object( $date );
				$start = clone $end;
				$start->sub( new \DateInterval( 'P3D' ) );

				return [ $start, $end ];
			}

			protected function get_url_date_format() {
				return 'Y-m';
			}
		};
	}

	/**
	 * Tests that the `url_for_query_args` method removes pagination parameters from the query arguments.
	 *
	 * @test
	 */
	public function should_remove_pagination_params_from_query_args() {
		$view                = $this->make_view();
		$query_args          = [
			'foo'   => 'bar',
			'page'  => 2,
			'paged' => 3,
			'baz'   => 'qux',
		];
		$expected_query_args = [
			'foo' => 'bar',
			'baz' => 'qux',
		];

		$url = $view->url_for_query_args( '2019-10-28', $query_args );
		$this->assertStringContainsString( '?foo=bar&baz=qux', $url, 'Expected query args are not present in URL' );
		$this->assertStringNotContainsString( 'page', $url, 'The "page" query parameter was not removed' );
		$this->assertStringNotContainsString( 'paged', $url, 'The "paged" query parameter was not removed' );
	}

	/**
	 * Tests that the `url_for_query_args` method converts a query string argument to an array of query arguments.
	 *
	 * @test
	 */
	public function should_convert_string_args_to_array() {
		$view                = $this->make_view();
		$query_args          = 'foo=bar&page=2&baz=qux';
		$expected_query_args = [
			'foo' => 'bar',
			'baz' => 'qux',
		];

		$url = $view->url_for_query_args( '2019-10-28', $query_args );
		$this->assertStringContainsString( '?foo=bar&baz=qux', $url, 'Expected query args are not present in URL' );
		$this->assertStringNotContainsString( 'page', $url, 'The "page" query parameter was not removed' );
		$this->assertStringNotContainsString( 'paged', $url, 'The "paged" query parameter was not removed' );
	}

	/**
	 * Tests that the `url_for_query_args` method does not remove non-pagination parameters from the query arguments.
	 *
	 * @test
	 */
	public function should_not_remove_other_params_from_query_args() {
		$view       = $this->make_view();
		$query_args = [
			'foo' => 'bar',
			'baz' => 'qux',
		];

		$url = $view->url_for_query_args( '2019-10-28', $query_args );
		$this->assertStringContainsString( '?foo=bar&baz=qux', $url, 'Expected query args are not present in URL' );
		$this->assertStringNotContainsString( 'page', $url, 'The "page" query parameter was removed' );
		$this->assertStringNotContainsString( 'paged', $url, 'The "paged" query parameter was removed' );
	}
}
