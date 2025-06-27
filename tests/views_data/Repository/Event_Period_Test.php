<?php
/**
 * ${CARET}
 *
 * @since   4.9.13
 *
 * @package Tribe\Events\Views\V2\Repository
 */


namespace Tribe\Events\Views\V2\Repository;


use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Date_Utils as Dates;

class Event_Period_Test extends WPTestCase {

	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
		static::factory()->venue = new Venue();
	}

	/**
	 * It should allow fetching events in a period
	 *
	 * @test
	 */
	public function should_allow_fetching_events_in_a_period() {
		$start_date = '2019-08-26';
		$end_date   = '2019-10-04';
		list( $before, $in_period, $after ) = $this->setup_events( $start_date, $end_date );

		/** @var Event_Period $repo */
		$repo      = tribe_events( 'period' );
		$found_ids = $repo->where( 'in_period', $start_date, $end_date )->get_ids();

		$this->assertEquals( $in_period, $found_ids );
	}

	protected function setup_events( $start_date, $end_date ) {
		$one_week = Dates::interval( 'P1W' );
		$one_day  = Dates::interval( 'P1D' );
		$start    = \DateTimeImmutable::createFromMutable( Dates::build_date_object( $start_date ) );
		$end      = \DateTimeImmutable::createFromMutable( Dates::build_date_object( $end_date ) );

		$before_dates   = [];
		$before_dates[] = $start->sub( $one_week )->format( Dates::DBDATETIMEFORMAT );
		$before_dates[] = $start->sub( $one_week )->format( Dates::DBDATETIMEFORMAT );

		$in_period_dates   = [];
		$in_period_dates[] = $start->add( $one_day )->format( Dates::DBDATETIMEFORMAT );
		$in_period_dates[] = $start->add( $one_day )->format( Dates::DBDATETIMEFORMAT );

		$after_dates   = [];
		$after_dates[] = $end->add( $one_week )->format( Dates::DBDATETIMEFORMAT );
		$after_dates[] = $end->add( $one_week )->format( Dates::DBDATETIMEFORMAT );

		$before_period_events = array_map( [ $this, 'create_for_date' ], $before_dates );
		$in_period_events     = array_map( [ $this, 'create_for_date' ], $in_period_dates );
		$after_period_events  = array_map( [ $this, 'create_for_date' ], $after_dates );

		return [ $before_period_events, $in_period_events, $after_period_events ];
	}

	/**
	 * It should allow getting sets of events in the period
	 *
	 * @test
	 */
	public function should_allow_getting_sets_of_events_in_the_period() {
		$start_date = Dates::build_date_object( '2019-08-26 00:00:00' );
		$end_date   = Dates::build_date_object( '2019-10-04 23:59:59' );
		list( $before, $in_period, $after ) = $this->setup_events( $start_date, $end_date );

		/** @var Event_Period $repo */
		$repo       = tribe_events( 'period' );
		$found_sets = $repo->where( 'in_period', $start_date, $end_date )->get_sets();

		$set_days = array_keys( $found_sets );
		$this->assertEquals( '2019-08-26', reset( $set_days ) );
		$this->assertEquals( '2019-10-04', end( $set_days ) );
		// The diff is not inclusive of the first day, se we add 1.
		$days = $end_date->diff($start_date)->days + 1;
		$this->assertCount( $days, $found_sets );
		$this->assertContainsOnlyInstancesOf( Events_Result_Set::class, $found_sets );
	}

	/**
	 * It should get events ending or staring in period
	 *
	 * @test
	 */
	public function should_get_events_ending_or_staring_in_period() {
		$starts_before = static::factory()->event->create( [ 'when' => '2019-01-01', 'duration' => WEEK_IN_SECONDS ] );
		$ends_after    = static::factory()->event->create( [ 'when' => '2019-01-05', 'duration' => WEEK_IN_SECONDS ] );
		$start_date    = '2019-01-03 00:00:00';
		$end_date      = '2019-01-10 23:59:59';

		/** @var Event_Period $repo */
		$repo      = tribe_events( 'period' );
		$found_ids = $repo->where( 'in_period', $start_date, $end_date )->get_ids();

		$this->assertEquals( [ $starts_before, $ends_after ], $found_ids );
	}

	/**
	 * It should not refetch on a second query
	 *
	 * @test
	 */
	public function should_not_refetch_on_a_second_query() {
		$start_date = Dates::build_date_object( '2019-08-26 00:00:00' );
		$end_date   = Dates::build_date_object( '2019-10-04 23:59:59' );

		list( $before, $in_period, $after ) = $this->setup_events( $start_date, $end_date );

		/** @var Event_Period $repo */
		$repo       = tribe_events( 'period' );
		$repo->where( 'in_period', $start_date, $end_date )->get_ids();

		$after_warmup_query_count = $this->queries()->countQueries();

		$repo->where( 'in_period', $start_date, $end_date )->get_ids();
		$repo->where( 'in_period', $start_date, $end_date )->get_ids();
		$repo->where( 'in_period', $start_date, $end_date )->get_ids();

		$this->queries()->assertCountQueries($after_warmup_query_count);
	}

	/**
	 * It should not re-fetch on period incl. in current period
	 *
	 * @test
	 */
	public function should_not_re_fetch_on_period_incl_in_current_period() {
		$start_date = Dates::build_date_object( '2019-08-26 00:00:00' );
		$end_date   = Dates::build_date_object( '2019-10-04 23:59:59' );

		list( $before, $in_period, $after ) = $this->setup_events( $start_date, $end_date );
		$canary = static::factory()->event->create( [ 'when' => '2019-08-30 09:00:00' ] );

		/** @var Event_Period $repo */
		$repo = tribe_events( 'period' );

		// e.g. fetch as we would do on the calendar grid.
		$repo->where( 'in_period', $start_date, $end_date )->get_ids();

		$after_warmup_query_count = $this->queries()->countQueries();

		// e.g.then fetch for a day in the calendar grid.
		$day_ids = $repo->by_date( '2019-08-30' )->get_ids();

		$this->queries()->assertCountQueries( $after_warmup_query_count );
		$this->assertEquals( [ $canary ], $day_ids );

		// e.g.then fetch for three days in the calendar grid.
		$three_day_ids = $repo->where( 'in_period', '2019-08-30 00:00:00', '2019-09-02 23:59:59' )->get_ids();

		$this->queries()->assertCountQueries( $after_warmup_query_count );
		$this->assertEquals( [ $canary ], $three_day_ids );
	}

	protected function create_for_date( $date ) {
		return static::factory()->event->create( [ 'when' => $date ] );
	}

	/**
	 * It should allow getting the caching version of the repository
	 *
	 * @test
	 */
	public function should_allow_getting_the_caching_version_of_the_repository() {
		/** @var Event_Period $repository */
		$repository = tribe_events( 'period', 'caching' );
		$this->assertInstanceOf( Event_Period::class, $repository );
		$this->assertTrue( $repository->cache_results );
	}

	/**
	 * It should use cache if caching
	 *
	 * @test
	 */
	public function should_use_cache_if_caching() {
		$start_date = Dates::build_date_object( '2019-08-26 00:00:00' );
		$end_date   = Dates::build_date_object( '2019-10-04 23:59:59' );

		list( $before, $in_period, $after ) = $this->setup_events( $start_date, $end_date );
		$canary = static::factory()->event->create( [ 'when' => '2019-08-30 09:00:00' ] );

		// e.g. Warmup.
		tribe_events( 'period', 'caching' )->where( 'in_period', $start_date, $end_date )->get_ids();

		$after_warmup_query_count = $this->queries()->countQueries();

		// e.g.then fetch for a day in the calendar grid.
		$day_ids = tribe_events( 'period', 'caching' )->by_date( '2019-08-30' )->get_ids();

		$this->queries()->assertCountQueries( $after_warmup_query_count );
		$this->assertEquals( [ $canary ], $day_ids );

		// e.g.then fetch for three days in the calendar grid.
		$three_day_ids = tribe_events( 'period', 'caching' )
			->where( 'in_period', '2019-08-30 00:00:00', '2019-09-02 23:59:59' )
			->get_ids();

		$this->queries()->assertCountQueries( $after_warmup_query_count );
		$this->assertEquals( [ $canary ], $three_day_ids );
	}

	/**
	 * It should allow fetching events by Venue
	 *
	 * @test
	 */
	public function should_allow_fetching_events_by_venue() {
		$start_date = Dates::build_date_object( '2019-08-26 00:00:00' );
		$end_date   = Dates::build_date_object( '2019-10-04 23:59:59' );

		$venue_1   = static::factory()->venue->create();
		$venue_2   = static::factory()->venue->create();
		$venue_3   = static::factory()->venue->create();

		$w_venue_1 = static::factory()->event->create(
			[
				'when'  => '2019-09-10 09:00:00',
				'venue' => $venue_1,
			]
		);
		$w_venue_2 = static::factory()->event->create(
			[
				'when'  => '2019-09-10 09:00:00',
				'venue' => $venue_2
			]
		);
		$wo_venue  = static::factory()->event->create( [ 'when' => '2019-09-10 09:00:00' ] );

		$result_0 = tribe_events( 'period' )->where( 'in_period', $start_date, $end_date )->get_ids();
		$this->assertEqualSets(
			[ $w_venue_1, $w_venue_2, $wo_venue ],
			$result_0,
			'W/o the Venue filter the all 3 events should match the query.'
		);

		$result_1 = tribe_events( 'period' )
			->where( 'in_period', $start_date, $end_date )
			->where( 'venue', $venue_1 )
			->get_ids();
		$this->assertEquals(
			[ $w_venue_1 ],
			$result_1,
			'Using venue_1 only the event w/ venue_1 should match.'
		);

		$result_2 = tribe_events( 'period' )
			->where( 'in_period', $start_date, $end_date )
			->where( 'venue', $venue_3 )
			->get_ids();
		$this->assertEmpty( $result_2, 'Using venue_3 no event should match.' );

		$result_3 = tribe_events( 'period' )
			->where( 'in_period', $start_date, $end_date )
			->where( 'venue', [ $venue_3, $venue_2 ] )
			->get_ids();
		$this->assertEquals(
			[ $w_venue_2 ],
			$result_3,
			'Using venue_3 and venue_2 the event w/ venue_2 should match.'
		);
	}
}
