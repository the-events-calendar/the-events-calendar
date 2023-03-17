<?php

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Updates\Events;
use Tribe\Events\Test\Factories\Event as Event_Factory;

/**
 * Test that only past events are purged
 */
class Tribe__Events__Event_Cleaner_SchedulerTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Check to make sure that past events are being correctly selected.
	 *
	 * @test
	 */
	public function should_trash_old_events() {
		$event_id = ( new Event_Factory() )->create( [ 'when' => '2018-01-01' ] );
		$events   = new Events();
		$updated  = $events->update( $event_id );

		$this->assertTrue( $updated );
		$this->assertInstanceOf( Event::class, Event::find( $event_id, 'post_id' ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $event_id )->count() );
		$trashed_post = get_post( $event_id );
		$this->assertEquals( 'publish', $trashed_post->post_status );

		$upcoming_event_id = ( new Event_Factory() )->create( [ 'when' => date( 'Y-m-d' ) ] );
		$updated           = $events->update( $upcoming_event_id );
		$this->assertTrue( $updated );
		$this->assertInstanceOf( Event::class, Event::find( $upcoming_event_id, 'post_id' ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $upcoming_event_id )->count() );

		$cleaner            = new Tribe__Events__Event_Cleaner_Scheduler();
		$event_ids_to_purge = $cleaner->select_events_to_purge( 3 );

		$this->assertContains( $event_id, $event_ids_to_purge, 'Past events should be selected' );
		$this->assertNotContains( $upcoming_event_id, $event_ids_to_purge, 'Upcoming events should never be selected' );

		// Should be trashed
		$results = $cleaner->move_old_events_to_trash();
		$this->assertIsArray( $results );
		$this->assertCount( 1, $results );
		$this->assertInstanceOf( WP_Post::class, $results[$event_id] );
		$this->assertEquals( $event_id, $results[$event_id]->ID );

		// Should still have occurrence, but the post should be trashed
		$this->assertInstanceOf( Event::class, Event::find( $event_id, 'post_id' ) );
		$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $event_id )->count() );

		// The post should be trashed now.
		$trashed_post = get_post( $event_id );
		$this->assertEquals( 'trash', $trashed_post->post_status );

		// Default is to delete posts 30 days old trash, let's modify the meta to flag this ready to remove
		$meta_value = time() - ((EMPTY_TRASH_DAYS * DAY_IN_SECONDS) + 1);
		update_post_meta($event_id, '_wp_trash_meta_time', $meta_value);
		wp_scheduled_delete();

		$trashed_post = get_post( $event_id );
		$this->assertEmpty($trashed_post);

		// Should be gone now
		$this->assertNull( Event::find( $event_id, 'post_id' ) );
		$this->assertEquals( 0, Occurrence::where( 'post_id', '=', $event_id )->count() );
	}
}