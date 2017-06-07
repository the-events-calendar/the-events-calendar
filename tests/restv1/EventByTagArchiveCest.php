<?php


class TagArchiveCest extends BaseRestCest {

	/**
	 * @test
	 * it should return 400 if hitting non existing tag
	 */
	public function it_should_return_400_if_hitting_non_existing_tag(Restv1Tester $I) {
		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1' ] ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}
	/**
	 * @test
	 * it should return 404 if hitting empty tag archive
	 */
	public function it_should_return_404_if_hitting_empty_tag_archive( Restv1Tester $I ) {
		$I->haveTermInDatabase( 'tag1', 'post_tag', [ 'slug' => 'tag1' ] );

		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1' ] ] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return events for the tag if existing
	 */
	public function it_should_return_events_for_the_tag_if_existing( Restv1Tester $I ) {
		$I->haveManyEventsInDatabase( 3, [ 'tags' => [ 'tag1' ] ] );
		$I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1' ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}

	/**
	 * @test
	 * it should allow pagination on tag archive
	 */
	public function it_should_allow_pagination_on_tag_archive( Restv1Tester $I ) {
		$I->haveManyEventsInDatabase( 4, [ 'tags' => [ 'tag1' ] ] );
		$I->haveManyEventsInDatabase( 4 );

		$I->sendGET( $this->events_url, [ 'tags' => [ 'tag1' ], 'per_page' => 3, 'page' => 2 ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->events );
	}
}
