<?php
namespace TEC\Events\Custom_Tables\V1\Updates;

use Tribe\Events\Test\Factories\Event as Event_Factory;
use Tribe__Events__Main as TEC;

class Meta_WatcherTest extends \Codeception\TestCase\WPTestCase
{
	/**
	 * It should return no ids if nothing has been tracked
	 *
	 * @test
	 */
	public function should_return_no_ids_if_nothing_has_been_tracked() {
		$meta_watcher = new Meta_Watcher();

		$this->assertEmpty($meta_watcher->get_marked_ids());
	}

	/**
	 * It should not mark for update a non Event post
	 *
	 * @test
	 */
	public function should_not_mark_for_update_a_non_event_post() {
		$post_id = static::factory()->post->create();

		$meta_watcher = new Meta_Watcher();
		$tracked_meta_keys = 	$meta_watcher->get_tracked_meta_keys();

		$this->assertNotEmpty($tracked_meta_keys);

		// Use a meta key that should trigger a mark on an Event.
		$meta_watcher->mark_for_update( $post_id, $tracked_meta_keys[0] );

		$this->assertEmpty($meta_watcher->get_marked_ids());
	}

	/**
	 * It should not mark for update an Event update on non-tracked meta key
	 *
	 * @test
	 */
	public function should_not_mark_for_update_an_event_update_on_non_tracked_meta_key() {
		$event_id = (new Event_Factory())->create();

		$this->assertEquals(TEC::POSTTYPE, get_post_type($event_id));

		$meta_watcher = new Meta_Watcher();
		$tracked_meta_keys = 	$meta_watcher->get_tracked_meta_keys();

		$this->assertFalse( in_array( '_some_meta_key', $tracked_meta_keys, true ) );

		// Use a meta key that should not trigger a mark on an Event.
		$meta_watcher->mark_for_update( $event_id, '_some_meta_key' );

		$this->assertEmpty($meta_watcher->get_marked_ids());
	}

	/**
	 * It should mark for update an Event with one tracked meta key
	 *
	 * @test
	 */
	public function should_mark_for_update_an_event_with_one_tracked_meta_key() {
		$event_id = ( new Event_Factory() )->create();

		$meta_watcher      = new Meta_Watcher();
		$tracked_meta_keys = $meta_watcher->get_tracked_meta_keys();
		// Use a meta key that should not trigger a mark on an Event.
		$meta_watcher->mark_for_update( $event_id, $tracked_meta_keys[0] );

		$this->assertEquals( [ $event_id ], $meta_watcher->get_marked_ids() );
	}

	/**
	 * It should behave like a FIFO queue
	 *
	 * @test
	 */
	public function should_behave_like_a_fifo_queue() {
		$event_factory = new Event_Factory();
		$event_id_1    = $event_factory->create();
		$event_id_2    = $event_factory->create();
		$event_id_3    = $event_factory->create();

		$meta_watcher      = new Meta_Watcher();
		$tracked_meta_keys = $meta_watcher->get_tracked_meta_keys();
		// Use a meta key that should not trigger a mark on an Event.
		$meta_watcher->mark_for_update( $event_id_2, $tracked_meta_keys[0] );
		$meta_watcher->mark_for_update( $event_id_1, $tracked_meta_keys[0] );
		$meta_watcher->mark_for_update( $event_id_3, $tracked_meta_keys[0] );

		$this->assertEquals( [ $event_id_2, $event_id_1, $event_id_3 ], $meta_watcher->get_marked_ids() );
		$this->assertEquals( $event_id_2, $meta_watcher->pop() );
		$this->assertEquals( [ $event_id_1, $event_id_3 ], $meta_watcher->get_marked_ids() );
		$this->assertEquals( $event_id_1, $meta_watcher->pop() );
		$this->assertEquals( [ $event_id_3 ], $meta_watcher->get_marked_ids() );
		$this->assertEquals( $event_id_3, $meta_watcher->pop() );
		$this->assertEquals( [], $meta_watcher->get_marked_ids() );
		$meta_watcher->push( $event_id_1 );
		$this->assertEquals( [ $event_id_1 ], $meta_watcher->get_marked_ids() );
		$meta_watcher->push( $event_id_2 );
		// Push multiple times to make sure it will be added once.
		$meta_watcher->push( $event_id_2 );
		$meta_watcher->push( $event_id_2 );
		$meta_watcher->push( $event_id_2 );
		$this->assertEquals( [ $event_id_1, $event_id_2 ], $meta_watcher->get_marked_ids() );
		$meta_watcher->push( $event_id_3 );
		$this->assertEquals( [ $event_id_1, $event_id_2, $event_id_3 ], $meta_watcher->get_marked_ids() );
	}

	/**
	 * It should allow knowing if post ID tracked or not
	 *
	 * @test
	 */
	public function should_allow_knowing_if_post_id_tracked_or_not() {
		$event_factory = new Event_Factory();
		$event_id_1    = $event_factory->create();
		$event_id_2    = $event_factory->create();
		$post_id       = static::factory()->post->create();

		$meta_watcher      = new Meta_Watcher();
		$tracked_meta_keys = $meta_watcher->get_tracked_meta_keys();
		// Use a meta key that should not trigger a mark on an Event.
		$meta_watcher->mark_for_update( $event_id_1, $tracked_meta_keys[0] );
		$meta_watcher->mark_for_update( $post_id, $tracked_meta_keys[0] );

		$this->assertTrue( $meta_watcher->is_tracked( $event_id_1 ) );
		$this->assertFalse( $meta_watcher->is_tracked( $event_id_2 ) );
		$this->assertFalse( $meta_watcher->is_tracked( $post_id ) );
	}

	/**
	 * It should allow removing ids
	 *
	 * @test
	 */
	public function should_allow_removing_ids() {
		$event_factory = new Event_Factory();
		$event_id_1    = $event_factory->create();
		$event_id_2    = $event_factory->create();
		$event_id_3    = $event_factory->create();
		$post_id       = static::factory()->post->create();

		$meta_watcher      = new Meta_Watcher();
		$tracked_meta_keys = $meta_watcher->get_tracked_meta_keys();
		// Use a meta key that should not trigger a mark on an Event.
		$meta_watcher->mark_for_update( $event_id_1, $tracked_meta_keys[0] );
		$meta_watcher->mark_for_update( $post_id, $tracked_meta_keys[0] );

		$meta_watcher->remove( $post_id );

		$this->assertEquals( [ $event_id_1 ], $meta_watcher->get_marked_ids() );

		$meta_watcher->push( $event_id_2 );
		$meta_watcher->push( $event_id_3 );
		$meta_watcher->push( $post_id );

		$this->assertEquals( [ $event_id_1, $event_id_2, $event_id_3 ], $meta_watcher->get_marked_ids() );

		$meta_watcher->remove( $event_id_2 );

		$this->assertEquals( [ $event_id_1, $event_id_3 ], $meta_watcher->get_marked_ids() );
	}
}
