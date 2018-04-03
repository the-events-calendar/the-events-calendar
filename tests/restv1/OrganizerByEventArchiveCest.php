<?php

use Step\Restv1\RestGuy as Tester;

class OrganizerByEventArchiveCest extends BaseRestCest {
	/**
	 * It should allow searching organizers by event
	 * @test
	 */
	public function it_should_allow_searching_organizers_by_event( Tester $I ) {
		$organizer_1       = $I->haveOrganizerInDatabase();
		$event_one_1   = $I->haveEventInDatabase( [ 'organizer' => $organizer_1 ] );
		$event_two_1   = $I->haveEventInDatabase( [ 'organizer' => $organizer_1 ] );
		$event_three_1 = $I->haveEventInDatabase( [ 'organizer' => $organizer_1 ] );
		$organizer_2       = $I->haveOrganizerInDatabase();
		$event_one_2   = $I->haveEventInDatabase( [ 'organizer' => $organizer_2 ] );
		$event_two_2   = $I->haveEventInDatabase( [ 'organizer' => $organizer_2 ] );
		$event_three_2 = $I->haveEventInDatabase( [ 'organizer' => $organizer_2 ] );
		$organizer_3       = $I->haveOrganizerInDatabase();
		$event_one_3   = $I->haveEventInDatabase( [ 'organizer' => $organizer_3 ] );
		$event_two_3   = $I->haveEventInDatabase( [ 'organizer' => $organizer_3 ] );
		$event_three_3 = $I->haveEventInDatabase( [ 'organizer' => $organizer_3 ] );

		$search_events = [
			$event_one_1   => $organizer_1,
			$event_two_1   => $organizer_1,
			$event_three_1 => $organizer_1,
			$event_one_2   => $organizer_2,
			$event_two_2   => $organizer_2,
			$event_three_2 => $organizer_2,
			$event_one_3   => $organizer_3,
			$event_two_3   => $organizer_3,
			$event_three_3 => $organizer_3,
		];
		foreach ( $search_events as $event_id => $expected_organizer ) {
			$I->sendGET( $this->organizers_url, [
				'event' => $event_id,
			] );
			$I->seeResponseCodeIs( 200 );
			$I->seeResponseIsJson();
			$response = json_decode( $I->grabResponse() );
			$I->assertCount( 1, $response->organizers );
			$first = $response->organizers[0];
			$I->assertEquals( $expected_organizer, $first->id );
		}
	}

	/**
	 * It should return bad request if event is not a valid event ID
	 * @test
	 */
	public function it_should_return_bad_request_if_event_is_not_a_valid_event_id( Tester $I ) {
		$I->sendGET( $this->organizers_url, [ 'event' => 23 ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 200 if no organizers are related to the event
	 * @test
	 */
	public function it_should_return_200_if_no_organizers_are_related_to_the_event( Tester $I ) {
		$event = $I->haveEventInDatabase();
		$I->haveManyOrganizersInDatabase( 3 );

		$I->sendGET( $this->organizers_url, [ 'event' => $event ] );

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
	 * It should return 200 if there are no events in db
	 * @test
	 */
	public function it_should_return_200_if_there_are_no_organizers_in_db( Tester $I ) {
		$event = $I->haveEventInDatabase();

		$I->sendGET( $this->organizers_url, [ 'event' => $event ] );

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
	 * It should not return non public organizers related to event
	 * @test
	 */
	public function it_should_not_return_non_public_organizers_related_to_event( Tester $I ) {
		$draft_organizer = $I->haveOrganizerInDatabase( [ 'post_status' => 'draft' ] );
		$event       = $I->haveEventInDatabase( [ 'organizer' => $draft_organizer ] );

		$I->sendGET( $this->organizers_url, [ 'event' => $event ] );

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
	 * It should show non public organizers to authorized user
	 * @test
	 */
	public function it_should_show_non_public_organizers_to_authorized_user( Tester $I ) {
		$draft_organizer = $I->haveOrganizerInDatabase( [ 'post_status' => 'draft' ] );
		$event       = $I->haveEventInDatabase( [ 'organizer' => $draft_organizer ] );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->organizers_url, [ 'event' => $event ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->organizers );
		$first = $response->organizers[0];
		$I->assertEquals( $draft_organizer, $first->id );
	}
}
