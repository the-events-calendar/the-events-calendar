<?php

use Step\Restv1\RestGuy as Tester;

class VenueByEmptyArchiveCest extends BaseRestCest {
	/**
	 * It should allow searching venues by empty status
	 * @test
	 */
	public function it_should_allow_searching_venues_by_empty_status( Tester $I ) {
		$venue_1     = $I->haveVenueInDatabase();
		$venue_2     = $I->haveVenueInDatabase();
		$venue_3     = $I->haveVenueInDatabase();
		$event_one_3 = $I->haveEventInDatabase( [ 'venue' => $venue_3 ] );

		$I->sendGET( $this->venues_url, [
			'has_events' => 'true',
		] );
		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['venues'] );
		$I->assertEquals( [ $venue_3 ], array_column( $response['venues'], 'id' ) );

		$I->sendGET( $this->venues_url, [
			'has_events' => 'false',
		] );
		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 2, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $venue_1, $venue_2 ], $response_ids );
	}

	/**
	 * It should return 200 if no venues have events
	 * @test
	 */
	public function it_should_return_200_if_no_venues_are_related_to_the_event( Tester $I ) {
		$I->haveManyEventsInDatabase( 3 );
		$I->haveManyVenuesInDatabase( 3 );

		$I->sendGET( $this->venues_url, [ 'has_events' => 'true' ] );

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
	 * It should return 200 if no venues have no events
	 * @test
	 */
	public function it_should_return_200_if_no_venues_have_no_events( Tester $I ) {
		$venue_1 = $I->haveVenueInDatabase();
		$I->haveEventInDatabase( [ 'venue' => $venue_1 ] );
		$venue_2 = $I->haveVenueInDatabase();
		$I->haveEventInDatabase( [ 'venue' => $venue_2 ] );
		$venue_3 = $I->haveVenueInDatabase();
		$I->haveEventInDatabase( [ 'venue' => $venue_3 ] );

		$I->sendGET( $this->venues_url, [ 'has_events' => 'false' ] );

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
	 * It should mark venue as empty if draft or pending events are assigned to it
	 * @test
	 */
	public function it_should_mark_venue_as_empty_if_draft_or_pending_events_are_assigned_to_it( Tester $I ) {
		$venue_1         = $I->haveVenueInDatabase();
		$venue_2         = $I->haveVenueInDatabase();
		$venue_3         = $I->haveVenueInDatabase();
		$venue_4         = $I->haveVenueInDatabase();
		$draft_event     = $I->haveEventInDatabase( [ 'post_status' => 'draft', 'venue' => $venue_1 ] );
		$pending_event   = $I->haveEventInDatabase( [ 'post_status' => 'pending', 'venue' => $venue_2 ] );
		$published_event = $I->haveEventInDatabase( [ 'venue' => $venue_3 ] );

		$I->sendGET( $this->venues_url, [ 'has_events' => 'true' ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['venues'] );
		$response_venues = array_column( $response['venues'], 'id' );
		$I->assertEquals( [ $venue_3 ], $response_venues );
	}
}
