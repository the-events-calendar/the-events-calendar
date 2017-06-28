<?php

use Step\Restv1\RestGuy as Tester;

class OrganizerByEmptyArchiveCest extends BaseRestCest {
	/**
	 * It should allow searching organizers by empty status
	 * @test
	 */
	public function it_should_allow_searching_organizers_by_empty_status( Tester $I ) {
		$organizer_1     = $I->haveOrganizerInDatabase();
		$organizer_2     = $I->haveOrganizerInDatabase();
		$organizer_3     = $I->haveOrganizerInDatabase();
		$event_one_3 = $I->haveEventInDatabase( [ 'organizer' => $organizer_3 ] );

		$I->sendGET( $this->organizers_url, [
			'has_events' => 'true',
		] );
		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['organizers'] );
		$I->assertEquals( [ $organizer_3 ], array_column( $response['organizers'], 'id' ) );

		$I->sendGET( $this->organizers_url, [
			'has_events' => 'false',
		] );
		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 2, $response['organizers'] );
		$response_ids = array_column( $response['organizers'], 'id' );
		sort( $response_ids );
		$I->assertEquals( [ $organizer_1, $organizer_2 ], $response_ids );
	}

	/**
	 * It should return 404 if no organizers have events
	 * @test
	 */
	public function it_should_return_404_if_no_organizers_are_related_to_the_event( Tester $I ) {
		$I->haveManyEventsInDatabase( 3 );
		$I->haveManyOrganizersInDatabase( 3 );

		$I->sendGET( $this->organizers_url, [ 'has_events' => 'true' ] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 404 if no organizers have no events
	 * @test
	 */
	public function it_should_return_404_if_no_organizers_have_no_events( Tester $I ) {
		$organizer_1 = $I->haveOrganizerInDatabase();
		$I->haveEventInDatabase( [ 'organizer' => $organizer_1 ] );
		$organizer_2 = $I->haveOrganizerInDatabase();
		$I->haveEventInDatabase( [ 'organizer' => $organizer_2 ] );
		$organizer_3 = $I->haveOrganizerInDatabase();
		$I->haveEventInDatabase( [ 'organizer' => $organizer_3 ] );

		$I->sendGET( $this->organizers_url, [ 'has_events' => 'false' ] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should mark organizer as empty if draft or pending events are assigned to it
	 * @test
	 */
	public function it_should_mark_organizer_as_empty_if_draft_or_pending_events_are_assigned_to_it( Tester $I ) {
		$organizer_1         = $I->haveOrganizerInDatabase();
		$organizer_2         = $I->haveOrganizerInDatabase();
		$organizer_3         = $I->haveOrganizerInDatabase();
		$organizer_4         = $I->haveOrganizerInDatabase();
		$draft_event     = $I->haveEventInDatabase( [ 'post_status' => 'draft', 'organizer' => $organizer_1 ] );
		$pending_event   = $I->haveEventInDatabase( [ 'post_status' => 'pending', 'organizer' => $organizer_2 ] );
		$published_event = $I->haveEventInDatabase( [ 'organizer' => $organizer_3 ] );

		$I->sendGET( $this->organizers_url, [ 'has_events' => 'true' ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 1, $response['organizers'] );
		$response_organizers = array_column( $response['organizers'], 'id' );
		$I->assertEquals( [ $organizer_3 ], $response_organizers );
	}
}
