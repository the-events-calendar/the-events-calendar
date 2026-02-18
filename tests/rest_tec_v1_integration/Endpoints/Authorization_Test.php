<?php
/**
 * Tests per-post capability checks for REST API endpoints.
 *
 * Ensures that the Events, Venues, and Organizers REST endpoints enforce
 * object-level authorization: users can only edit/delete posts they own
 * unless they have the appropriate "edit/delete others" capability.
 *
 * @package TEC\Tests\REST\V1\Endpoints
 * @since TBD
 */

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use Codeception\TestCase\WPTestCase;

class Authorization_Test extends WPTestCase {

	/**
	 * Data provider for event REST authorization scenarios.
	 *
	 * @return array<string, array{string, bool, string, int|array<int>>} Scenario name, actor role, is own post, action, expected status.
	 */
	public function event_authorization_provider() {
		return [
			'contributor cannot edit admin event'   => [ 'contributor', false, 'edit', 403 ],
			'contributor can edit own event'       => [ 'contributor', true, 'edit', [ 200, 201 ] ],
			'contributor cannot delete admin event' => [ 'contributor', false, 'delete', 403 ],
			'contributor can delete own event'      => [ 'contributor', true, 'delete', 200 ],
			'editor can edit other events'         => [ 'editor', false, 'edit', 200 ],
			'editor can delete other events'       => [ 'editor', false, 'delete', 200 ],
		];
	}

	/**
	 * Event REST API enforces per-post edit/delete authorization.
	 *
	 * @test
	 * @dataProvider event_authorization_provider
	 *
	 * @param string         $actor_role     Role of the user making the request.
	 * @param bool           $is_own_post    Whether the actor is the post author.
	 * @param string         $action         'edit' or 'delete'.
	 * @param int|array<int> $expected_status Expected HTTP status (or allowed set for success).
	 */
	public function event_rest_authorization( $actor_role, $is_own_post, $action, $expected_status ) {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$author_id = $is_own_post
			? $this->factory()->user->create( [ 'role' => $actor_role ] )
			: $admin_id;

		if ( $is_own_post ) {
			wp_set_current_user( $author_id );
		}

		$event_id = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'     => 'publish',
			'author'     => $author_id,
		] )->create()->ID;

		$actor_id = $is_own_post ? $author_id : $this->factory()->user->create( [ 'role' => $actor_role ] );
		wp_set_current_user( $actor_id );

		if ( $action === 'edit' ) {
			$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/events/' . $event_id );
			$request->set_body_params( [ 'title' => 'Updated Title' ] );
		} else {
			$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/events/' . $event_id );
			$request->set_param( 'force', true );
		}

		$response = rest_get_server()->dispatch( $request );
		$status   = $response->get_status();

		if ( is_array( $expected_status ) ) {
			$this->assertContains( $status, $expected_status, 'Response status should be one of the allowed success codes.' );
		} else {
			$this->assertSame( $expected_status, $status, 'Response status should match expected.' );
		}
	}

	/**
	 * Data provider for venue REST authorization (contributor vs admin).
	 *
	 * @return array<string, array{string, int}>
	 */
	public function venue_authorization_provider() {
		return [
			'contributor cannot edit admin venue'    => [ 'edit', 403 ],
			'contributor cannot delete admin venue'  => [ 'delete', 403 ],
		];
	}

	/**
	 * Venue REST API rejects contributor edit/delete of another user's venue.
	 *
	 * @test
	 * @dataProvider venue_authorization_provider
	 *
	 * @param string $action         'edit' or 'delete'.
	 * @param int    $expected_status Expected HTTP status (403).
	 */
	public function venue_rest_authorization( $action, $expected_status ) {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$venue_id = tribe_venues()->set_args( [
			'title'   => 'Admin Venue',
			'address' => '123 Main St',
			'status'  => 'publish',
		] )->create()->ID;

		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		if ( $action === 'edit' ) {
			$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/venues/' . $venue_id );
			$request->set_body_params( [ 'venue' => 'Tampered', 'address' => '1 Attacker St' ] );
		} else {
			$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/venues/' . $venue_id );
			$request->set_param( 'force', true );
		}

		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( $expected_status, $response->get_status(), 'Contributor should not be able to modify admin venue.' );
	}

	/**
	 * Data provider for organizer REST authorization (contributor vs admin).
	 *
	 * @return array<string, array{string, int}>
	 */
	public function organizer_authorization_provider() {
		return [
			'contributor cannot edit admin organizer'    => [ 'edit', 403 ],
			'contributor cannot delete admin organizer'   => [ 'delete', 403 ],
		];
	}

	/**
	 * Organizer REST API rejects contributor edit/delete of another user's organizer.
	 *
	 * @test
	 * @dataProvider organizer_authorization_provider
	 *
	 * @param string $action         'edit' or 'delete'.
	 * @param int    $expected_status Expected HTTP status (403).
	 */
	public function organizer_rest_authorization( $action, $expected_status ) {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$organizer_id = tribe_organizers()->set_args( [
			'title'  => 'Admin Organizer',
			'email'  => 'admin@example.com',
			'status' => 'publish',
		] )->create()->ID;

		$contributor_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $contributor_id );

		if ( $action === 'edit' ) {
			$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/organizers/' . $organizer_id );
			$request->set_body_params( [ 'organizer' => 'Tampered Organizer' ] );
		} else {
			$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/organizers/' . $organizer_id );
			$request->set_param( 'force', true );
		}

		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( $expected_status, $response->get_status(), 'Contributor should not be able to modify admin organizer.' );
	}

	/**
	 * Data provider for unauthenticated event requests.
	 *
	 * @return array<string, array{string, int}>
	 */
	public function unauthenticated_event_provider() {
		return [
			'unauthenticated cannot edit event'    => [ 'edit', 401 ],
			'unauthenticated cannot delete event' => [ 'delete', 401 ],
		];
	}

	/**
	 * Event REST API rejects unauthenticated edit and delete requests.
	 *
	 * @test
	 * @dataProvider unauthenticated_event_provider
	 *
	 * @param string $action         'edit' or 'delete'.
	 * @param int    $expected_status Expected HTTP status (401).
	 */
	public function unauthenticated_event_rest_authorization( $action, $expected_status ) {
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );

		$event_id = tribe_events()->set_args( [
			'title'      => 'Public Event',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+1 day +2 hours' ) ),
			'status'     => 'publish',
		] )->create()->ID;

		wp_set_current_user( 0 );

		if ( $action === 'edit' ) {
			$request = new \WP_REST_Request( 'PUT', '/tribe/events/v1/events/' . $event_id );
			$request->set_body_params( [ 'title' => 'Tampered by Guest' ] );
		} else {
			$request = new \WP_REST_Request( 'DELETE', '/tribe/events/v1/events/' . $event_id );
			$request->set_param( 'force', true );
		}

		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( $expected_status, $response->get_status(), 'Unauthenticated user should not be able to modify event.' );
	}
}
