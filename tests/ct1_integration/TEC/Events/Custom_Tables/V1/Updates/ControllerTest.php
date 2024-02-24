<?php

namespace TEC\Events\Custom_Tables\V1\Updates;

use Tribe\Events\Test\Factories\Event as Event_Factory;

class ControllerTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should not commit any updates if meta watcher did not track any
	 *
	 * @test
	 * @covers Controller::commit_updates()
	 */
	public function should_not_commit_any_updates_if_meta_watcher_did_not_track_any() {
		$controller = new Controller( new Meta_Watcher(), new Requests(), new Events() );
		$updated    = $controller->commit_updates();

		$this->assertEquals( 0, $updated );
	}

	/**
	 * It should commit an update for each tracked post ID
	 *
	 * @test
	 * @covers Controller::commit_updates()
	 * @covers Controller::commit_post_updates()
	 */
	public function should_commit_an_update_for_each_tracked_post_id() {
		$event_id_1   = ( new Event_Factory() )->create();
		$event_id_2   = ( new Event_Factory() )->create();
		$event_id_3   = ( new Event_Factory() )->create();
		$meta_watcher = new Meta_Watcher();
		$meta_watcher->mark_for_update( $event_id_1, $meta_watcher->get_tracked_meta_keys()[0] );
		$meta_watcher->mark_for_update( $event_id_2, $meta_watcher->get_tracked_meta_keys()[0] );
		$meta_watcher->mark_for_update( $event_id_3, $meta_watcher->get_tracked_meta_keys()[0] );

		$controller = new Controller( $meta_watcher, new Requests(), new Events() );
		$updated    = $controller->commit_updates();

		$this->assertEquals( 3, $updated );
		$this->assertCount( 0, $meta_watcher->get_marked_ids() );
	}

	/**
	 * It should not commit updates on REST request that did not update
	 *
	 * @test
	 * @covers Controller::commit_post_rest_update()
	 * @covers Controller::commit_post_updates()
	 */
	public function should_not_commit_updates_on_rest_request_that_did_not_update() {
		$event        = ( new Event_Factory() )->create_and_get();
		$meta_watcher = new Meta_Watcher();
		$requests     = new Requests();

		$this->assertCount( 0, $meta_watcher->get_marked_ids() );

		$controller = new Controller( $meta_watcher, $requests, new Events() );
		$updated    = $controller->commit_post_rest_update( $event, $requests->from_http_request() );

		$this->assertFalse( $updated );
		$this->assertCount( 0, $meta_watcher->get_marked_ids() );
	}

	/**
	 * It should commit updates on REST request correctly
	 *
	 * @test
	 * @covers Controller::commit_post_rest_update()
	 * @covers Controller::commit_post_updates()
	 */
	public function should_commit_updates_on_rest_request_correctly() {
		$event        = ( new Event_Factory() )->create_and_get();
		$meta_watcher = new Meta_Watcher();
		$meta_watcher->mark_for_update( $event->ID, $meta_watcher->get_tracked_meta_keys()[0] );
		$requests = new Requests();

		$this->assertCount( 1, $meta_watcher->get_marked_ids() );

		$controller = new Controller( $meta_watcher, $requests, new Events() );
		$updated    = $controller->commit_post_rest_update( $event, $requests->from_http_request() );

		$this->assertTrue( $updated );
		$this->assertCount( 0, $meta_watcher->get_marked_ids() );
	}

	/**
	 * It should report no deletions when Events makes no deletions
	 *
	 * @test
	 * @covers Controller::delete_custom_tables_data()
	 */
	public function should_report_no_deletions_when_events_makes_no_deletions() {
		$event_id = ( new Event_Factory() )->create();
		$requests = new Requests();

		// Since there was no update of the custom tables data, Events will report no deletions.
		$controller = new Controller( new Meta_Watcher(), $requests, new Events() );
		$affected   = $controller->delete_custom_tables_data( $event_id, $requests->from_http_request() );

		$this->assertEquals( 0, $affected );
	}

	/**
	 * It should report deletions when Events does
	 *
	 * @test
	 */
	public function should_report_deletions_when_events_does() {
		$event_id = ( new Event_Factory() )->create();
		$requests = new Requests();
		$events   = new Events();
		$this->assertTrue( $events->update( $event_id ) );

		$controller = new Controller( new Meta_Watcher(), $requests, $events );
		$affected   = $controller->delete_custom_tables_data( $event_id, $requests->from_http_request() );

		$this->assertEquals( 2, $affected );
	}
}
