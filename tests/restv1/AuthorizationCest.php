<?php

use Step\Restv1\RestGuy as Tester;

/**
 * Tests per-post capability checks for REST API endpoints.
 *
 * Ensures that the Events, Venues, and Organizers REST endpoints enforce
 * object-level authorization: users can only read, edit or delete posts they
 * own unless they have the appropriate "read/edit/delete others" capability.
 *
 * @package TEC\Tests\REST\V1\Endpoints
 * @since 6.15.16.1
 */
class AuthorizationCest extends BaseRestCest {

	/**
	 * Contributor cannot edit an event created by another user.
	 *
	 * @test
	 */
	public function contributor_cannot_edit_admin_event( Tester $I ) {
		// Admin creates an event
		$I->generate_nonce_for_role( 'administrator' );
		$event_id = $I->haveEventInDatabase( [
			'post_title' => 'Admin Event',
			'when'       => '+1 day 9am',
		] );

		// Contributor tries to edit it
		$I->generate_nonce_for_role( 'contributor' );
		$I->sendPUT( $this->events_url . "/{$event_id}", [ 'title' => 'Hacked Title' ] );

		$I->seeResponseCodeIs( 403 );
	}

	/**
	 * Contributor cannot edit their own published event.
	 *
	 * @test
	 */
	public function contributor_cannot_edit_own_published_event( Tester $I ) {
		// Contributor creates a published event
		$contributor_id = $I->haveUserInDatabase( 'contributor_user', 'contributor', [ 'user_pass' => 'contributor' ] );
		$I->loginAs( 'contributor_user', 'contributor' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $contributor_id );
		$nonce = wp_create_nonce( 'wp_rest' );
		$I->haveHttpHeader( 'X-WP-Nonce', $nonce );

		$event_id = $I->haveEventInDatabase( [
			'post_title'   => 'Contributor Event',
			'when'         => '+1 day 9am',
			'post_author'  => $contributor_id,
		] );

		// Same contributor tries to edit their own published event
		$I->sendPUT( $this->events_url . "/{$event_id}", [ 'title' => 'Updated Title' ] );

		$I->seeResponseCodeIs( 403 );
	}

	/**
	 * Contributor cannot delete an event created by another user.
	 *
	 * @test
	 */
	public function contributor_cannot_delete_admin_event( Tester $I ) {
		// Admin creates an event
		$I->generate_nonce_for_role( 'administrator' );
		$event_id = $I->haveEventInDatabase( [
			'post_title' => 'Admin Event',
			'when'       => '+1 day 9am',
		] );

		// Contributor tries to delete it
		$I->generate_nonce_for_role( 'contributor' );
		$I->sendDELETE( $this->events_url . "/{$event_id}", [ 'force' => true ] );

		$I->seeResponseCodeIs( 403 );
	}

	/**
	 * Contributor cannot delete their own published event.
	 *
	 * @test
	 */
	public function contributor_cannot_delete_own_published_event( Tester $I ) {
		// Contributor creates a published event
		$contributor_id = $I->haveUserInDatabase( 'contributor_user', 'contributor', [ 'user_pass' => 'contributor' ] );
		$I->loginAs( 'contributor_user', 'contributor' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $contributor_id );
		$nonce = wp_create_nonce( 'wp_rest' );
		$I->haveHttpHeader( 'X-WP-Nonce', $nonce );

		$event_id = $I->haveEventInDatabase( [
			'post_title'   => 'Contributor Event',
			'when'         => '+1 day 9am',
			'post_author'  => $contributor_id,
		] );

		// Same contributor tries to delete their own published event
		$I->sendDELETE( $this->events_url . "/{$event_id}", [ 'force' => true ] );

		$I->seeResponseCodeIs( 403 );
	}

	/**
	 * Contributor can edit their own draft event.
	 *
	 * @test
	 */
	public function contributor_can_edit_own_draft_event( Tester $I ) {
		// Contributor creates a draft event
		$contributor_id = $I->haveUserInDatabase( 'contributor_user', 'contributor', [ 'user_pass' => 'contributor' ] );
		$I->loginAs( 'contributor_user', 'contributor' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $contributor_id );
		$nonce = wp_create_nonce( 'wp_rest' );
		$I->haveHttpHeader( 'X-WP-Nonce', $nonce );

		$event_id = $I->haveEventInDatabase( [
			'post_title'   => 'Draft Contributor Event',
			'when'         => '+1 day 9am',
			'post_author'  => $contributor_id,
			'post_status'  => 'draft',
		] );

		// Same contributor edits their own draft event
		$I->sendPUT( $this->events_url . "/{$event_id}", [ 'title' => 'Updated Draft Title' ] );

		$I->seeResponseCodeIsSuccessful();
	}

