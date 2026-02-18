<?php
/**
 * Test per-post capability checks for REST API endpoints.
 *
 * This test suite ensures that authenticated users with lower privileges
 * cannot modify or delete posts they don't own via the REST API.
 *
 * @package TEC\Tests\REST\V1\Endpoints
 * @since TBD
 */

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use Codeception\TestCase\WPTestCase;
use WP_REST_Server;

class Authorization_Test extends WPTestCase {

	/**
	 * Test that a contributor cannot edit an event created by an administrator.
	 *
	 * Vulnerability: Broken object-level authorization allows contributors
	 * to edit events they don't own via REST API.
	 *
	 * Expected behavior (after fix): HTTP 403 Forbidden
	 * Actual behavior (vulnerable): HTTP 200 OK
	 */
	public function test_contributor_cannot_edit_admin_event() {
		// Create an admin user and event
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$event_id = tribe_events()->set_args( [
			'title'       => 'Admin Event',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'      => 'publish',
		] )->create()->ID;

		// Create a contributor user
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		// Attempt to edit the admin's event via REST API
		$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/events/' . $event_id );
		$request->set_body_params( [
			'title' => 'TAMPERED BY CONTRIBUTOR',
			'description' => 'This event was modified without authorization.',
		] );

		// Verify the request is rejected with 403 Forbidden
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 403, $response->get_status(), 'Contributor should not be able to edit admin event' );
	}

	/**
	 * Test that a contributor can edit their own event.
	 *
	 * Expected behavior: HTTP 200 OK
	 */
	public function test_contributor_can_edit_own_event() {
		// Create a contributor user and event
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		$event_id = tribe_events()->set_args( [
			'title'       => 'Contributor Event',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'      => 'publish',
			'author'      => $contributor_id,
		] )->create()->ID;

		// Attempt to edit the contributor's own event
		$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/events/' . $event_id );
		$request->set_body_params( [
			'title' => 'Updated by Contributor',
		] );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is allowed (200 or 201)
		$this->assertThat(
			$response->get_status(),
			$this->logicalOr(
				$this->equalTo( 200 ),
				$this->equalTo( 201 )
			),
			'Contributor should be able to edit their own event'
		);
	}

	/**
	 * Test that a contributor cannot delete an event created by an administrator.
	 *
	 * Vulnerability: Broken object-level authorization allows contributors
	 * to delete events they don't own via REST API.
	 *
	 * Expected behavior (after fix): HTTP 403 Forbidden
	 * Actual behavior (vulnerable): HTTP 200 OK
	 */
	public function test_contributor_cannot_delete_admin_event() {
		// Create an admin user and event
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$event_id = tribe_events()->set_args( [
			'title'       => 'Admin Event to Delete',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'      => 'publish',
		] )->create()->ID;

		// Create a contributor user
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		// Attempt to delete the admin's event via REST API
		$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/events/' . $event_id );
		$request->set_param( 'force', true );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is rejected with 403 Forbidden
		$this->assertSame( 403, $response->get_status(), 'Contributor should not be able to delete admin event' );
	}

	/**
	 * Test that a contributor can delete their own event.
	 *
	 * Expected behavior: HTTP 200 OK
	 */
	public function test_contributor_can_delete_own_event() {
		// Create a contributor user and event
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		$event_id = tribe_events()->set_args( [
			'title'       => 'Contributor Event to Delete',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'      => 'publish',
			'author'      => $contributor_id,
		] )->create()->ID;

		// Attempt to delete the contributor's own event
		$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/events/' . $event_id );
		$request->set_param( 'force', true );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is allowed
		$this->assertSame( 200, $response->get_status(), 'Contributor should be able to delete their own event' );
	}

	/**
	 * Test that an editor with edit_others_tribe_events can edit any event.
	 *
	 * Expected behavior: HTTP 200 OK
	 */
	public function test_editor_can_edit_other_events() {
		// Create an admin user and event
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$event_id = tribe_events()->set_args( [
			'title'       => 'Admin Event',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'      => 'publish',
		] )->create()->ID;

		// Create an editor user
		$editor_id = $this->factory()->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $editor_id );

		// Attempt to edit the admin's event via REST API
		$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/events/' . $event_id );
		$request->set_body_params( [
			'title' => 'Edited by Editor',
		] );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is allowed (editors have edit_others_tribe_events cap)
		$this->assertSame( 200, $response->get_status(), 'Editor should be able to edit other events' );
	}

	/**
	 * Test that an editor can delete any event.
	 *
	 * Expected behavior: HTTP 200 OK
	 */
	public function test_editor_can_delete_other_events() {
		// Create an admin user and event
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$event_id = tribe_events()->set_args( [
			'title'       => 'Admin Event to Delete',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'      => 'publish',
		] )->create()->ID;

		// Create an editor user
		$editor_id = $this->factory()->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $editor_id );

		// Attempt to delete the admin's event via REST API
		$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/events/' . $event_id );
		$request->set_param( 'force', true );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is allowed
		$this->assertSame( 200, $response->get_status(), 'Editor should be able to delete other events' );
	}

	/**
	 * Test venue authorization: contributor cannot edit admin venue.
	 */
	public function test_contributor_cannot_edit_admin_venue() {
		// Create an admin user and venue
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$venue_id = tribe_venues()->set_args( [
			'title'   => 'Admin Venue',
			'address' => '123 Main St',
			'status'  => 'publish',
		] )->create()->ID;

		// Create a contributor user
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		// Attempt to edit the admin's venue via REST API
		$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/venues/' . $venue_id );
		$request->set_body_params( [
			'venue' => 'Tampered Venue Name',
			'address' => '1 Attacker St',
		] );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is rejected with 403 Forbidden
		$this->assertSame( 403, $response->get_status(), 'Contributor should not be able to edit admin venue' );
	}

	/**
	 * Test venue authorization: contributor cannot delete admin venue.
	 */
	public function test_contributor_cannot_delete_admin_venue() {
		// Create an admin user and venue
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$venue_id = tribe_venues()->set_args( [
			'title'   => 'Admin Venue to Delete',
			'address' => '123 Main St',
			'status'  => 'publish',
		] )->create()->ID;

		// Create a contributor user
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		// Attempt to delete the admin's venue via REST API
		$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/venues/' . $venue_id );
		$request->set_param( 'force', true );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is rejected with 403 Forbidden
		$this->assertSame( 403, $response->get_status(), 'Contributor should not be able to delete admin venue' );
	}

	/**
	 * Test organizer authorization: contributor cannot edit admin organizer.
	 */
	public function test_contributor_cannot_edit_admin_organizer() {
		// Create an admin user and organizer
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$organizer_id = tribe_organizers()->set_args( [
			'title'  => 'Admin Organizer',
			'email'  => 'admin@example.com',
			'status' => 'publish',
		] )->create()->ID;

		// Create a contributor user
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		// Attempt to edit the admin's organizer via REST API
		$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/organizers/' . $organizer_id );
		$request->set_body_params( [
			'organizer' => 'Tampered Organizer Name',
		] );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is rejected with 403 Forbidden
		$this->assertSame( 403, $response->get_status(), 'Contributor should not be able to edit admin organizer' );
	}

	/**
	 * Test organizer authorization: contributor cannot delete admin organizer.
	 */
	public function test_contributor_cannot_delete_admin_organizer() {
		// Create an admin user and organizer
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$organizer_id = tribe_organizers()->set_args( [
			'title'  => 'Admin Organizer to Delete',
			'email'  => 'admin@example.com',
			'status' => 'publish',
		] )->create()->ID;

		// Create a contributor user
		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		// Attempt to delete the admin's organizer via REST API
		$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/organizers/' . $organizer_id );
		$request->set_param( 'force', true );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is rejected with 403 Forbidden
		$this->assertSame( 403, $response->get_status(), 'Contributor should not be able to delete admin organizer' );
	}

	/**
	 * Test that unauthenticated users cannot edit events.
	 */
	public function test_unauthenticated_cannot_edit_event() {
		// Create an event as admin
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$event_id = tribe_events()->set_args( [
			'title'       => 'Public Event',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'      => 'publish',
		] )->create()->ID;

		// Logout
		wp_set_current_user( 0 );

		// Attempt to edit the event via REST API
		$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/events/' . $event_id );
		$request->set_body_params( [
			'title' => 'Tampered by Guest',
		] );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is rejected
		$this->assertSame( 401, $response->get_status(), 'Unauthenticated user should not be able to edit event' );
	}

	/**
	 * Test that unauthenticated users cannot delete events.
	 */
	public function test_unauthenticated_cannot_delete_event() {
		// Create an event as admin
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$event_id = tribe_events()->set_args( [
			'title'       => 'Public Event',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'      => 'publish',
		] )->create()->ID;

		// Logout
		wp_set_current_user( 0 );

		// Attempt to delete the event via REST API
		$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/events/' . $event_id );
		$request->set_param( 'force', true );

		$response = rest_get_server()->dispatch( $request );

		// Verify the request is rejected
		$this->assertSame( 401, $response->get_status(), 'Unauthenticated user should not be able to delete event' );
	}
}
