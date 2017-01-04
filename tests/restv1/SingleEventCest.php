<?php


class SingleEventCest extends BaseRestCest {

	/**
	 * @test
	 * it should return a bad request status if hitting a non existing single event endpoint
	 */
	public function it_should_return_a_bad_request_status_if_hitting_a_non_existing_single_event_endpoint( Restv1Tester $I ) {
		$I->sendGET( $this->events_url . '/13' );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return a not found status if id is not of an event
	 */
	public function it_should_return_a_not_found_status_if_id_is_not_of_an_event( Restv1Tester $I ) {
		$id = $I->havePostInDatabase();
		$I->sendGET( $this->events_url . '/' . $id );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return invalid auth status if event is not accessible
	 */
	public function it_should_return_invalid_auth_status_if_event_is_not_accessible( Restv1Tester $I ) {
		$id = $I->haveEventInDatabase( [ 'post_status' => 'draft' ] );
		$I->sendGET( $this->events_url . '/' . $id );

		$I->seeResponseCodeIs( 403 );
		$I->seeResponseIsJson();
	}
}
