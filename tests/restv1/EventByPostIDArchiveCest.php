<?php


class EventByPostIDArchiveCest extends BaseRestCest {

	/**
	 * It should return 400 if not all include values are positive integers
	 *
	 * @test
	 */
	public function should_return_400_if_not_all_include_values_are_positive_integers( Restv1Tester $I ) {
		$I->sendGET( $this->events_url, [ 'include' => 'foo-bar,23,89' ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();

		$I->sendGET( $this->events_url, [ 'include' => [ 'foo-bar', 23, 89 ] ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should allow fetching events by post ID
	 *
	 * @test
	 */
	public function should_allow_fetching_events_by_post_id( Restv1Tester $I ) {
		$event_ids = $I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'include' => $event_ids ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		/** @var Tribe__Events__REST__Interfaces__Post_Repository $repo */
		$repo            = tribe( 'tec.rest-v1.repository' );
		$expected_events = [
			$repo->get_event_data( $event_ids[0] ),
			$repo->get_event_data( $event_ids[1] ),
			$repo->get_event_data( $event_ids[2] ),
		];
		$I->seeResponseContainsJson( [
			'total'  => 3,
			'events' => $expected_events,
		] );

		$I->sendGET( $this->events_url, [ 'include' => implode( ',', $event_ids ) ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [
			'total'  => 3,
			'events' => $expected_events,
		] );

		$two_event_ids = array_slice( $event_ids, 0, 2 );
		$I->sendGET( $this->events_url, [ 'include' => implode( ',', $two_event_ids ) ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$expected_events = [
			$repo->get_event_data( $event_ids[0] ),
			$repo->get_event_data( $event_ids[1] ),
		];
		$I->seeResponseContainsJson( [
			'total'  => 2,
			'events' => $expected_events,
		] );
	}
}
