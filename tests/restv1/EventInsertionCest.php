<?php

use Step\Restv1\Auth as Tester;

class EventInsertionCest extends BaseRestCest {
	/**
	 * It should allow inserting an event
	 *
	 * @test
	 */
	public function it_should_allow_inserting_an_event( Tester $I ) {
		$I->authenticate_with_role( 'administrator' );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( 'tomorrow 9am' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( 'tomorrow 11am' ) ),
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'       => 'An event',
			'description' => trim( apply_filters( 'the_content', 'An event content' ) ),
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( 'tomorrow 9am' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( 'tomorrow 11am' ) ),
		] );
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'id', $response );
	}
}