	/**
	 * Contributor can delete their own draft event.
	 *
	 * @test
	 */
	public function contributor_can_delete_own_draft_event( Tester $I ) {
		// Contributor creates a draft event
		$contributor_id = $I->haveUserInDatabase( 'contributor_user', 'contributor', [ 'user_pass' => 'contributor' ] );
		$I->loginAs( 'contributor_user', 'contributor' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $contributor_id );
		$nonce = wp_create_nonce( 'wp_rest' );
		$I->haveHttpHeader( 'X-WP-Nonce', $nonce );

		$event_id = $I->haveEventInDatabase( [
			'post_title'   => 'Draft Contributor Event',
			'when'         => '+1 day 9am',
			'post_author'  => $contributor_id,
			'post_status'  => 'draft',
		] );

		// Same contributor deletes their own draft event
		$I->sendDELETE( $this->events_url . "/{$event_id}", [ 'force' => true ] );

		$I->seeResponseCodeIs( 200 );
	}

	/**
	 * Editor can edit events created by other users.
	 *
	 * @test
	 */
	public function editor_can_edit_other_events( Tester $I ) {
		// Admin creates an event
		$admin_id = $I->haveUserInDatabase( 'admin_user', 'administrator', [ 'user_pass' => 'admin' ] );
		$I->loginAs( 'admin_user', 'admin' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $admin_id );
		$admin_nonce = wp_create_nonce( 'wp_rest' );
		$I->haveHttpHeader( 'X-WP-Nonce', $admin_nonce );

		$event_id = $I->haveEventInDatabase( [
			'post_title'   => 'Admin Event',
			'when'         => '+1 day 9am',
			'post_author'  => $admin_id,
		] );

		// Editor (different user) edits the event
		$editor_id = $I->haveUserInDatabase( 'editor_user', 'editor', [ 'user_pass' => 'editor' ] );
		$I->loginAs( 'editor_user', 'editor' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $editor_id );
		$editor_nonce = wp_create_nonce( 'wp_rest' );
		$I->haveHttpHeader( 'X-WP-Nonce', $editor_nonce );

		$I->sendPUT( $this->events_url . "/{$event_id}", [ 'title' => 'Edited by Editor' ] );

		$I->seeResponseCodeIsSuccessful();
	}

	/**
	 * Editor can delete events created by other users.
	 *
	 * @test
	 */
	public function editor_can_delete_other_events( Tester $I ) {
		// Admin creates an event
		$admin_id = $I->haveUserInDatabase( 'admin_user', 'administrator', [ 'user_pass' => 'admin' ] );
		$I->loginAs( 'admin_user', 'admin' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $admin_id );
		$admin_nonce = wp_create_nonce( 'wp_rest' );
		$I->haveHttpHeader( 'X-WP-Nonce', $admin_nonce );

		$event_id = $I->haveEventInDatabase( [
			'post_title'   => 'Admin Event',
			'when'         => '+1 day 9am',
			'post_author'  => $admin_id,
		] );

		// Editor (different user) deletes the event
		$editor_id = $I->haveUserInDatabase( 'editor_user', 'editor', [ 'user_pass' => 'editor' ] );
		$I->loginAs( 'editor_user', 'editor' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $editor_id );
		$editor_nonce = wp_create_nonce( 'wp_rest' );
		$I->haveHttpHeader( 'X-WP-Nonce', $editor_nonce );

		$I->sendDELETE( $this->events_url . "/{$event_id}", [ 'force' => true ] );

		$I->seeResponseCodeIs( 200 );
	}

	/**
	 * Contributor cannot edit a venue created by another user.
	 *
	 * @test
	 */
	public function contributor_cannot_edit_admin_venue( Tester $I ) {
		// Admin creates a venue
		$I->generate_nonce_for_role( 'administrator' );
		$venue_id = $I->haveVenueInDatabase( [
			'post_title' => 'Admin Venue',
		] );

		// Contributor tries to edit it
		$I->generate_nonce_for_role( 'contributor' );
		$I->sendPUT( $this->venues_url . "/{$venue_id}", [ 'venue' => 'Hacked Venue' ] );

		$I->seeResponseCodeIs( 403 );
	}

	/**
	 * Contributor cannot delete a venue created by another user.
	 *
	 * @test
	 */
	public function contributor_cannot_delete_admin_venue( Tester $I ) {
		// Admin creates a venue
		$I->generate_nonce_for_role( 'administrator' );
		$venue_id = $I->haveVenueInDatabase( [
			'post_title' => 'Admin Venue',
		] );

		// Contributor tries to delete it
		$I->generate_nonce_for_role( 'contributor' );
		$I->sendDELETE( $this->venues_url . "/{$venue_id}", [ 'force' => true ] );

		$I->seeResponseCodeIs( 403 );
	}

