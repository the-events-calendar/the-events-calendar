<?php

use Step\Restv1\RestGuy as Tester;

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
	 * Contributor can edit their own event.
	 *
	 * @test
	 */
	public function contributor_can_edit_own_event( Tester $I ) {
		// Contributor creates and edits their own event
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

		// Same contributor edits their own event (reuse nonce from above)
		$I->sendPUT( $this->events_url . "/{$event_id}", [ 'title' => 'Updated Title' ] );

		$I->seeResponseCodeIsSuccessful();
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
	 * Contributor can delete their own event.
	 *
	 * @test
	 */
	public function contributor_can_delete_own_event( Tester $I ) {
		// Contributor creates and deletes their own event
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

		// Same contributor deletes their own event (reuse nonce from above)
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
}
