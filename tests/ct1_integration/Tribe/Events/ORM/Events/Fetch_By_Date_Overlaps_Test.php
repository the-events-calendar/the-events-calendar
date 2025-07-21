<?php

namespace Tribe\Events\ORM\Events;

use Codeception\TestCase\WPTestCase;
use Tribe__Settings_Manager;

class Fetch_By_Date_Overlaps_Test extends WPTestCase {
	public function setUp() {
		// Before.
		parent::setUp();

		tribe_unset_var( Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// Explicitly set the timezone mode to use the site-wide setting.
		tribe_update_option( 'tribe_events_timezone_mode', 'site' );

		tribe( 'cache' )->reset();
	}

	private function setup_events(): array {
		$one = tribe_events()->set_args( [
			'title'      => 'one',
			'status'     => 'publish',
			'start_date' => '2020-01-01 10:00:00',
			'end_date'   => '2020-01-01 19:00:00',
			'timezone'   => 'Europe/Paris',
		] )->create()->ID;

		$two = tribe_events()->set_args( [
			'title'      => 'one',
			'status'     => 'publish',
			'start_date' => '2020-01-02 10:00:00',
			'end_date'   => '2020-01-02 19:00:00',
			'timezone'   => 'Europe/Paris',
		] )->create()->ID;

		$three = tribe_events()->set_args( [
			'title'      => 'one',
			'status'     => 'publish',
			'start_date' => '2020-01-03 10:00:00',
			'end_date'   => '2020-01-03 19:00:00',
			'timezone'   => 'Europe/Paris',
		] )->create()->ID;

		$four = tribe_events()->set_args( [
			'title'      => 'one',
			'status'     => 'publish',
			'start_date' => '2020-01-04 10:00:00',
			'end_date'   => '2020-01-04 19:00:00',
			'timezone'   => 'Europe/Paris',
		] )->create()->ID;

		$five = tribe_events()->set_args( [
			'title'      => 'one',
			'status'     => 'publish',
			'start_date' => '2020-01-05 10:00:00',
			'end_date'   => '2020-01-05 19:00:00',
			'timezone'   => 'Europe/Paris',
		] )->create()->ID;

		return [ $one, $two, $three, $four, $five ];
	}

	/**
	 * @covers Tribe__Events__Repositories__Event::filter_by_date_overlaps
	 */
	public function test_filter_by_date_overlaps_with_min_overlap_eq_0(): void {
		[ $one, $two, $three, $four, $five ] = $this->setup_events();
		$timezone = 'Europe/Paris';

		$assert_timestampdiff_not_used = function ( string $query ): string {
			$this->assertStringNotContainsString( 'TIMESTAMPDIFF', $query );

			return $query;
		};

		add_filter( 'query', $assert_timestampdiff_not_used );

		// Range starts before one and ends after five.
        //   |=================range=======================|
        // 	      |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $one, $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 08:00:00', '2020-01-06 19:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range start to the start of one.
        //   |============range=======================|
        //   |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $one, $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 10:00:00', '2020-01-06 19:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range start to the end of one: one is included since this is an inclusive check.
        //         |===========range==================|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $one, $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 19:00:00', '2020-01-06 19:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range start between the end of one and the start of two.
        //          |===========range=================|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-06 19:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end to the end of five: five is included since this is an inclusive check.
        //          |============range=============|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 19:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end to the start of five: five is included since this is an inclusive check.
        //          |===========range========|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 10:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end between the end of four and the start of five.
        //          |=========range=========|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 08:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end between the start of four and the end of four.
        //          |=======range=======|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 12:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end at the start of four: four is included since this is an inclusive check.
        //          |======range=====|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 10:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end between the end of three and the start of four.
        //          |======range====|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 08:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end between the start of three and the end of three.
        //          |====range===|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 12:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end at the start of three: three is included since this is an inclusive check.
        //          |=range==|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 10:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range end between the end of two and the start of three.
        //          |=range=|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 08:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range start and end to match two start and end.
        //           |range|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 10:00:00', '2020-01-02 19:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range start after the start of two.
        //            |rnge|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 12:00:00', '2020-01-02 19:00:00', $timezone, 0 )
				->get_ids()
		);

		// Move the range and before the end of two.
        //            |rng|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 12:00:00', '2020-01-02 17:00:00', $timezone, 0 )
				->get_ids()
		);

