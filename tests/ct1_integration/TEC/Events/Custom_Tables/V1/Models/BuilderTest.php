<?php

namespace TEC\Events\Custom_Tables\V1\Models;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
use WP_Post;

class BuilderTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;
	use CT1_Fixtures;

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
		$this->assertEquals(0,$upserted);
	}

	/**
	 * @test
	 */
	public function should_handle_multiple_order_bys() {
		// Should see start_date ASC and end_date_utc DESC, in that order.
		$sql  = Occurrence::order_by( 'start_date' )
		                  ->order_by( 'end_date_utc', 'DESC' )
		                  ->get_sql();

		$this->assertMatchesSnapshot( $sql );

		// Should only see end_date_utc DESC.
		$sql  = Occurrence::order_by( 'end_date_utc', 'DESC' )
		                  ->get_sql();
		$this->assertMatchesSnapshot( $sql );
	}
}
