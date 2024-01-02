<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use Tribe__Events__Main;
use WP_Post;

class BuilderTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;
	use CT1_Fixtures;

	public function report_db_errors_queries_data_provider(): array {
		return [
			'find()'   => [ 'find', [ 1 ] ],
			'upsert()' => [
				'upsert',
				[
					[ 'occurrence_id' ],
					[
						'occurrence_id' => 123,
						'title'         => 'test',
						'status'        => 'publish',
						'start_date'    => '2020-01-22 10:00:00',
						'end_date'      => "2020-01-22 12:00:00",
						'timezone'      => 'Europe/Paris',
					]
				]
			],
			'update()' => [
				'update',
				[
					[
						'occurrence_id' => 123,
						'title'         => 'test',
						'status'        => 'publish',
						'start_date'    => '2020-01-22 10:00:00',
						'end_date'      => "2020-01-22 12:00:00",
						'timezone'      => 'Europe/Paris',
					]
				]
			],
			'insert()' => [
				'insert',
				[
					[
						'title'          => 'test report_db_errors_queries',
						'status'         => 'publish',
						'start_date'     => '2020-01-22 11:00:00',
						'end_date'       => "2020-01-22 13:00:00",
						'start_date_utc' => '2020-01-22 10:00:00',
						'end_date_utc'   => "2020-01-22 12:00:00",
						'timezone'       => 'Europe/Paris',
						'duration'       => 7200,
						'event_id'       => 123,
						'hash'           => '123hashbrowns321'
					]
				]
			]
		];
	}

	/**
	 * @dataProvider  report_db_errors_queries_data_provider
	 * @test
	 */
	public function should_report_db_errors( $func, $args ) {
		global $wpdb;
		$event = tribe_events()->set_args( [
			                                   'title'      => 'test',
			                                   'status'     => 'publish',
			                                   'start_date' => '2020-01-22 10:00:00',
			                                   'end_date'   => "2020-01-22 12:00:00",
			                                   'timezone'   => 'Europe/Paris',
		                                   ] )->create();
		// Add post ID for insert.
		if ( $func === 'insert' ) {
			$args[0]['post_id'] = $event->ID;
		}
		$occurrences_table = Occurrences::table_name();

		$q = "DROP /* WPTestCase will replace this if it matches substr()...*/ TABLE IF EXISTS `$occurrences_table`;";
		$wpdb->query( $q );
		tribe_cache()->reset();

		$found = false;
		// Validate our error shows...
		add_action( 'tribe_log', function ( $type, $message, $context ) use ( $occurrences_table, &$found ) {
			if ( $message === 'Builder: query failure.'
			     && isset( $context['error'] )
			     && stripos( $context['error'], "Table 'test.$occurrences_table' doesn't exist" ) !== false ) {
				$found = true;
			}
		}, 10, 3 );

		// Database error Unknown table
		$occurrence = Occurrence::$func( ...$args );

		$this->assertEmpty( $occurrence, 'Should not be a successful query.' );
		$this->assertTrue( $found, 'Did not trigger the expected error.' );

		tribe( Schema_Builder::class )->up( true );
	}

	/**
	 * Validate chained where clauses that feed into a delete(), will invalidate cache.
	 *
	 * @test
	 */
	public function should_expire_cache_on_delete() {
		$post_a = tribe_events()->set_args( [
			'title'      => 'test',
			'status'     => 'publish',
			'start_date' => '2020-01-22 10:00:00',
			'end_date'   => "2020-01-22 12:00:00",
			'timezone'   => 'Europe/Paris',
		] )->create();
		$post_b = tribe_events()->set_args( [
			'title'      => 'test',
			'status'     => 'publish',
			'start_date' => '2020-01-22 10:00:00',
			'end_date'   => "2020-01-22 12:00:00",
			'timezone'   => 'Europe/Paris',
		] )->create();
		// Prime internal cache
		$event = Event::find( $post_a->ID, 'post_id' );
		$this->assertTrue( $event instanceof Event );
		// Validate the internal cache is cleared on delete
		Event::where( 'post_id', '=', $post_a->ID )->delete();
		$event = Event::find( $post_a->ID, 'post_id' );
		$this->assertNull( $event );

		// Prime internal cache
		$event = Event::find( $post_b->ID, 'post_id' );
		$this->assertTrue( $event instanceof Event );
		// Validate the internal cache is cleared on delete with multiple params.
		Event::where( 'post_id', '=', $post_b->ID )
		     ->where( 'start_date', '=', '2020-01-22 10:00:00' )
		     ->delete();
		$event = Event::find( $post_b->ID, 'post_id' );
		$this->assertNull( $event );
	}

	/**
	 * Validate static upserts() will invalidate cache.
	 *
	 * @test
	 */
	public function should_expire_cache_on_upsert() {
		$start_a = "2020-01-22 10:00:00";
		$start_b = "2020-01-22 11:00:00";
		$start_c = "2020-01-22 10:30:00";
		$post    = tribe_events()->set_args( [
			'title'      => 'test should_expire_cache_on_upsert',
			'status'     => 'publish',
			'start_date' => $start_a,
			'end_date'   => "2020-01-22 12:00:00",
			'timezone'   => 'Europe/Paris',
		] )->create();
		$event   = Event::find( $post->ID, 'post_id' );
		$this->assertTrue( $event instanceof Event );
		$this->assertEquals( $start_a, $event->start_date );
		Event::upsert( [ 'post_id' ], [ 'start_date' => $start_b, 'post_id' => $post->ID ] );
		$event = Event::find( $post->ID, 'post_id' );
		$this->assertTrue( $event instanceof Event );
		$this->assertEquals( $start_b, $event->start_date );
		Event::upsert( [ 'post_id' ], [ 'start_date' => $start_c, 'post_id' => $post->ID ] );
		$event = Event::find( $post->ID, 'post_id' );
		$this->assertTrue( $event instanceof Event );
		$this->assertEquals( $start_c, $event->start_date );
	}

	/**
	 * Ensures cache key generation.
	 *
	 * @test
	 */
	public function should_generate_cache_key() {
		foreach ( range( 1, 4 ) as $i ) {
			$event    = tribe_events()->set_args( [
				'title'      => 'test',
				'status'     => 'publish',
				'start_date' => "2020-01-$i 10:00:00",
				'end_date'   => "2020-01-$i 12:00:00",
				'timezone'   => 'Europe/Paris',
			] )->create();
			$events[] = $event;
		}

		foreach ( $events as $post ) {
			// Will generate a cache key during find().
			$occurrence = Occurrence::find( $post->ID, 'post_id' );
			// Should look like this.
			$key = 'post_id' . serialize( $post->ID ) . get_class( $occurrence );

			// Make sure the cache key is in the expected format.
			$this->assertEquals( $key, Builder::generate_cache_key( $occurrence, 'post_id', (int) $post->ID ) );
			// Make sure the key is what this instances is memoized by.
			$this->assertEquals( $key, $occurrence->cache_key );
		}

		$empty = new Occurrence();
		$this->assertNull( $empty->cache_key );
	}

	/**
	 * Should validate whether our simple find() memoization works as expected, and is cleared on save.
	 *
	 * @throws \Tribe__Repository__Usage_Error
	 * @test
	 */
	public function should_memoize_find() {
		$event1 = tribe_events()->set_args( [
			'title'      => "Faux Event",
			'start_date' => "2021-01-01 08:00:00",
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$event2 = tribe_events()->set_args( [
			'title'      => "Faux Event",
			'start_date' => "2021-01-05 08:00:00",
			'duration'   => 2 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();

		// Sanity checks / givens.
		$this->assertInstanceOf( WP_Post::class, $event1 );
		$this->assertInstanceOf( WP_Post::class, $event2 );
		$occurrence1 = Occurrence::where( 'post_id', '=', $event1->ID )->first();
		$occurrence2 = Occurrence::where( 'post_id', '=', $event2->ID )->first();
		$this->assertInstanceOf( Occurrence::class, $occurrence1 );
		$this->assertInstanceOf( Occurrence::class, $occurrence2 );

		// Test memoization / cache management.
		// First instance
		$query1_hit = false;
		add_filter( 'query', function ( $query ) use ( &$query1_hit, $occurrence1 ) {
			if ( stripos( $query, Occurrences::table_name() ) && stripos( $query, (string) $occurrence1->occurrence_id ) ) {
				$query1_hit = true;
			}

			return $query;
		} );
		$o1 = Occurrence::find( $occurrence1->occurrence_id );
		$this->assertInstanceOf( Occurrence::class, $o1 );
		$this->assertTrue( $query1_hit, 'Should hit query first time.' );
		$query1_hit = false;
		$o1         = Occurrence::find( $occurrence1->occurrence_id );
		$this->assertInstanceOf( Occurrence::class, $o1 );
		$this->assertFalse( $query1_hit, 'Should not hit query second time.' );

		// Second instance
		$query2_hit = false;
		add_filter( 'query', function ( $query ) use ( &$query2_hit, $occurrence2 ) {
			if ( stripos( $query, Occurrences::table_name() ) && stripos( $query, (string) $occurrence2->occurrence_id ) ) {
				$query2_hit = true;
			}

			return $query;
		} );
		$o2 = Occurrence::find( $occurrence2->occurrence_id );
		$this->assertInstanceOf( Occurrence::class, $o2 );
		$this->assertTrue( $query2_hit, 'Should hit query first time.' );
		$query2_hit = false;
		$o2         = Occurrence::find( $occurrence2->occurrence_id );
		$this->assertInstanceOf( Occurrence::class, $o2 );
		$this->assertFalse( $query2_hit, 'Should not hit query second time.' );

		// This will clear memoized occurrences.
		wp_insert_post( [
			'post_title'  => 'A test post',
			'post_status' => 'publish',
			'post_type'   => Tribe__Events__Main::POSTTYPE
		] );

		// Now validate the query runs again.
		$query2_hit = false;
		$o2         = Occurrence::find( $occurrence2->occurrence_id );
		$this->assertInstanceOf( Occurrence::class, $o2 );
		$this->assertTrue( $query2_hit, 'Should hit query - should have cleared memoized values after post save.' );

		$query1_hit = false;
		$o1         = Occurrence::find( $occurrence1->occurrence_id );
		$this->assertInstanceOf( Occurrence::class, $o1 );
		$this->assertTrue( $query1_hit, 'Should hit query - should have cleared memoized values after post save.' );
	}

	/**
	 * It should correctly upsert sets of models
	 *
	 * @test
	 */
	public function should_correctly_upsert_sets_of_models() {
		$occurrences = [];
		foreach ( range( 1, 6 ) as $i ) {
			$event = tribe_events()->set_args( [
				'title'      => "Event $i",
				'start_date' => "2021-01-0$i 08:00:00",
				'duration'   => 2 * HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )->create();
			$this->assertInstanceOf( WP_Post::class, $event );
			$occurrence = Occurrence::where( 'post_id', '=', $event->ID )->first();
			$this->assertInstanceOf( Occurrence::class, $occurrence );
			$occurrences[] = $occurrence;
		}

		array_walk( $occurrences, static function ( Occurrence $occurrence ) {
			$occurrence->start_date = '2022-02-02 09:00:00';
			$occurrence->end_date   = '2022-02-02 11:00:00';
		} );
		$upserted = Occurrence::set_batch_size( 2 )->upsert_set( $occurrences );
		$this->assertEquals( 6, $upserted, 'All Occurrences should have been updated' );
		$this->assertEquals(
			array_fill( 0, 6, '2022-02-02 09:00:00' ),
			wp_list_pluck( Occurrence::get(), 'start_date' ),
			'All Occurrences should have the new start_date'
		);
	}

	/**
	 * It should correctly upsert a set of models in array format
	 *
	 * @test
	 */
	public function should_correctly_upsert_a_set_of_models_in_array_format() {
		$occurrences = [];
		foreach ( range( 1, 6 ) as $i ) {
			$event = tribe_events()->set_args( [
				'title'      => "Event $i",
				'start_date' => "2021-01-0$i 08:00:00",
				'duration'   => 2 * HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )->create();
			$this->assertInstanceOf( WP_Post::class, $event );
			$occurrence = Occurrence::where( 'post_id', '=', $event->ID )->first();
			$this->assertInstanceOf( Occurrence::class, $occurrence );
			$occurrences[] = $occurrence->to_array();
		}

		array_walk( $occurrences, static function ( &$occurrence ) {
			$occurrence['start_date'] = '2022-02-02 09:00:00';
			$occurrence['end_date']   = '2022-02-02 11:00:00';
		} );
		$upserted = Occurrence::set_batch_size( 2 )->upsert_set( $occurrences );
		$this->assertEquals( 6, $upserted, 'All Occurrences should have been updated' );
		$this->assertEquals(
			array_fill( 0, 6, '2022-02-02 09:00:00' ),
			wp_list_pluck( Occurrence::get(), 'start_date' ),
			'All Occurrences should have the new start_date'
		);
	}

	/**
	 * It should allow upserting an empty set
	 *
	 * @test
	 */
	public function should_allow_upserting_an_empty_set() {
		$upserted = Occurrence::set_batch_size( 2 )->upsert_set( [] );
		$this->assertEquals( 0, $upserted );
	}

	/**
	 * @test
	 */
	public function should_handle_multiple_order_bys() {
		// Should see start_date ASC and end_date_utc DESC, in that order.
		$sql = Occurrence::order_by( 'start_date' )
		                 ->order_by( 'end_date_utc', 'DESC' )
		                 ->get_sql();

		$this->assertMatchesSnapshot( $sql );

		// Should only see end_date_utc DESC.
		$sql = Occurrence::order_by( 'end_date_utc', 'DESC' )
		                 ->get_sql();
		$this->assertMatchesSnapshot( $sql );
	}
}