	/**
	 * Contributor cannot edit an organizer created by another user.
	 *
	 * @test
	 */
	public function contributor_cannot_edit_admin_organizer( Tester $I ) {
		// Admin creates an organizer
		$I->generate_nonce_for_role( 'administrator' );
		$organizer_id = $I->haveOrganizerInDatabase( [
			'post_title' => 'Admin Organizer',
		] );

		// Contributor tries to edit it
		$I->generate_nonce_for_role( 'contributor' );
		$I->sendPUT( $this->organizers_url . "/{$organizer_id}", [ 'organizer' => 'Hacked Organizer' ] );

		$I->seeResponseCodeIs( 403 );
	}

	/**
	 * Contributor cannot delete an organizer created by another user.
	 *
	 * @test
	 */
	public function contributor_cannot_delete_admin_organizer( Tester $I ) {
		// Admin creates an organizer
		$I->generate_nonce_for_role( 'administrator' );
		$organizer_id = $I->haveOrganizerInDatabase( [
			'post_title' => 'Admin Organizer',
		] );

		// Contributor tries to delete it
		$I->generate_nonce_for_role( 'contributor' );
		$I->sendDELETE( $this->organizers_url . "/{$organizer_id}", [ 'force' => true ] );

		$I->seeResponseCodeIs( 403 );
	}

	/**
	 * Unauthenticated user cannot edit an event.
	 *
	 * @test
	 */
	public function unauthenticated_cannot_edit_event( Tester $I ) {
		// Admin creates an event
		$I->generate_nonce_for_role( 'administrator' );
		$event_id = $I->haveEventInDatabase( [
			'post_title' => 'Public Event',
			'when'       => '+1 day 9am',
		] );

		// Clear authentication for unauthenticated request
		$I->haveHttpHeader( 'X-WP-Nonce', '' );
		$I->sendPUT( $this->events_url . "/{$event_id}", [ 'title' => 'Tampered' ] );

		$I->seeResponseCodeIs( 401 );
	}

	/**
	 * Unauthenticated user cannot delete an event.
	 *
	 * @test
	 */
	public function unauthenticated_cannot_delete_event( Tester $I ) {
		// Admin creates an event
		$I->generate_nonce_for_role( 'administrator' );
		$event_id = $I->haveEventInDatabase( [
			'post_title' => 'Public Event',
			'when'       => '+1 day 9am',
		] );

		// Clear authentication for unauthenticated request
		$I->haveHttpHeader( 'X-WP-Nonce', '' );
		$I->sendDELETE( $this->events_url . "/{$event_id}", [ 'force' => true ] );

		$I->seeResponseCodeIs( 401 );
	}

	/**
	 * Unauthenticated user cannot edit a contributor's draft event.
	 *
	 * @test
	 */
	public function unauthenticated_cannot_edit_draft_event( Tester $I ) {
		// Contributor creates a draft event
		$contributor_id = $I->haveUserInDatabase( 'contributor_user', 'contributor', [ 'user_pass' => 'contributor' ] );
		$event_id = $I->haveEventInDatabase( [
			'post_title'  => 'Draft Event',
			'when'        => '+1 day 9am',
			'post_author' => $contributor_id,
			'post_status' => 'draft',
		] );

		// Unauthenticated user tries to edit the draft event
		$I->haveHttpHeader( 'X-WP-Nonce', '' );
		$I->sendPUT( $this->events_url . "/{$event_id}", [ 'title' => 'Tampered Draft' ] );

		$I->seeResponseCodeIs( 401 );
	}

	/**
	 * Logs in as a Contributor and returns their user ID.
	 */
	private function login_as_contributor( Tester $I ): int {
		$contributor_id = $I->haveUserInDatabase( 'contributor_user', 'contributor', [ 'user_pass' => 'contributor' ] );

		$I->loginAs( 'contributor_user', 'contributor' );
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $contributor_id );
		$I->haveHttpHeader( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );

