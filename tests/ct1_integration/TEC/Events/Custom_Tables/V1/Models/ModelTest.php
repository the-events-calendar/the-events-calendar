<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use Codeception\TestCase\WPTestCase;
use TEC\Events_Pro\Custom_Tables\V1\EventRecurrence_Factory;
use WP_Post;

class ModelTest extends WPTestCase {
	/**
	 * It should allow using raw WHERE clauses for filtering
	 *
	 * @test
	 */
	public function should_allow_using_raw_where_clauses_for_filtering() {
		foreach ( range( 1, 4 ) as $i ) {
			$event = tribe_events()->set_args( [
				'title'      => 'test',
				'status'     => 'publish',
				'start_date' => "2020-01-$i 10:00:00",
				'end_date'   => "2020-01-$i 12:00:00",
				'timezone'   => 'Europe/Paris',
			] )->create();
			$this->assertInstanceOf( WP_Post::class, $event );
			$events[] = $event;
		}
		$occurrences = Occurrence::order_by( 'start_date', 'ASC' )
		                         ->limit( 10 )
		                         ->get();
		$this->assertCount( 4, $occurrences );
		$map_occurrence_dates = static function ( Occurrence $occurrence ) {
			return [ $occurrence->start_date, $occurrence->end_date ];
		};
		$occurrence_dates = array_map( $map_occurrence_dates, $occurrences );
		$this->assertEquals( [
			[ '2020-01-01 10:00:00', '2020-01-01 12:00:00' ],
			[ '2020-01-02 10:00:00', '2020-01-02 12:00:00' ],
			[ '2020-01-03 10:00:00', '2020-01-03 12:00:00' ],
			[ '2020-01-04 10:00:00', '2020-01-04 12:00:00' ],
		], $occurrence_dates );

		Occurrence::where_raw( 'start_date = %s OR start_date = %s', '2020-01-02 10:00:00', '2020-01-04 10:00:00' )
		          ->delete();

		$occurrences = Occurrence::order_by( 'start_date', 'ASC' )
		                         ->limit( 10 )
		                         ->get();
		$this->assertCount( 2, $occurrences );
		$map_occurrence_dates = static function ( Occurrence $occurrence ) {
			return [ $occurrence->start_date, $occurrence->end_date ];
		};
		$occurrence_dates = array_map( $map_occurrence_dates, $occurrences );
		$this->assertEquals( [
			[ '2020-01-01 10:00:00', '2020-01-01 12:00:00' ],
			[ '2020-01-03 10:00:00', '2020-01-03 12:00:00' ],
		], $occurrence_dates );
	}
}
