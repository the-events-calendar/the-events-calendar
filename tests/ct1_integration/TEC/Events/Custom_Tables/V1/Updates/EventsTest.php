<?php

namespace TEC\Events\Custom_Tables\V1\Updates;

use Codeception\TestCase\WPTestCase;
use RuntimeException;
use TEC\Events\Custom_Tables\V1\Models\Builder;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Factories\Event as Event_Factory;
use Tribe\Tests\Traits\With_Log_Recording;
use Tribe\Tests\Traits\With_Uopz;
use WP_Post;

class EventsTest extends WPTestCase {
	use With_Uopz;
	use With_Log_Recording;

	/**
	 * It should not update a non-Event post
	 *
	 * @test
	 */
	public function should_not_update_a_non_event_post() {
		$post_id = static::factory()->post->create();

		$events  = new Events();
		$updated = $events->update( $post_id );

		$this->assertFalse( $updated );
	}

	/**
	 * It should not update if event data is incomplete
	 *
	 * @test
	 */
	public function should_not_update_if_event_data_is_incomplete() {
		$event_id = ( new Event_Factory() )->create();

		add_filter( 'tec_events_custom_tables_v1_event_data_from_post', static function ( $data, $id ) use ( $event_id ) {
			if ( $event_id === $id ) {
				unset( $data['start_date'] );
			}

			return $data;
		}, 10, 2 );

		$events = new Events;
		$updated = $events->update( $event_id );

		$this->assertFalse( $updated );
	}

	/**
	 * It should not update if Event upsertion fails
	 *
	 * @test
	 */
	public function should_not_update_if_event_upsertion_fails() {
		$this->set_class_fn_return( Builder::class, 'upsert', false );
		$event_id = ( new Event_Factory() )->create();

		$events  = new Events();
		$updated = $events->update( $event_id );

		$this->assertFalse( $updated );
		$this->assertCount( 0, $this->get_log_records() );
	}

	/**
	 * It should not update if Event cannot be found in custom tables after upsertion
	 *
	 * @test
	 */
	public function should_not_update_if_event_cannot_be_found_in_custom_tables_after_upsertion() {
		// Return `true`, as if the Event was actually upserted, but nothing will be done.
		$this->set_class_fn_return( Builder::class, 'upsert', true );
		// The Event will not be found.
		$this->set_class_fn_return( Builder::class, 'find', null );
		$event_id = ( new Event_Factory() )->create();

		$events  = new Events();
		$updated = $events->update( $event_id );

		$this->assertFalse( $updated );
		$this->assertCount( 1, $this->get_log_records() );
		$record = $this->get_log_record( 0 );
		$this->assertEquals( $event_id, $record['context']['post_id'] );
		$this->assertEquals( Events::class, $record['context']['source'] );
		$this->assertEquals( 'fetch-after-upsert', $record['context']['slug'] );
	}

	/**
	 * It should not update if Occurrences update fails
	 *
	 * @test
	 */
	public function should_not_update_if_occurrences_update_fails() {
		$event_id = ( new Event_Factory() )->create();
		add_action( 'tec_events_custom_tables_v1_after_save_occurrences', static function () {
			throw new RuntimeException( 'Something failed.' );
		} );

		$events  = new Events();
		$updated = $events->update( $event_id );

		$this->assertFalse( $updated );
		$this->assertCount( 1, $this->get_log_records() );
		$record = $this->get_log_record( 0 );
		$this->assertEquals( $event_id, $record['context']['post_id'] );
		$this->assertEquals( Events::class, $record['context']['source'] );
		$this->assertEquals( 'update-occurrences', $record['context']['slug'] );
	}

	/**
	 * It should update Events correctly
	 *
	 * @test
	 */
	public function should_update_events_correctly() {
		$event_id = ( new Event_Factory() )->create();

		$events  = new Events();
		$updated = $events->update( $event_id );

		$this->assertTrue( $updated );
		$this->assertCount( 0, $this->get_log_records() );
		$this->assertInstanceOf( Event::class, Event::find( $event_id, 'post_id' ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $event_id )->count() );
	}

	/**
	 * It should not delete if Event or Occurrence deletion fails
	 *
	 * @test
	 */
	public function should_not_delete_if_event_or_occurrence_deletion_fails() {
		// Builder::delete calls for Events and Occurrences will return 0.
		$this->set_class_fn_return( Builder::class, 'delete', 0 );
		$event_id = ( new Event_Factory() )->create();

		$events   = new Events();
		$affected = $events->delete( $event_id );

		$this->assertEquals( 0, $affected );
		$this->assertCount( 0, $this->get_log_records() );
	}

	/**
	 * It should delete correctly
	 *
	 * @test
	 */
	public function should_delete_correctly() {
		// Builder::delete calls for Events and Occurrences will return 1.
		$this->set_class_fn_return( Builder::class, 'delete', 1 );
		$event_id = ( new Event_Factory() )->create();

		$events   = new Events();
		$affected = $events->delete( $event_id );

		$this->assertEquals( 2, $affected );
		$this->assertCount( 0, $this->get_log_records() );
	}