		return $contributor_id;
	}

	/**
	 * Grabs the IDs returned for an archive request.
	 */
	private function grab_archive_ids( Tester $I, string $url, array $params, string $key ): array {
		$I->sendGET( $url, $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );

		return array_map( 'intval', array_column( $response[ $key ], 'id' ) );
	}

	/**
	 * Contributor sees their own draft event but not another user's in the archive.
	 *
	 * @test
	 */
	public function contributor_cannot_list_admin_draft_event( Tester $I ) {
		$admin_id    = $I->haveUserInDatabase( 'admin_user', 'administrator', [ 'user_pass' => 'admin' ] );
		$admin_draft = $I->haveEventInDatabase( [
			'post_title'  => 'Admin Draft Event',
			'when'        => '+1 day 9am',
			'post_author' => $admin_id,
			'post_status' => 'draft',
		] );

		$contributor_id = $this->login_as_contributor( $I );
		$own_draft      = $I->haveEventInDatabase( [
			'post_title'  => 'Contributor Draft Event',
			'when'        => '+1 day 9am',
			'post_author' => $contributor_id,
			'post_status' => 'draft',
		] );

		$ids = $this->grab_archive_ids( $I, $this->events_url, [ 'status' => 'draft' ], 'events' );

		$I->assertContains( $own_draft, $ids );
		$I->assertNotContains( $admin_draft, $ids );
	}

	/**
	 * Contributor does not see another user's draft event when no status is requested.
	 *
	 * @test
	 */
	public function contributor_cannot_list_admin_draft_event_without_a_status( Tester $I ) {
		$admin_id    = $I->haveUserInDatabase( 'admin_user', 'administrator', [ 'user_pass' => 'admin' ] );
		$admin_draft = $I->haveEventInDatabase( [
			'post_title'  => 'Admin Draft Event',
			'when'        => '+1 day 9am',
			'post_author' => $admin_id,
			'post_status' => 'draft',
		] );

		$contributor_id = $this->login_as_contributor( $I );
		$own_draft      = $I->haveEventInDatabase( [
			'post_title'  => 'Contributor Draft Event',
			'when'        => '+1 day 9am',
			'post_author' => $contributor_id,
			'post_status' => 'draft',
		] );

		$ids = $this->grab_archive_ids( $I, $this->events_url, [], 'events' );

		$I->assertContains( $own_draft, $ids );
		$I->assertNotContains( $admin_draft, $ids );
	}

	/**
	 * Contributor sees their own draft venue but not another user's in the archive.
	 *
	 * @test
	 */
	public function contributor_cannot_list_admin_draft_venue( Tester $I ) {
		$admin_id    = $I->haveUserInDatabase( 'admin_user', 'administrator', [ 'user_pass' => 'admin' ] );
		$admin_draft = $I->haveVenueInDatabase( [
			'post_title'  => 'Admin Draft Venue',
			'post_author' => $admin_id,
			'post_status' => 'draft',
		] );

		$contributor_id = $this->login_as_contributor( $I );
		$own_draft      = $I->haveVenueInDatabase( [
			'post_title'  => 'Contributor Draft Venue',
			'post_author' => $contributor_id,
			'post_status' => 'draft',
		] );

		$ids = $this->grab_archive_ids( $I, $this->venues_url, [ 'status' => 'draft' ], 'venues' );

		$I->assertContains( $own_draft, $ids );
		$I->assertNotContains( $admin_draft, $ids );
	}

	/**
	 * Contributor sees their own draft organizer but not another user's in the archive.
	 *
	 * @test
	 */
	public function contributor_cannot_list_admin_draft_organizer( Tester $I ) {
		$admin_id    = $I->haveUserInDatabase( 'admin_user', 'administrator', [ 'user_pass' => 'admin' ] );
		$admin_draft = $I->haveOrganizerInDatabase( [
			'post_title'  => 'Admin Draft Organizer',
			'post_author' => $admin_id,
			'post_status' => 'draft',
		] );

		$contributor_id = $this->login_as_contributor( $I );
		$own_draft      = $I->haveOrganizerInDatabase( [
			'post_title'  => 'Contributor Draft Organizer',
			'post_author' => $contributor_id,
			'post_status' => 'draft',
		] );

		$ids = $this->grab_archive_ids( $I, $this->organizers_url, [ 'status' => 'draft' ], 'organizers' );

		$I->assertContains( $own_draft, $ids );
		$I->assertNotContains( $admin_draft, $ids );
	}

	/**
	 * The reported total and total_pages for a contributor's draft event archive match the
	 * number of events actually returned, not the raw count of every matching draft.
	 *
	 * @test
	 */
	public function contributor_archive_totals_match_readable_events( Tester $I ) {
		$admin_id = $I->haveUserInDatabase( 'admin_user', 'administrator', [ 'user_pass' => 'admin' ] );

		for ( $i = 0; $i < 3; $i++ ) {
			$I->haveEventInDatabase( [
				'post_title'  => "Admin Draft Event {$i}",
				'when'        => '+1 day 9am',
				'post_author' => $admin_id,
				'post_status' => 'draft',
			] );
		}

		$contributor_id = $this->login_as_contributor( $I );
		$I->haveEventInDatabase( [
			'post_title'  => 'Contributor Draft Event',
			'when'        => '+1 day 9am',
			'post_author' => $contributor_id,
			'post_status' => 'draft',
		] );

		$I->sendGET( $this->events_url, [ 'status' => 'draft' ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 1, $response['events'] );
		$I->assertEquals( 1, $response['total'] );
		$I->assertEquals( 1, $response['total_pages'] );
	}
}
