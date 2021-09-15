<?php

namespace Tribe\Events\Aggregator\Processes;

use Tribe\Events\Test\Testcases\Aggregator\V1\Aggregator_TestCase;
use Tribe__Events__Aggregator__Processes__Import_Events as Import_Events;
use Tribe__Events__Aggregator__Record__Activity as Activity;
use Tribe__Events__Aggregator__Record__iCal as Record;
use Tribe__Events__Main as Main;

class Import_EventsTest extends Aggregator_TestCase {

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Import_Events::class, $sut );
	}

	/**
	 * @return Import_Events
	 */
	private function make_instance() {
		return new Import_Events();
	}

	/**
	 * Leveraging PHP 7.0+ anonymous classes and method opening
	 * extend the `Import_Events` with one that "opens" the `complete`
	 * method making it public.
	 */
	private function make_instance_opening_complete() {
		$opening = new class extends Import_Events {
			public function complete() {
				return parent::complete();
			}
		};

		return new $opening;
	}

	/**
	 * It should create the event if the data does not contain linked posts
	 *
	 * @test
	 */
	public function should_create_the_event_if_the_data_does_not_contain_linked_posts() {
		$item = $this->factory()->import_record->create_and_get_event_data();
		unset( $item->venue, $item->organizer );
		$record = new Record();
		$record->create( 'manual', [], [ 'origin' => 'ical', 'post_status' => 'draft' ] );
		$data = [
			'record_id'       => $record->id,
			'data'            => (array) $item,
			'transitional_id' => 'foo-bar',
			'user_id'         => $this->factory()->user->create(),
		];

		$sut = $this->make_instance();
		$sut->push_to_queue( $data );
		$sut->save();
		$result = $sut->sync_process();

		/** @var Activity $activity */
		$activity = $result[0];
		$this->assertInstanceOf( Activity::class, $activity );
		$this->assertEquals( 1, $activity->count( Main::POSTTYPE ) );
		$event_id = $activity->get( Main::POSTTYPE, 'created' )[0];
		$this->assertEmpty( get_post_meta( $event_id, $sut->get_transitional_meta_key() ) );
	}

	/**
	 * It should not insert the event if the event is dependent on a linked post
	 *
	 * @test
	 */
	public function should_not_insert_the_event_if_the_event_is_dependent_on_a_linked_post() {
		$item             = $this->factory()->import_record->create_and_get_event_data();
		$item->depends_on = [ 'some-linked-post-global-id' ];
		$record           = new Record();
		$record->create( 'manual', [], [ 'origin' => 'ical', 'post_status' => 'draft' ] );
		$data = [
			'record_id'       => $record->id,
			'data'            => (array) $item,
			'transitional_id' => 'foo-bar',
			'user_id'         => $this->factory()->user->create(),
		];

		$sut = $this->make_instance();
		$sut->push_to_queue( $data );
		$sut->save();
		$result = $sut->sync_process();

		$data['requeued'] = 1;
		$this->assertEquals( $data, $result[0] );
	}

	/**
	 * It should set the transitional meta on a venue when inserting non dependent event
	 *
	 * @test
	 */
	public function should_set_the_transitional_meta_on_a_venue_when_inserting_non_dependent_event() {
		$item   = $this->factory()->import_record->create_and_get_event_data();
		$record = new Record();
		$record->create( 'manual', [], [ 'origin' => 'ical', 'post_status' => 'draft' ] );
		$data = [
			'record_id'       => $record->id,
			'data'            => (array) $item,
			'transitional_id' => 'foo-bar',
			'user_id'         => $this->factory()->user->create(),
		];

		$sut = $this->make_instance();
		$sut->push_to_queue( $data );
		$sut->save();
		$result = $sut->sync_process();

		/** @var Activity $activity */
		$activity = $result[0];
		$this->assertInstanceOf( Activity::class, $activity );
		$this->assertEquals( 1, $activity->count( Main::POSTTYPE ) );
		$event_id = $activity->get( Main::POSTTYPE, 'created' )[0];
		$this->assertEmpty( get_post_meta( $event_id, $sut->get_transitional_meta_key() ) );
		$venue_id = get_post_meta( $event_id, '_EventVenueID', true );
		$this->assertEquals( $item->venue->global_id, get_post_meta( $venue_id, $sut->get_transitional_meta_key(), true ) );
	}

	/**
	 * It should set the transitional meta on organizers when inserting non dependent events
	 *
	 * @test
	 */
	public function should_set_the_transitional_meta_on_organizers_when_inserting_non_dependent_events() {
		$item   = $this->factory()->import_record->create_and_get_event_data();
		$record = new Record();
		$record->create( 'manual', [], [ 'origin' => 'ical', 'post_status' => 'draft' ] );
		$data = [
			'record_id'       => $record->id,
			'data'            => (array) $item,
			'transitional_id' => 'foo-bar',
			'user_id'         => $this->factory()->user->create(),
		];

		$sut = $this->make_instance();
		$sut->push_to_queue( $data );
		$sut->save();
		$result = $sut->sync_process();

		/** @var Activity $activity */
		$activity = $result[0];
		$this->assertInstanceOf( Activity::class, $activity );
		$this->assertEquals( 1, $activity->count( Main::POSTTYPE ) );
		$event_id = $activity->get( Main::POSTTYPE, 'created' )[0];
		$this->assertEmpty( get_post_meta( $event_id, $sut->get_transitional_meta_key() ) );
		$organizer_ids = get_post_meta( $event_id, '_EventOrganizerID' );
		$i             = 0;
		foreach ( $organizer_ids as $organizer_id ) {
			$this->assertEquals( $item->organizer[ $i ]->global_id, get_post_meta( $organizer_id, $sut->get_transitional_meta_key(), true ) );
			$i ++;
		}
	}

	/**
	 * It should insert dependent items when their dependencies are in DB
	 *
	 * @test
	 */
	public function should_insert_dependent_items_when_their_dependencies_are_in_db() {
		$item                  = $this->factory()->import_record->create_and_get_event_data();
		$sut                   = $this->make_instance();
		$transitional_meta_key = $sut->get_transitional_meta_key( 'foo-bar' );
		$venue_id              = $this->factory()->venue->create( [ 'meta_input' => [ $transitional_meta_key => 'venue-global-id' ] ] );
		$organizer_1_id        = $this->factory()->organizer->create( [ 'meta_input' => [ $transitional_meta_key => 'org-1-global-id' ] ] );
		$organizer_2_id        = $this->factory()->organizer->create( [ 'meta_input' => [ $transitional_meta_key => 'org-2-global-id' ] ] );
		$item->depends_on      = [
			'venue-global-id',
			'org-1-global-id',
			'org-2-global-id',
		];
		$record                = new Record();
		$record->create( 'manual', [], [ 'origin' => 'ical', 'post_status' => 'draft' ] );
		$data = [
			'record_id'       => $record->id,
			'data'            => (array) $item,
			'transitional_id' => 'foo-bar',
			'user_id'         => $this->factory()->user->create(),
		];

		$sut->push_to_queue( $data );
		$sut->save();
		$result = $sut->sync_process();

		/** @var Activity $activity */
		$activity = $result[0];
		$this->assertInstanceOf( Activity::class, $activity );
		$this->assertEquals( 1, $activity->count( Main::POSTTYPE ) );
		$event_id = $activity->get( Main::POSTTYPE, 'created' )[0];
		$this->assertEmpty( get_post_meta( $event_id, $transitional_meta_key ) );
		$this->assertEquals( $venue_id, get_post_meta( $event_id, '_EventVenueID', true ) );
		$this->assertEqualSets( [ $organizer_1_id, $organizer_2_id ], get_post_meta( $event_id, '_EventOrganizerID' ) );
	}

	/**
	 * It should remove the transitional meta from all posts when complete
	 *
	 * @test
	 */
	public function should_remove_the_transitional_meta_from_all_posts_when_complete() {
		$sut = $this->make_instance_opening_complete();
		$sut->set_transitional_id( 'foo-bar' );
		$transitional_meta_key = $sut->get_transitional_meta_key( 'foo-bar' );
		$venue_id              = $this->factory()->venue->create( [ 'meta_input' => [ $transitional_meta_key => 'venue-global-id' ] ] );
		$organizer_1_id        = $this->factory()->organizer->create( [ 'meta_input' => [ $transitional_meta_key => 'org-1-global-id' ] ] );
		$organizer_2_id        = $this->factory()->organizer->create( [ 'meta_input' => [ $transitional_meta_key => 'org-2-global-id' ] ] );
		$ids                   = [ $venue_id, $organizer_1_id, $organizer_2_id ];
		foreach ( $ids as $id ) {
			$this->assertNotEmpty( get_post_meta( $id, $transitional_meta_key ) );
		}

		$sut->complete();

		foreach ( $ids as $id ) {
			wp_cache_delete( $id, 'post_meta' );
			$meta = get_post_meta( $id, $transitional_meta_key );
			$this->assertEmpty( $meta );
		}
	}

	/**
	 * It should skip the import of an event that generates an exception
	 *
	 * @test
	 */
	public function should_skip_the_import_of_an_event_that_generates_an_exception() {
		$this->markTestSkipped( "Skipping due ot some Exception problem not being handled properly." );

		$item = $this->factory()->import_record->create_and_get_event_data();
		unset( $item->venue, $item->organizer );
		$record = new Record();
		$record->create( 'manual', [], [ 'origin' => 'ical', 'post_status' => 'draft' ] );
		$data = [
			'record_id'       => $record->id,
			'data'            => (array) $item,
			'transitional_id' => 'foo-bar',
			'user_id'         => $this->factory()->user->create(),
		];
		add_filter( 'tribe_aggregator_async_insert_event', function () {
			throw new \RuntimeException( 'Something happened while importing the event' );
		} );

		$sut = $this->make_instance();
		$sut->push_to_queue( $data );
		$sut->save();
		$result = $sut->sync_process();

		/** @var Activity $activity */
		$activity = $result[0];
		$this->assertInstanceOf( Activity::class, $activity );
		$this->assertEquals( 1, $activity->count( Main::POSTTYPE ) );
		$skipped_event_identifier = $activity->get( Main::POSTTYPE, 'skipped' )[0];
		$this->assertEquals( $item->global_id, $skipped_event_identifier );
	}

	/**
	 * It should complete in clear state on first run
	 *
	 * Here we check that, upon completion, the queue will properly set/unset
	 * queue-related flags on the Record.
	 *
	 * @test
	 */
	public function should_complete_in_clear_state_on_first_run() {
		$item = $this->factory()->import_record->create_and_get_event_data();
		unset( $item->venue, $item->organizer );
		$record = new Record();
		$record->create( 'manual', [], [ 'origin' => 'ical', 'post_status' => 'draft' ] );
		// let's set, on the record, the flags the queue processor would set
		$record->meta['in_progress'] = true;
		$record->meta['queue']       = 'something';
		/** @var Import_Events $sut */
		$sut = $this->make_instance_opening_complete();
		$sut->set_record_id( $record->id );
		$sut->save();

		$sut->complete();

		$this->assertEmpty(
			get_post_meta( Record::$meta_key_prefix . 'in_progress', true ),
			'After completion the Record `in_progress` flag should be unset.'
		);
		$this->assertEmpty(
			get_post_meta( Record::$meta_key_prefix . 'queue', true ),
			'After completion the Record `queue` flag should be unset.'
		);

		/**
		 * Mind that we do NOT check on the queue status.done key, in the transient, after
		 * completion as "completed", from the point of view of the queue, means just that
		 * it's done processing either because items have been all processed or because
		 * the user interrupted the processing.
		 */
	}
}
