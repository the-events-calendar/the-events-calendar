<?php

use Step\Restv1\RestGuy as Tester;

class EventByOrganizerArchiveCest extends BaseRestCest {
	/**
	 * It should return bad request if trying to get events by non numeric organizer
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_get_events_by_non_numeric_organizer( Tester $I ) {
		$I->sendGET( $this->events_url, [ 'organizer' => 'foo' ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should retuen bad request if trying to get events by non existing organizer ID
	 *
	 * @test
	 */
	public function it_should_retuen_bad_request_if_trying_to_get_events_by_non_existing_organizer_id( Tester $I ) {
		$I->sendGET( $this->events_url, [ 'organizer' => 23 ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 404 if trying to get events by organizer not assigned to any event
	 *
	 * @test
	 */
	public function it_should_return_404_if_trying_to_get_events_by_organizer_not_assigned_to_any_event( Tester $I ) {
		$organizer = $I->haveOrganizerInDatabase();

		$I->sendGET( $this->events_url, [ 'organizer' => $organizer ] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return events related to the organizer when specifying existing organizer ID
	 *
	 * @test
	 */
	public function it_should_return_events_related_to_the_organizer_when_specifying_existing_organizer_id( Tester $I ) {
		$organizer = $I->haveOrganizerInDatabase();
		$I->haveManyEventsInDatabase( 3, [ 'organizer' => $organizer ] );

		$I->sendGET( $this->events_url, [ 'organizer' => $organizer ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}

	/**
	 * It should not return non public events related to existing organizer ID
	 *
	 * @test
	 */
	public function it_should_not_return_non_public_events_related_to_existing_organizer_id( Tester $I ) {
		$organizer = $I->haveOrganizerInDatabase();
		$I->haveManyEventsInDatabase( 3, [ 'organizer' => $organizer, 'post_status' => 'draft' ] );

		$I->sendGET( $this->events_url, [ 'organizer' => $organizer ] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return non public events related to existing organizer ID if user can edit events
	 *
	 * @test
	 */
	public function it_should_return_non_public_events_related_to_existing_organizer_id_if_user_can_edit_events( Tester $I ) {
		$organizer = $I->haveOrganizerInDatabase();
		$I->haveManyEventsInDatabase( 3, [ 'organizer' => $organizer, 'post_status' => 'draft' ] );

		$I->haveHttpHeader( 'X-WP-Nonce', $I->generate_nonce_for_role( 'editor' ) );
		$I->sendGET( $this->events_url, [ 'organizer' => $organizer ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}

	/**
	 * It should return events associated with multiple organizer with OR logic
	 *
	 * @test
	 */
	public function it_should_return_events_associated_with_multiple_organizer_with_or_logic(Tester $I) {
		$organizer_1 = $I->haveOrganizerInDatabase();
		$organizer_2 = $I->haveOrganizerInDatabase();
		$I->haveManyEventsInDatabase( 3, [ 'organizer' => $organizer_1 ] );
		$I->haveManyEventsInDatabase( 3, [ 'organizer' => $organizer_2 ] );
		$I->haveManyEventsInDatabase( 3, [ 'organizer' => [ $organizer_1, $organizer_2 ] ] );

		$I->sendGET( $this->events_url, [ 'organizer' => [$organizer_1,$organizer_2] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 9, $response->events );
	}
}
