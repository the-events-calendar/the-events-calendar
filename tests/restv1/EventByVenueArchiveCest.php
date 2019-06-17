<?php

use Step\Restv1\RestGuy as Tester;

class EventByVenueArchiveCest extends BaseRestCest {
	/**
	 * It should return bad request if trying to get events by non numeric venue
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_get_events_by_non_numeric_venue( Tester $I ) {
		$I->sendGET( $this->events_url, [ 'venue' => 'foo' ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return bad request if trying to get events by non existing venue ID
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_get_events_by_non_existing_venue_id( Tester $I ) {
		$I->sendGET( $this->events_url, [ 'venue' => 23 ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 200 if trying to get events by venue not assigned to any event
	 *
	 * @test
	 */
	public function it_should_return_200_if_trying_to_get_events_by_venue_not_assigned_to_any_event( Tester $I ) {
		$venue = $I->haveVenueInDatabase();

		$I->sendGET( $this->events_url, [ 'venue' => $venue ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->events );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
	}

	/**
	 * It should return events related to the venue when specifying existing venue ID
	 *
	 * @test
	 */
	public function it_should_return_events_related_to_the_venue_when_specifying_existing_venue_id( Tester $I ) {
		$venue = $I->haveVenueInDatabase();
		$I->haveManyEventsInDatabase( 3, [ 'venue' => $venue ] );

		$I->sendGET( $this->events_url, [ 'venue' => $venue ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}

	/**
	 * It should not return non public events related to existing venue ID
	 *
	 * @test
	 */
	public function it_should_not_return_non_public_events_related_to_existing_venue_id( Tester $I ) {
		$venue = $I->haveVenueInDatabase();
		$I->haveManyEventsInDatabase( 3, [ 'venue' => $venue, 'post_status' => 'draft' ] );

		$I->sendGET( $this->events_url, [ 'venue' => $venue ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return non public events related to existing venue ID if user can edit events
	 *
	 * @test
	 */
	public function it_should_return_non_public_events_related_to_existing_venue_id_if_user_can_edit_events( Tester $I ) {
		$venue = $I->haveVenueInDatabase();
		$I->haveManyEventsInDatabase( 3, [ 'venue' => $venue, 'post_status' => 'draft' ] );

		$I->haveHttpHeader( 'X-WP-Nonce', $I->generate_nonce_for_role( 'editor' ) );
		$I->sendGET( $this->events_url, [ 'venue' => $venue ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}
}
