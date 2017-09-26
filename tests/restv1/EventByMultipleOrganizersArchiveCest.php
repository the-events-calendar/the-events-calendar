<?php

use Step\Restv1\RestGuy as Tester;

class EventByMultipleOrganizersArchiveCest extends BaseRestCest {

	/**
	 * It should allow filtering events by an array of organizer IDs
	 *
	 * A logic OR
	 *
	 * @test
	 */
	public function should_allow_filtering_events_by_an_array_of_organizer_i_ds( Tester $I ) {
		list( $organizer_1, $organizer_2, $organizer_3, $organizer_4 ) = $organizers = $I->haveManyOrganizersInDatabase( 4 );

		$organizer_1_events  = $I->haveManyEventsInDatabase( 3, [ 'organizer' => [ $organizer_1 ] ] );
		$organizer_2_events  = $I->haveManyEventsInDatabase( 3, [ 'organizer' => [ $organizer_2 ] ] );
		$organizer_3_events  = $I->haveManyEventsInDatabase( 3, [ 'organizer' => [ $organizer_3 ] ] );
		$events_wo_organizer = $I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'organizer' => [ $organizer_1 ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 3, $response['events'] );
		$I->assertEquals( $organizer_1_events, array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'organizer' => [ $organizer_1, $organizer_2 ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 6, $response['events'] );
		$I->assertEquals( array_merge( $organizer_1_events, $organizer_2_events ), array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'organizer' => [ $organizer_1, $organizer_2, $organizer_3 ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 9, $response['events'] );
		$I->assertEquals( array_merge( $organizer_1_events, $organizer_2_events, $organizer_3_events ), array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'organizer' => [ $organizer_1, $organizer_2, $organizer_3, $organizer_4 ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 9, $response['events'] );
		$I->assertEquals( array_merge( $organizer_1_events, $organizer_2_events, $organizer_3_events ), array_column( $response['events'], 'id' ) );
	}

	/**
	 * It should allow filtering events by a comma separated list of organizer IDs
	 *
	 * A logic OR
	 *
	 * @test
	 */
	public function should_allow_filtering_events_by_a_comma_separated_list_of_organizer_i_ds( Tester $I ) {
		list( $organizer_1, $organizer_2, $organizer_3, $organizer_4 ) = $organizers = $I->haveManyOrganizersInDatabase( 4 );

		$organizer_1_events  = $I->haveManyEventsInDatabase( 3, [ 'organizer' => [ $organizer_1 ] ] );
		$organizer_2_events  = $I->haveManyEventsInDatabase( 3, [ 'organizer' => [ $organizer_2 ] ] );
		$organizer_3_events  = $I->haveManyEventsInDatabase( 3, [ 'organizer' => [ $organizer_3 ] ] );
		$events_wo_organizer = $I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'organizer' => $organizer_1 ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 3, $response['events'] );
		$I->assertEquals( $organizer_1_events, array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'organizer' => implode( ',', [ $organizer_1, $organizer_2 ] ) ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 6, $response['events'] );
		$I->assertEquals( array_merge( $organizer_1_events, $organizer_2_events ), array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'organizer' => implode( ',', [ $organizer_1, $organizer_2, $organizer_3 ] ) ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 9, $response['events'] );
		$I->assertEquals( array_merge( $organizer_1_events, $organizer_2_events, $organizer_3_events ), array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'organizer' => implode( ',', [ $organizer_1, $organizer_2, $organizer_3, $organizer_4 ] ) ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 9, $response['events'] );
		$I->assertEquals( array_merge( $organizer_1_events, $organizer_2_events, $organizer_3_events ), array_column( $response['events'], 'id' ) );
	}
}