		// Collapse the range to have the same start and end.
        //              |
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 17:00:00', '2020-01-02 17:00:00', $timezone, 0 )
				->get_ids()
		);
	}

	/**
	 * @covers Tribe__Events__Repositories__Event::filter_by_date_overlaps
	 */
	public function test_filter_by_date_overlaps_with_min_overlap_eq_1(): void {
		[ $one, $two, $three, $four, $five ] = $this->setup_events();
		$timezone = 'Europe/Paris';

		$assert_timestampdiff_used = function ( string $query ): string {
			$this->assertStringNotContainsString( 'TIMESTAMPDIFF', $query );

			return $query;
		};

		add_filter( 'query', $assert_timestampdiff_used );

		// Range starts before one and ends after five.
        //   |=================range=======================|
        // 	      |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $one, $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 08:00:00', '2020-01-06 19:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range start to the start of one.
        //   |============range=======================|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $one, $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 10:00:00', '2020-01-06 19:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range start to the end of one: one is excluded since this is a non-inclusive check.
        //         |===========range==================|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 19:00:00', '2020-01-06 19:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range start between the end of one and the start of two.
        //          |===========range=================|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-06 19:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end to the end of five.
        //          |============range=============|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 19:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end to the start of five: five is excluded since this is a non-inclusive check.
        //          |===========range========|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 10:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end between the end of four and the start of five.
        //          |=========range=========|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 08:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end between the start of four and the end of four.
        //          |=======range=======|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 12:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end at the start of four: four is excluded since this is a non-inclusive check.
        //          |======range=====|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 10:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end between the end of three and the start of four.
        //          |======range====|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 08:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end between the start of three and the end of three.
        //          |====range===|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 12:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end at the start of three: three is excluded since this is a non-inclusive check.
        //          |=range==|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 10:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range end between the end of two and the start of three.
        //          |=range=|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 08:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range start and end to match two start and end.
        //           |range|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 10:00:00', '2020-01-02 19:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range start after the start of two.
        //            |rnge|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 12:00:00', '2020-01-02 19:00:00', $timezone, 1 )
				->get_ids()
		);

		// Move the range and before the end of two.
        //            |rng|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 12:00:00', '2020-01-02 17:00:00', $timezone, 1 )
				->get_ids()
		);

		// Collapse the range to have the same start and end.
        //              |
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 17:00:00', '2020-01-02 17:00:00', $timezone, 1 )
				->get_ids()
		);
	}

	/*
	 * This test is not inclusive like the `test_filter_by_date_overlaps_with_min_overlap_eq_1` one, but will
	 * use the TIMESTAMPDIFF function due to the `$min_sec_overlap` argument greater than `1`.
	 *
	 * @covers Tribe__Events__Repositories__Event::filter_by_date_overlaps
	 */
	public function test_filter_by_date_overlaps_with_min_overlap_eq_2(): void {
		[ $one, $two, $three, $four, $five ] = $this->setup_events();
		$timezone = 'Europe/Paris';

		$timestampdiff_uses       = 0;
		$count_timestampdiff_uses = function ( string $query ) use ( &$timestampdiff_uses ): string {
			if ( str_starts_with( $query, 'SELECT' ) && str_contains( $query, 'TIMESTAMPDIFF' ) ) {
				++ $timestampdiff_uses;
			}

			return $query;
		};

		add_filter( 'query', $count_timestampdiff_uses );

		// Range starts before one and ends after five.
        //   |=================range=======================|
        // 	      |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $one, $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 08:00:00', '2020-01-06 19:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range start to the start of one.
        //   |============range=======================|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $one, $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 10:00:00', '2020-01-06 19:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range start to the end of one: one is excluded since this is a non-inclusive check.
        //         |===========range==================|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-01 19:00:00', '2020-01-06 19:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range start between the end of one and the start of two.
        //          |===========range=================|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-06 19:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end to the end of five.
        //          |============range=============|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four, $five ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 19:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end to the start of five: five is excluded since this is a non-inclusive check.
        //          |===========range========|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 10:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end between the end of four and the start of five.
        //          |=========range=========|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-05 08:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end between the start of four and the end of four.
        //          |=======range=======|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three, $four ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 12:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end at the start of four: four is excluded since this is a non-inclusive check.
        //          |======range=====|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 10:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end between the end of three and the start of four.
        //          |======range====|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-04 08:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end between the start of three and the end of three.
        //          |====range===|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two, $three ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 12:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end at the start of three: three is excluded since this is a non-inclusive check.
        //          |=range==|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 10:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range end between the end of two and the start of three.
        //          |=range=|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 08:00:00', '2020-01-03 08:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range start and end to match two start and end.
        //           |range|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 10:00:00', '2020-01-02 19:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range start after the start of two.
        //            |rnge|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 12:00:00', '2020-01-02 19:00:00', $timezone, 2 )
				->get_ids()
		);

		// Move the range and before the end of two.
        //            |rng|
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 12:00:00', '2020-01-02 17:00:00', $timezone, 2 )
				->get_ids()
		);

		// Collapse the range to have the same start and end.
        //              |
        // 	 |==1==| |==2==| |==3==| |==4==| |==5==|
        $this->assertEquals(
			[ $two ],
			tribe_events()
				->where( 'date_overlaps', '2020-01-02 17:00:00', '2020-01-02 17:00:00', $timezone, 2 )
				->get_ids()
		);

		// We've run 17 fetch queries for overlapping Events.
		$this->assertEquals( 17, $timestampdiff_uses );
	}
}
