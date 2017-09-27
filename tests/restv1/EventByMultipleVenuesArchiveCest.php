<?php

use Step\Restv1\RestGuy as Tester;

class EventByMultipleVenuesArchiveCest extends BaseRestCest {

	/**
	 * It should allow filtering events by an array of venue IDs
	 *
	 * A logic OR
	 *
	 * @test
	 */
	public function should_allow_filtering_events_by_an_array_of_venue_i_ds( Tester $I ) {
		list( $venue_1, $venue_2, $venue_3, $venue_4 ) = $venues = $I->haveManyVenuesInDatabase( 4 );

		$venue_1_events  = $I->haveManyEventsInDatabase( 3, [ 'venue' => $venue_1 ] );
		$venue_2_events  = $I->haveManyEventsInDatabase( 3, [ 'venue' => $venue_2 ] );
		$venue_3_events  = $I->haveManyEventsInDatabase( 3, [ 'venue' => $venue_3 ] );
		$events_wo_venue = $I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'venue' => [ $venue_1 ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 3, $response['events'] );
		$I->assertEquals( $venue_1_events, array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'venue' => [ $venue_1, $venue_2 ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 6, $response['events'] );
		$I->assertEquals( array_merge( $venue_1_events, $venue_2_events ), array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'venue' => [ $venue_1, $venue_2, $venue_3 ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 9, $response['events'] );
		$I->assertEquals( array_merge( $venue_1_events, $venue_2_events, $venue_3_events ), array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'venue' => [ $venue_1, $venue_2, $venue_3, $venue_4 ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 9, $response['events'] );
		$I->assertEquals( array_merge( $venue_1_events, $venue_2_events, $venue_3_events ), array_column( $response['events'], 'id' ) );
	}

	/**
	 * It should allow filtering events by a comma separated list of venue IDs
	 *
	 * A logic OR
	 *
	 * @test
	 */
	public function should_allow_filtering_events_by_a_comma_separated_list_of_venue_i_ds( Tester $I ) {
		list( $venue_1, $venue_2, $venue_3, $venue_4 ) = $venues = $I->haveManyVenuesInDatabase( 4 );

		$venue_1_events  = $I->haveManyEventsInDatabase( 3, [ 'venue' => $venue_1 ] );
		$venue_2_events  = $I->haveManyEventsInDatabase( 3, [ 'venue' => $venue_2 ] );
		$venue_3_events  = $I->haveManyEventsInDatabase( 3, [ 'venue' => $venue_3 ] );
		$events_wo_venue = $I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'venue' => $venue_1 ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 3, $response['events'] );
		$I->assertEquals( $venue_1_events, array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'venue' => implode( ',', [ $venue_1, $venue_2 ] ) ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 6, $response['events'] );
		$I->assertEquals( array_merge( $venue_1_events, $venue_2_events ), array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'venue' => implode( ',', [ $venue_1, $venue_2, $venue_3 ] ) ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 9, $response['events'] );
		$I->assertEquals( array_merge( $venue_1_events, $venue_2_events, $venue_3_events ), array_column( $response['events'], 'id' ) );

		$I->sendGET( $this->events_url, [ 'venue' => implode( ',', [ $venue_1, $venue_2, $venue_3, $venue_4 ] ) ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 9, $response['events'] );
		$I->assertEquals( array_merge( $venue_1_events, $venue_2_events, $venue_3_events ), array_column( $response['events'], 'id' ) );
	}
}
