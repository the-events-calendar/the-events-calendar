<?php


class CategoryArchiveCest extends BaseRestCest {

	/**
	 * @test
	 * it should return 400 if hitting non existing category
	 */
	public function it_should_return_400_if_hitting_non_existing_category(Restv1Tester $I) {
		$I->sendGET( $this->events_url, [ 'categories' => [ 'cat1' ] ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}
	/**
	 * @test
	 * it should return 404 if hitting empty category archive
	 */
	public function it_should_return_404_if_hitting_empty_category_archive( Restv1Tester $I ) {
		$I->haveTermInDatabase( 'cat1', 'tribe_events_cat', [ 'slug' => 'cat1' ] );

		$I->sendGET( $this->events_url, [ 'categories' => [ 'cat1' ] ] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return events for the category if existing
	 */
	public function it_should_return_events_for_the_category_if_existing( Restv1Tester $I ) {
		$I->haveManyEventsInDatabase( 3, [ 'categories' => [ 'cat1' ] ] );
		$I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'categories' => [ 'cat1' ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}

	/**
	 * @test
	 * it should allow pagination on category archive
	 */
	public function it_should_allow_pagination_on_category_archive( Restv1Tester $I ) {
		$I->haveManyEventsInDatabase( 4, [ 'categories' => [ 'cat1' ] ] );
		$I->haveManyEventsInDatabase( 4 );

		$I->sendGET( $this->events_url, [ 'categories' => [ 'cat1' ], 'per_page' => 3, 'page' => 2 ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->events );
	}
}
