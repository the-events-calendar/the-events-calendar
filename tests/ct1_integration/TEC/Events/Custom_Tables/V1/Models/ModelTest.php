<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use Codeception\TestCase\WPTestCase;
use Generator;
use TEC\Events\Custom_Tables\V1\Tables\Events;
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
		$occurrence_dates     = array_map( $map_occurrence_dates, $occurrences );
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
		$occurrence_dates     = array_map( $map_occurrence_dates, $occurrences );
		$this->assertEquals( [
			[ '2020-01-01 10:00:00', '2020-01-01 12:00:00' ],
			[ '2020-01-03 10:00:00', '2020-01-03 12:00:00' ],
		], $occurrence_dates );
	}

	/**
	 * It should allow setting the return output of a collection query.
	 *
	 * @test
	 */
	public function should_allow_setting_the_return_output_of_a_collection_query_() {
		foreach ( range( 1, 2 ) as $i ) {
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
		                         ->output( $output = OBJECT )
		                         ->get();

		$this->assertCount( 2, $occurrences );

		$this->assertContainsOnlyInstancesOf(
			Occurrence::class,
			$occurrences,
			'OBJECT output should return model instances.'
		);

		$array_a_occurrences = Occurrence::order_by( 'start_date', 'ASC' )
		                                 ->limit( 10 )
		                                 ->output( ARRAY_A )
		                                 ->get();
		$this->assertCount( 2, $array_a_occurrences );

		$this->assertArraySubset( [
			'occurrence_id'  => (string) $occurrences[0]->occurrence_id,
			'event_id'       => (string) $occurrences[0]->event_id,
			'post_id'        => (string) $events[0]->ID,
			'start_date'     => '2020-01-01 10:00:00',
			'start_date_utc' => '2020-01-01 09:00:00',
			'end_date'       => '2020-01-01 12:00:00',
			'end_date_utc'   => '2020-01-01 11:00:00',
			'duration'       => 2 * HOUR_IN_SECONDS,
			'hash'           => $occurrences[0]->hash,
			'updated_at'     => $occurrences[0]->updated_at,
		], $array_a_occurrences[0] );
		$this->assertArraySubset( [
			'occurrence_id'  => (string) $occurrences[1]->occurrence_id,
			'event_id'       => (string) $occurrences[1]->event_id,
			'post_id'        => (string) $events[1]->ID,
			'start_date'     => '2020-01-02 10:00:00',
			'start_date_utc' => '2020-01-02 09:00:00',
			'end_date'       => '2020-01-02 12:00:00',
			'end_date_utc'   => '2020-01-02 11:00:00',
			'duration'       => 2 * HOUR_IN_SECONDS,
			'hash'           => $occurrences[1]->hash,
			'updated_at'     => $occurrences[1]->updated_at,
		], $array_a_occurrences[1] );

		$array_n_occurrences = Occurrence::order_by( 'start_date', 'ASC' )
		                                 ->limit( 10 )
		                                 ->output( ARRAY_N )
		                                 ->get();
		$this->assertCount( 2, $array_n_occurrences );
		$this->assertCount( 2, $array_n_occurrences );
		$this->assertArraySubset( [
			(string) $occurrences[0]->occurrence_id,
			(string) $occurrences[0]->event_id,
			(string) $events[0]->ID,
			'2020-01-01 10:00:00',
			'2020-01-01 09:00:00',
			'2020-01-01 12:00:00',
			'2020-01-01 11:00:00',
			2 * HOUR_IN_SECONDS,
			$occurrences[0]->hash,
			$occurrences[0]->updated_at,
		], $array_n_occurrences[0] );
		$this->assertArraySubset( [
			(string) $occurrences[1]->occurrence_id,
			(string) $occurrences[1]->event_id,
			(string) $events[1]->ID,
			'2020-01-02 10:00:00',
			'2020-01-02 09:00:00',
			'2020-01-02 12:00:00',
			'2020-01-02 11:00:00',
			2 * HOUR_IN_SECONDS,
			$occurrences[1]->hash,
			$occurrences[1]->updated_at,
		], $array_n_occurrences[1] );
	}

	/**
	 * It should allow setting the output format of a single model query
	 *
	 * @test
	 */
	public function should_allow_setting_the_output_format_of_a_single_model_query() {
		$event = tribe_events()->set_args( [
			'title'      => 'test',
			'status'     => 'publish',
			'start_date' => '2020-01-01 10:00:00',
			'end_date'   => '2020-01-01 12:00:00',
			'timezone'   => 'Europe/Paris',
		] )->create();
		$this->assertInstanceOf( WP_Post::class, $event );

		$occurrence = Occurrence::where( 'post_id', '=', $event->ID )
		                        ->output( $output = OBJECT )
		                        ->first();

		$this->assertInstanceOf( Occurrence::class, $occurrence );

		$array_a_occurrence = Occurrence::where( 'post_id', '=', $event->ID )
		                                ->output( ARRAY_A )
		                                ->first();

		$this->assertArraySubset( [
			'occurrence_id'  => (string) $occurrence->occurrence_id,
			'event_id'       => (string) $occurrence->event_id,
			'post_id'        => (string) $event->ID,
			'start_date'     => '2020-01-01 10:00:00',
			'start_date_utc' => '2020-01-01 09:00:00',
			'end_date'       => '2020-01-01 12:00:00',
			'end_date_utc'   => '2020-01-01 11:00:00',
			'duration'       => 2 * HOUR_IN_SECONDS,
			'hash'           => $occurrence->hash,
			'updated_at'     => $occurrence->updated_at,
		], $array_a_occurrence );

		$array_n_occurrence = Occurrence::where( 'post_id', '=', $event->ID )
		                                ->output( ARRAY_N )
		                                ->first();

		$this->assertArraySubset( [
			(string) $occurrence->occurrence_id,
			(string) $occurrence->event_id,
			(string) $event->ID,
			'2020-01-01 10:00:00',
			'2020-01-01 09:00:00',
			'2020-01-01 12:00:00',
			'2020-01-01 11:00:00',
			2 * HOUR_IN_SECONDS,
			$occurrence->hash,
			$occurrence->updated_at,
		], $array_n_occurrence );
	}

	/**
	 * It should allow getting all results in a generator
	 *
	 * @test
	 */
	public function should_allow_getting_all_results_in_a_generator() {
		global $wpdb;
		foreach ( range( 1, 10 ) as $i ) {
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
		$occurrences = Occurrence::limit( 11 )->get();
		$this->assertCount( 10, $occurrences );

		$all = Occurrence::order_by( 'start_date', 'ASC' )
		                 ->set_batch_size( 2 )
		                 ->all();

		$this->assertInstanceOf( Generator::class, $all );
		$wpdb_queries = $wpdb->num_queries;
		$results = iterator_to_array( $all, true );
		$this->assertEquals(
			6,
			$wpdb->num_queries - $wpdb_queries,
			'Should run 5 queries to fetch, 1 to find out there are no more results.'
		);
		$this->assertCount( 10, $results );
		$this->assertEquals(
			wp_list_pluck( $occurrences, 'occurrence_id' ),
			wp_list_pluck( $results, 'occurrence_id' )
		);

		$all = Occurrence::order_by( 'start_date', 'ASC' )
		                 ->offset( 5 )
		                 ->set_batch_size( 2 )
		                 ->all();
		$this->assertInstanceOf( Generator::class, $all );
		$wpdb_queries = $wpdb->num_queries;
		$results = iterator_to_array( $all, true );
		$this->assertEquals(
			3,
			$wpdb->num_queries - $wpdb_queries,
			'Should run 2 queries to fetch, 1 to find out there are no more results.'
		);
		$this->assertCount( 5, $results );
		$this->assertEquals(
			wp_list_pluck( array_slice( $occurrences, 5 ), 'occurrence_id' ),
			wp_list_pluck( $results, 'occurrence_id' )
		);

		$all = Occurrence::order_by( 'start_date', 'ASC' )
		                 ->limit( 5 )
		                 ->set_batch_size( 2 )
		                 ->all();
		$this->assertInstanceOf( Generator::class, $all );
		$wpdb_queries = $wpdb->num_queries;
		$results = iterator_to_array( $all, true );
		$this->assertEquals(
			3,
			$wpdb->num_queries - $wpdb_queries,
			'Should run 2 queries to fetch, 1 to find out there are no more results.'
		);
		$this->assertCount( 5, $results );
		$this->assertEquals(
			wp_list_pluck( array_slice( $occurrences, 0, 5 ), 'occurrence_id' ),
			wp_list_pluck( $results, 'occurrence_id' )
		);

		$all = Occurrence::order_by( 'start_date', 'ASC' )
		                 ->offset( 6 )
		                 ->limit( 5 )
		                 ->set_batch_size( 2 )
		                 ->all();
		$this->assertInstanceOf( Generator::class, $all );
		$wpdb_queries = $wpdb->num_queries;
		$results = iterator_to_array( $all, true );
		$this->assertEquals(
			3,
			$wpdb->num_queries - $wpdb_queries,
			'Should run 2 queries to fetch, 1 to find out there are no more results.'
		);
		$this->assertCount( 4, $results );
		$this->assertEquals(
			wp_list_pluck( array_slice( $occurrences, 6, 4 ), 'occurrence_id' ),
			wp_list_pluck( $results, 'occurrence_id' )
		);
	}

	/**
	 * It should allow plucking fields from results
	 *
	 * @test
	 */
	public function should_allow_plucking_fields_from_results() {
		$post_ids = [];
		for ( $i = 1; $i <= 7; $i ++ ) {
			$day = str_pad( $i, 2, '0', STR_PAD_LEFT );
			$post_id = tribe_events()->set_args( array(
				'title'      => 'Pluck test ' . $i,
				'status'     => 'publish',
				'start_date' => "2020-$day-01 10:00:00",
				'end_date'   => "2020-$day-01 12:00:00",
				'timezone'   => 'Europe/Paris',
			) )->create()->ID;
			$this->assertInstanceOf( Event::class, Event::find( $post_id, 'post_id' ) );
			$post_ids[] = $post_id;
		}

		global $wpdb;
		$events = Events::table_name( true );
		// Try out some fields.
		foreach ( [ 'start_date', 'end_date', 'timezone', 'updated_at' ] as $field ) {
			$expected = $wpdb->get_col( "SELECT $field FROM $events WHERE post_id IN (" . implode( ',', $post_ids ) . ")" );
			$plucked = Event::where_in( 'post_id', $post_ids )->pluck( $field );
			$this->assertEquals( $expected, $plucked );
		}
	}

	/**
	 * It should pluck null when the field is not defined in the table schema
	 *
	 * @test
	 */
	public function should_pluck_null_when_the_field_is_not_defined_in_the_table_schema() {
		$post_ids = [];
		for ( $i = 1; $i <= 7; $i ++ ) {
			$day = str_pad( $i, 2, '0', STR_PAD_LEFT );
			$post_id = tribe_events()->set_args( array(
				'title'      => 'Pluck test ' . $i,
				'status'     => 'publish',
				'start_date' => "2020-$day-01 10:00:00",
				'end_date'   => "2020-$day-01 12:00:00",
				'timezone'   => 'Europe/Paris',
			) )->create()->ID;
			$this->assertInstanceOf( Event::class, Event::find( $post_id, 'post_id' ) );
			$post_ids[] = $post_id;
		}

		$expected = array_fill( 0, 7, null );
		$plucked = Event::where_in( 'post_id', $post_ids )->pluck( 'non_existing_field' );
		$this->assertEquals( $expected, $plucked );
	}
}
