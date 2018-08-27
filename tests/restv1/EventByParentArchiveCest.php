<?php


class EventByParentArchiveCest extends BaseRestCest {

	/**
	 * It should return 400 when trying to filter events by bad parent
	 *
	 * @test
	 */
	public function should_return_400_when_trying_to_filter_events_by_bad_parent( Restv1Tester $I ) {
		$I->sendGET( $this->events_url, [ 'post_parent' => 'foo-bar' ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 400when trying to filter events by non existing parent
	 *
	 * @test
	 */
	public function should_return_400_when_trying_to_filter_events_by_non_existing_parent( Restv1Tester $I ) {
		$I->sendGET( $this->events_url, [ 'post_parent' => 23 ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should allow filtering events by parent
	 *
	 * @test
	 */
	public function should_allow_filtering_events_by_parent( Restv1Tester $I ) {
		$parent   = $I->haveEventInDatabase();
		$children = $I->haveManyEventsInDatabase( 2, [ 'post_parent' => $parent ] );
		$I->sendGET( $this->events_url, [ 'post_parent' => $parent ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		/** @var Tribe__Events__REST__Interfaces__Post_Repository $repo */
		$repo            = tribe( 'tec.rest-v1.repository' );
		$expected_events = [
			$repo->get_event_data( $children[0] ),
			$repo->get_event_data( $children[1] ),
		];
		$I->seeResponseContainsJson( [
			'total'       => 2,
			'total_pages' => 1,
			'events'      => $expected_events,
		] );
	}
}
