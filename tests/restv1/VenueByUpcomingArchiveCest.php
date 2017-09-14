<?php

use Step\Restv1\RestGuy as Tester;

class VenueByUpcomingArchiveCest extends BaseRestCest {
	/**
	 * It should allow searching venues by upcoming events
	 * @test
	 */
	public function it_should_allow_searching_venues_by_upcoming_events( Tester $I ) {
		$venue_1 = $I->haveVenueInDatabase();
		$venue_2 = $I->haveVenueInDatabase();
		$venue_3 = $I->haveVenueInDatabase();
		$venue_4 = $I->haveVenueInDatabase();
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'venue' => $venue_1 ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'venue' => $venue_2 ] );
		$I->haveEventInDatabase( [ 'when' => '-1 month', 'venue' => $venue_3 ] );

		$I->sendGET( $this->venues_url, [
			'only_with_upcoming' => 'true',
		] );
		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 2, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $venue_1, $venue_2 ], $response_ids );

		$I->sendGET( $this->venues_url, [
			'only_with_upcoming' => 'false',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 4, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $venue_1, $venue_2, $venue_3, $venue_4 ], $response_ids );
	}

	/**
	 * It should return 404 if no venues have upcoming events
	 * @test
	 */
	public function it_should_return_404_if_no_venues_have_upcoming_events( Tester $I ) {
		$venue_1 = $I->haveVenueInDatabase();
		$venue_2 = $I->haveVenueInDatabase();
		$venue_3 = $I->haveVenueInDatabase();
		$I->haveVenueInDatabase();
		$I->haveEventInDatabase( [ 'when' => '-1 month', 'venue' => $venue_1 ] );
		$I->haveEventInDatabase( [ 'when' => '-1 month', 'venue' => $venue_2 ] );
		$I->haveEventInDatabase( [ 'when' => '-1 month', 'venue' => $venue_3 ] );

		$I->sendGET( $this->venues_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should mark a venue as with upcoming events depending on the user auth
	 * @test
	 */
	public function it_should_mark_a_venue_as_with_upcoming_events_depending_on_the_user_auth( Tester $I ) {
		$venue_1 = $I->haveVenueInDatabase();
		$venue_2 = $I->haveVenueInDatabase();
		$venue_3 = $I->haveVenueInDatabase();
		$venue_4 = $I->haveVenueInDatabase();
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'venue' => $venue_1 ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'venue' => $venue_2, 'post_status' => 'private' ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'venue' => $venue_3, 'post_status' => 'private' ] );

		$I->sendGET( $this->venues_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $venue_1 ], $response_ids );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->venues_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 3, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $venue_1, $venue_2, $venue_3 ], $response_ids );
	}

	/**
	 * It should exclude draft and pending events from marking a venue with upcoming events
	 * @test
	 */
	public function it_should_exclude_draft_and_pending_events_from_marking_a_venue_with_upcoming_events( Tester $I ) {
		$venue_1 = $I->haveVenueInDatabase();
		$venue_2 = $I->haveVenueInDatabase();
		$venue_3 = $I->haveVenueInDatabase();
		$venue_4 = $I->haveVenueInDatabase();
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'venue' => $venue_1 ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'venue' => $venue_2, 'post_status' => 'draft' ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'venue' => $venue_3, 'post_status' => 'pending' ] );

		$I->sendGET( $this->venues_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $venue_1 ], $response_ids );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->venues_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $venue_1 ], $response_ids );
	}
}
