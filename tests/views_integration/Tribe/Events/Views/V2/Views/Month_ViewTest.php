<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;
use Tribe__Timezones as Timezones;

class Month_ViewTest extends ViewTestCase {
	use MatchesSnapshots;

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	public function setUp() {
		parent::setUp();

		$now = new \DateTime( $this->mock_date_value );

		$this->context = tribe_context()->alter( [
			'event_date' => $now->format( 'Y-m-d' ),
		] );
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$month_view = View::make( Month_View::class, $this->context );

		$this->assertEmpty( $month_view->found_post_ids() );

		 $this->assertMatchesSnapshot( $month_view->get_html() );
	}

	/**
	 * Test render with events
	 */
	public function test_render_with_events() {
		/** @var Month_View $month_view */
		$month_view = View::make( Month_View::class, $this->context );
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new \DateTimeImmutable( $this->mock_date_value, $timezone );

		$events    = array_map( static function ( $i ) use ( $now, $timezone ) {
			return tribe_events()->set_args( [
					'start_date' => $now->setTime( 10 + $i, 0 ),
					'timezone'   => $timezone,
					'duration'   => 3 * HOUR_IN_SECONDS,
					'title'      => 'Test Event - ' . $i,
					'status'     => 'publish',
				]
			)->create();
		}, range( 1, 3 ) );
		$event_ids = array_map( static function ( \WP_Post $event ) {
			return $event->ID;
		}, $events );

		 $month_view->get_html();

		$this->assertEquals( $event_ids, $month_view->found_post_ids() );

		foreach ( $month_view->get_grid_days( $now->format( 'Y-m' ) ) as $date => $found_day_ids ) {
			$day          = new \DateTimeImmutable( $date, $timezone );
			$expected_ids = tribe_events()
				->where(
					'date_overlaps',
					$day->setTime( 0, 0 ),
					$day->setTime( 23, 59, 59 ),
					$timezone
				)->get_ids();

			$this->assertEquals(
				$expected_ids,
				$found_day_ids,
				sprintf(
					'Day %s event IDs mismatch, expected %s, got %s',
					$day->format( 'Y-m-d' ),
					json_encode( $expected_ids ),
					json_encode( $found_day_ids )
				) );
		}

		// $this->assertMatchesSnapshot( $month_view->get_html() );
	}

	/**
	 * It should correctly parse and add spacers in week stack not recycling spaces
	 *
	 * @test
	 */
	public function should_correctly_parse_and_add_spacers_in_week_stack_not_recycling_spaces() {
		$start_of_week = 1; // Monday
		update_option( 'start_of_week', $start_of_week );
		$timezone = Timezones::build_timezone_object();
		$monday   = new \DateTimeImmutable( '2019-07-15 09:00:00', $timezone );
		$tuesday  = new \DateTimeImmutable( '2019-07-16 09:00:00', $timezone );
		$friday   = new \DateTimeImmutable( '2019-07-19 09:00:00', $timezone );
		$sunday   = new \DateTimeImmutable( '2019-07-21 09:00:00', $timezone );

		$event_1 = tribe_events()->set_args( [
			'title'      => 'Start on Mo 9am, end on Thu 1pm',
			'start_date' => $monday,
			'duration'   => 2 * DAY_IN_SECONDS + 4 * HOUR_IN_SECONDS,
			'status'     => 'publish'
		] )->create();
		$event_2 = tribe_events()->set_args( [
			'title'      => 'Start on Mo 10am, end on Tue 2pm',
			'start_date' => $monday->setTime( 10, 0 ),
			'duration'   => 1 * DAY_IN_SECONDS + 4 * HOUR_IN_SECONDS,
			'status'     => 'publish'
		] )->create();
		$event_3 = tribe_events()->set_args( [
			'title'      => 'Start on Tue 9am, end on Sat 2pm',
			'start_date' => $tuesday,
			'duration'   => 5 * DAY_IN_SECONDS + 4 * HOUR_IN_SECONDS,
			'status'     => 'publish'
		] )->create();
		$event_4 = tribe_events()->set_args( [
			'title'      => 'Start on Fri 9am, end on next Tue 2pm',
			'start_date' => $friday,
			'duration'   => 4 * DAY_IN_SECONDS + 4 * HOUR_IN_SECONDS,
			'status'     => 'publish'
		] )->create();

		// Let's access the 2019 July Month grid.
		$this->context->alter( [ 'event_date' => '2019-07-01' ] );
		$month_view = View::make( Month_View::class, $this->context );

		add_filter( 'tribe_events_views_v2_stack_recycle_spaces', '__return_false' );

		$week_stack          = $month_view->get_multiday_stack( $monday, $sunday );
		$expected_week_stack = [
			[ $event_1->ID, $event_1->ID, $event_1->ID, '_', '_', '_', '_' ],
			[ $event_2->ID, $event_2->ID, '_', '_', '_', '_', '_' ],
			[ '_', $event_3->ID, $event_3->ID, $event_3->ID, $event_3->ID, $event_3->ID, $event_3->ID ],
			[ '_', '_', '_', '_', $event_4->ID, $event_4->ID, $event_4->ID ],
		];
		$expected            = $this->visualize_week_stack( $expected_week_stack, true );
		$actual              = $this->visualize_week_stack( $week_stack );
		$this->assertEquals( $expected, $actual );

		add_filter( 'tribe_events_views_v2_stack_recycle_spaces', '__return_true' );

		$week_stack          = $month_view->get_multiday_stack( $monday, $sunday );
		$expected_week_stack = [
			[ $event_1->ID, $event_1->ID, $event_1->ID, '_', $event_4->ID, $event_4->ID, $event_4->ID ],
			[ $event_2->ID, $event_2->ID, '_', '_', '_', '_', '_' ],
			[ '_', $event_3->ID, $event_3->ID, $event_3->ID, $event_3->ID, $event_3->ID, $event_3->ID ],
		];
		$expected            = $this->visualize_week_stack( $expected_week_stack, true );
		$actual              = $this->visualize_week_stack( $week_stack );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Creates a "visual" representation of the week stack for easier understanding and comparison.
	 *
	 * @since TBD
	 *
	 * @param array $week_stack The week stack columns (e.g. as returned by the Month view) or rows.
	 * @param bool  $passing_rows Whether we're passing in columns (as coming from Views) or rows.
	 *
	 * @return string An ASCII table representing the week stack.
	 */
	protected function visualize_week_stack( array $week_stack, $passing_rows = false ) {
		$rows = [];

		$week_stack = array_map( static function ( $column ) {
			$filled = [];
			foreach ( $column as $item ) {
				$filled[] = empty( $item ) ? '_' : $item;
			}

			return $filled;
		}, $week_stack );

		if ( ! $passing_rows ) {
			$week_stack_height = array_reduce( $week_stack, static function ( $height, array $column ) {
				return max( $height, count( $column ) );
			}, 0 );
			foreach ( range( 1, $week_stack_height ) as $i ) {
				$rows[ $i ] = array_column( $week_stack, $i - 1 );
			}
		} else {
			$rows = $week_stack;
		}

		$table = trim( implode( PHP_EOL, array_map( static function ( array $row ) {
			return '|' . implode( '|', $row ) . '|';
		}, $rows ) ) );

		return PHP_EOL . $table;
	}
}
