<?php

use Step\Restv1\RestGuy as Tester;

class VenueByEventArchiveCest extends BaseRestCest {
	/**
	 * It should allow searching venues by event
	 * @test
	 */
	public function it_should_allow_searching_venues_by_event( Tester $I ) {
		$venue_1       = $I->haveVenueInDatabase();
		$event_one_1   = $I->haveEventInDatabase( [ 'venue' => $venue_1 ] );
		$event_two_1   = $I->haveEventInDatabase( [ 'venue' => $venue_1 ] );
		$event_three_1 = $I->haveEventInDatabase( [ 'venue' => $venue_1 ] );
		$venue_2       = $I->haveVenueInDatabase();
		$event_one_2   = $I->haveEventInDatabase( [ 'venue' => $venue_2 ] );
		$event_two_2   = $I->haveEventInDatabase( [ 'venue' => $venue_2 ] );
		$event_three_2 = $I->haveEventInDatabase( [ 'venue' => $venue_2 ] );
		$venue_3       = $I->haveVenueInDatabase();
		$event_one_3   = $I->haveEventInDatabase( [ 'venue' => $venue_3 ] );
		$event_two_3   = $I->haveEventInDatabase( [ 'venue' => $venue_3 ] );
		$event_three_3 = $I->haveEventInDatabase( [ 'venue' => $venue_3 ] );

		$search_events = [
			$event_one_1   => $venue_1,
			$event_two_1   => $venue_1,
			$event_three_1 => $venue_1,
			$event_one_2   => $venue_2,
			$event_two_2   => $venue_2,
			$event_three_2 => $venue_2,
			$event_one_3   => $venue_3,
			$event_two_3   => $venue_3,
			$event_three_3 => $venue_3,
		];
		foreach ( $search_events as $event_id => $expected_venue ) {
			$I->sendGET( $this->venues_url, [
				'event' => $event_id,
			] );
			$I->seeResponseCodeIs( 200 );
			$I->seeResponseIsJson();
			$response = json_decode( $I->grabResponse() );
			$I->assertCount( 1, $response->venues );
			$first = $response->venues[0];
			$I->assertEquals( $expected_venue, $first->id );
		}
	}

	/**
	 * It should return bad request if event is not a valid event ID
	 * @test
	 */
	public function it_should_return_bad_request_if_event_is_not_a_valid_event_id( Tester $I ) {
		$I->sendGET( $this->venues_url, [ 'event' => 23 ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 200 if no venues are related to the event
	 * @test
	 */
	public function it_should_return_200_if_no_venues_are_related_to_the_event( Tester $I ) {
		$event = $I->haveEventInDatabase();
		$I->haveManyVenuesInDatabase( 3 );

		$I->sendGET( $this->venues_url, [ 'event' => $event ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->venues );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
	}

	/**
	 * It should return 404 if there are no events in db
	 * @test
	 */
	public function it_should_return_404_if_there_are_no_venues_in_db( Tester $I ) {
		$event = $I->haveEventInDatabase();

		$I->sendGET( $this->venues_url, [ 'event' => $event ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->venues );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
	}

	/**
	 * It should not return non public venues related to event
	 * @test
	 */
	public function it_should_not_return_non_public_venues_related_to_event( Tester $I ) {
		$draft_venue = $I->haveVenueInDatabase( [ 'post_status' => 'draft' ] );
		$event       = $I->haveEventInDatabase( [ 'venue' => $draft_venue ] );

		$I->sendGET( $this->venues_url, [ 'event' => $event ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->venues );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
	}

	/**
	 * It should show non public venues to authorized user
	 * @test
	 */
	public function it_should_show_non_public_venues_to_authorized_user( Tester $I ) {
		$draft_venue = $I->haveVenueInDatabase( [ 'post_status' => 'draft' ] );
		$event       = $I->haveEventInDatabase( [ 'venue' => $draft_venue ] );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->venues_url, [ 'event' => $event ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->venues );
		$first = $response->venues[0];
		$I->assertEquals( $draft_venue, $first->id );
	}
}
