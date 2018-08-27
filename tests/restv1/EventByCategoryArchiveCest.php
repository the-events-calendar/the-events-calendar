<?php

use Tribe__Events__Main as Main;

class EventByCategoryArchiveCest extends BaseRestCest {

	/**
	 * @test
	 * it should return 400 if hitting non existing category
	 */
	public function it_should_return_400_if_hitting_non_existing_category( Restv1Tester $I ) {
		$I->sendGET( $this->events_url, [ 'categories' => [ 'cat1' ] ] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return 200 if hitting empty category archive
	 */
	public function it_should_return_200_if_hitting_empty_category_archive( Restv1Tester $I ) {
		$I->haveTermInDatabase( 'cat1', 'tribe_events_cat', [ 'slug' => 'cat1' ] );

		$I->sendGET( $this->events_url, [ 'categories' => [ 'cat1' ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->events );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
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

	/**
	 * It should allow selecting events by an array of categories
	 *
	 * A logic OR
	 *
	 * @test
	 */
	public function should_allow_selecting_events_by_an_array_of_categories( Restv1Tester $I ) {
		$cat_1_events = $I->haveManyEventsInDatabase( 2, [ 'categories' => [ 'cat1' ] ] );
		$cat_2_events = $I->haveManyEventsInDatabase( 2, [ 'categories' => [ 'cat2' ] ] );
		$I->haveManyEventsInDatabase( 2 );

		$I->sendGET( $this->events_url, [ 'categories' => [ 'cat1', 'cat2' ] ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 4, $response['events'] );
		$I->assertEquals( array_merge( $cat_1_events, $cat_2_events ), array_column( $response['events'], 'id' ) );
	}

	/**
	 * It should allow selecting events by comma separated list of categories
	 *
	 * A logic OR
	 *
	 * @test
	 */
	public function should_allow_selecting_events_by_a_comma_separated_list_of_categories( Restv1Tester $I ) {
		list( $cat_1_term_id, $_ ) = $I->haveTermInDatabase( 'cat1', Main::TAXONOMY, [ 'slug' => 'cat1' ] );
		list( $cat_2_term_id, $_ ) = $I->haveTermInDatabase( 'cat2', Main::TAXONOMY, [ 'slug' => 'cat2' ] );
		$cat_1_events = $I->haveManyEventsInDatabase( 2, [ 'categories' => [ 'cat1' ] ] );
		$cat_2_events = $I->haveManyEventsInDatabase( 2, [ 'categories' => [ 'cat2' ] ] );
		$I->haveManyEventsInDatabase( 2 );

		$I->sendGET( $this->events_url, [ 'categories' => "{$cat_1_term_id},{$cat_2_term_id}" ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertCount( 4, $response['events'] );
		$I->assertEquals( array_merge( $cat_1_events, $cat_2_events ), array_column( $response['events'], 'id' ) );
	}
}