	/**
	 * It should not update know range when no Occurrences are found in database
	 *
	 * @test
	 */
	public function should_not_update_know_range_when_no_occurrences_are_found_in_database() {
		Occurrence::where( 'post_id', '!=', 0 )->delete();
		$this->assertEquals( 0, Occurrence::where( 'post_id', '!=', 0 )->count() );

		$events  = new Events();
		$updated = $events->rebuild_known_range();

		$this->assertContains( wp_date( 'Y-m-d' ), tribe_get_option( 'earliest_date' ) );
		$this->assertContains( wp_date( 'Y-m-d' ), tribe_get_option( 'latest_date' ) );
		$this->assertTrue( $updated );
	}

	/**
	 * It should update known range when only one Occurrence is found in database
	 *
	 * @test
	 */
	public function should_update_known_range_when_only_one_occurrence_is_found_in_database() {
		Occurrence::where( 'post_id', '!=', 0 )->delete();
		$this->assertEquals( 0, Occurrence::where( 'post_id', '!=', 0 )->count() );
		$post = tribe_events()->set_args( [
			'title'      => 'First',
			'start_date' => '2020-01-01 08:00:00',
			'end_date'   => '2020-01-01 12:00:00',
			'status'     => 'publish',
		] )->create();
		$this->assertInstanceOf( WP_Post::class, $post );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );

		$events  = new Events();
		$updated = $events->rebuild_known_range();

		$this->assertTrue( $updated );
		$this->assertEquals( '2020-01-01 08:00:00', tribe_get_option( 'earliest_date' ) );
		$this->assertEquals( '2020-01-01 12:00:00', tribe_get_option( 'latest_date' ) );
	}

	/**
	 * It should update known range when there are many events
	 *
	 * @test
	 */
	public function should_update_known_range_when_there_are_many_events() {
		Occurrence::where( 'post_id', '!=', 0 )->delete();
		$this->assertEquals( 0, Occurrence::where( 'post_id', '!=', 0 )->count() );
		foreach (
			[
				[ '2020-01-01 08:00:00', '2020-01-01 12:00:00' ],
				[ '2020-02-01 08:00:00', '2020-02-01 12:00:00' ],
				[ '2020-03-01 08:00:00', '2020-03-01 12:00:00' ],
			] as list(
			$start_date, $end_date
		)
		) {
			$post = tribe_events()->set_args( [
				'title'      => 'First',
				'start_date' => $start_date,
				'end_date'   => $end_date,
				'status'     => 'publish',
			] )->create();
			$this->assertInstanceOf( WP_Post::class, $post );
			$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $post->ID )->count() );
		}

		$events  = new Events();
		$updated = $events->rebuild_known_range();

		$this->assertTrue( $updated );
		$this->assertEquals( '2020-01-01 08:00:00', tribe_get_option( 'earliest_date' ) );
		$this->assertEquals( '2020-03-01 12:00:00', tribe_get_option( 'latest_date' ) );
	}


	/**
	 * It should correctly fetch earliest and latest dates
	 *
	 * @test
	 */
	public function should_correctly_fetch_earliest_and_latest_dates() {
		$events = new Events();

		$this->assertEquals( wp_date( 'Y-m-d' ), $events->get_earliest_date()->format( 'Y-m-d' ) );
		$this->assertEquals( wp_date( 'Y-m-d' ), $events->get_latest_date()->format( 'Y-m-d' ) );

		$this->given_an_event_with_status_and_dates( 'publish', '2020-01-01 08:00:00', '2020-01-01 12:00:00' );
		$this->given_an_event_with_status_and_dates( 'private', '2021-01-01 08:00:00', '2021-01-01 12:00:00' );
		$this->given_an_event_with_status_and_dates( 'draft', '2022-01-01 08:00:00', '2022-01-01 12:00:00' );
		$this->given_an_event_with_status_and_dates( 'protected', '2023-01-01 08:00:00', '2023-01-01 12:00:00' );

		$this->assertEquals( '2020-01-01 08:00:00', $events->get_earliest_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2023-01-01 12:00:00', $events->get_latest_date()->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-01-01 08:00:00', $events->get_earliest_date( 'publish' )->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-01-01 12:00:00', $events->get_latest_date( 'publish' )->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2020-01-01 08:00:00', $events->get_earliest_date( [ 'publish','draft' ] )->format( 'Y-m-d H:i:s' ) );
		$this->assertEquals( '2022-01-01 12:00:00', $events->get_latest_date( [
			'publish',
			'draft'
		] )->format( 'Y-m-d H:i:s' ) );
	}

	private function given_an_event_with_status_and_dates( $post_status, $start_date, $end_date ) {
		$published = tribe_events()->set_args( [
			'title'      => 'Test',
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'status'     => $post_status,
		] )->create();
		$this->assertInstanceOf( WP_Post::class, $published );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $published->ID )->count() );
	}
}
