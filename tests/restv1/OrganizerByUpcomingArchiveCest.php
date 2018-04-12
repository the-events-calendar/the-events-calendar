<?php

use Step\Restv1\RestGuy as Tester;

class OrganizerByUpcomingArchiveCest extends BaseRestCest {
	/**
	 * It should allow searching organizers by upcoming events
	 * @test
	 */
	public function it_should_allow_searching_organizers_by_upcoming_events( Tester $I ) {
		$organizer_1 = $I->haveOrganizerInDatabase();
		$organizer_2 = $I->haveOrganizerInDatabase();
		$organizer_3 = $I->haveOrganizerInDatabase();
		$organizer_4 = $I->haveOrganizerInDatabase();
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'organizer' => $organizer_1 ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'organizer' => $organizer_2 ] );
		$I->haveEventInDatabase( [ 'when' => '-1 month', 'organizer' => $organizer_3 ] );

		$I->sendGET( $this->organizers_url, [
			'only_with_upcoming' => 'true',
		] );
		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 2, $response['organizers'] );
		$response_ids = array_column( $response['organizers'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $organizer_1, $organizer_2 ], $response_ids );

		$I->sendGET( $this->organizers_url, [
			'only_with_upcoming' => 'false',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 4, $response['organizers'] );
		$response_ids = array_column( $response['organizers'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $organizer_1, $organizer_2, $organizer_3, $organizer_4 ], $response_ids );
	}

	/**
	 * It should return 200 if no organizers have upcoming events
	 * @test
	 */
	public function it_should_return_200_if_no_organizers_have_upcoming_events( Tester $I ) {
		$organizer_1 = $I->haveOrganizerInDatabase();
		$organizer_2 = $I->haveOrganizerInDatabase();
		$organizer_3 = $I->haveOrganizerInDatabase();
		$I->haveOrganizerInDatabase();
		$I->haveEventInDatabase( [ 'when' => '-1 month', 'organizer' => $organizer_1 ] );
		$I->haveEventInDatabase( [ 'when' => '-1 month', 'organizer' => $organizer_2 ] );
		$I->haveEventInDatabase( [ 'when' => '-1 month', 'organizer' => $organizer_3 ] );

		$I->sendGET( $this->organizers_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->organizers );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
	}

	/**
	 * It should mark a organizer as with upcoming events depending on the user auth
	 * @test
	 */
	public function it_should_mark_a_organizer_as_with_upcoming_events_depending_on_the_user_auth( Tester $I ) {
		$organizer_1 = $I->haveOrganizerInDatabase();
		$organizer_2 = $I->haveOrganizerInDatabase();
		$organizer_3 = $I->haveOrganizerInDatabase();
		$organizer_4 = $I->haveOrganizerInDatabase();
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'organizer' => $organizer_1 ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'organizer' => $organizer_2, 'post_status' => 'private' ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'organizer' => $organizer_3, 'post_status' => 'private' ] );

		$I->sendGET( $this->organizers_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['organizers'] );
		$response_ids = array_column( $response['organizers'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $organizer_1 ], $response_ids );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->organizers_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 3, $response['organizers'] );
		$response_ids = array_column( $response['organizers'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $organizer_1, $organizer_2, $organizer_3 ], $response_ids );
	}

	/**
	 * It should exclude draft and pending events from marking a organizer with upcoming events
	 * @test
	 */
	public function it_should_exclude_draft_and_pending_events_from_marking_a_organizer_with_upcoming_events( Tester $I ) {
		$organizer_1 = $I->haveOrganizerInDatabase();
		$organizer_2 = $I->haveOrganizerInDatabase();
		$organizer_3 = $I->haveOrganizerInDatabase();
		$organizer_4 = $I->haveOrganizerInDatabase();
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'organizer' => $organizer_1 ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'organizer' => $organizer_2, 'post_status' => 'draft' ] );
		$I->haveEventInDatabase( [ 'when' => '+1 month', 'organizer' => $organizer_3, 'post_status' => 'pending' ] );

		$I->sendGET( $this->organizers_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['organizers'] );
		$response_ids = array_column( $response['organizers'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $organizer_1 ], $response_ids );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->organizers_url, [
			'only_with_upcoming' => 'true',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['organizers'] );
		$response_ids = array_column( $response['organizers'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $organizer_1 ], $response_ids );
	}
}
