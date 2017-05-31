<?php

use Step\Restv1\RestGuy as Tester;

class FeaturedEventArchiveCest extends BaseRestCest {
	/**
	 * It should return 404 if no event is featured
	 *
	 * @test
	 */
	public function it_should_return_404_if_no_event_is_featured( Tester $I ) {
		$I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'featured' => true ] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return featured events if filtering by featured events
	 *
	 * @test
	 */
	public function it_should_return_featured_events_if_filtering_by_featured_events( Tester $I ) {
		$I->haveManyEventsInDatabase( 3, [ 'meta_input' => [ Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => true ] ] );

		$I->sendGET( $this->events_url, [ 'featured' => true ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}

	/**
	 * It should return non featured events if filtering by non filtering events
	 *
	 * @test
	 */
	public function it_should_return_non_featured_events_if_filtering_by_non_filtering_events( Tester $I ) {
		$I->haveManyEventsInDatabase( 3, [ 'meta_input' => [ Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => true ] ] );
		$non_featured = $I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url, [ 'featured' => false ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
		$I->assertEquals( $non_featured, array_column( $response->events, 'id' ) );
	}

	/**
	 * It should not return non published featured events
	 *
	 * @test
	 */
	public function it_should_not_return_non_published_featured_events( Tester $I ) {
		$featured = $I->haveManyEventsInDatabase( 3, [ 'meta_input' => [ Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => true ] ] );
		$I->haveManyEventsInDatabase( 3, [ 'post_status' => 'draft' ] );

		$I->sendGET( $this->events_url, [ 'featured' => true ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
		$I->assertEquals( $featured, array_column( $response->events, 'id' ) );
	}

	/**
	 * It should return non published featured events is user can edit events
	 *
	 * @test
	 */
	public function it_should_return_non_published_featured_events_is_user_can_edit_events( Tester $I ) {
		$featured = $I->haveManyEventsInDatabase( 3, [ 'meta_input' => [ Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => true ] ] );
		$featured_drafts = $I->haveManyEventsInDatabase( 3, [
			'post_status' => 'draft',
			'meta_input'  => [ Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => true ]
		] );

		$I->haveHttpHeader( 'X-WP-Nonce', $I->generate_nonce_for_role( 'editor' ) );
		$I->sendGET( $this->events_url, [ 'featured' => true ] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 6, $response->events );
		$I->assertEquals( array_merge( $featured, $featured_drafts ), array_column( $response->events, 'id' ) );
	}

	/**
	 * It should return featured and non featured events if the featured query var is not specified
	 *
	 * @test
	 */
	public function it_should_return_featured_and_non_featured_events_if_the_featured_query_var_is_not_specified(Tester $I) {
		$featured = $I->haveManyEventsInDatabase( 3, [ 'meta_input' => [ Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => true ] ] );
		$not_featured = $I->haveManyEventsInDatabase( 3 );

		$I->sendGET( $this->events_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 6, $response->events );
		$I->assertEquals( array_merge( $featured, $not_featured ), array_column( $response->events, 'id' ) );
	}
}
