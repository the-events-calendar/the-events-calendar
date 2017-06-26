<?php

use Step\Restv1\RestGuy as Tester;

class VenueArchiveSearchCest extends BaseRestCest {
	/**
	 * It should allow searching venues by title
	 * @test
	 */
	public function it_should_allow_searching_venues_by_title( Tester $I ) {
		$I->haveVenueInDatabase( [ 'post_title' => 'Venue 1' ] );
		$I->haveVenueInDatabase( [ 'post_title' => 'Venue 2' ] );
		$I->haveVenueInDatabase( [ 'post_title' => 'Venue 3' ] );

		$I->sendGET( $this->venues_url, [
			'search' => 'foo',
		] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();

		$I->sendGET( $this->venues_url, [
			'search' => 'Venue',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->venues );

		$I->sendGET( $this->venues_url, [
			'search' => 'Venue 1',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->venues );
	}

	/**
	 * It should allow searching venues by description
	 * @test
	 */
	public function it_should_allow_searching_venues_by_description( Tester $I ) {
		$I->haveVenueInDatabase( [ 'post_content' => 'Content 1' ] );
		$I->haveVenueInDatabase( [ 'post_content' => 'Content 2' ] );
		$I->haveVenueInDatabase( [ 'post_content' => 'Content 3' ] );

		$I->sendGET( $this->venues_url, [
			'search' => 'foo',
		] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();

		$I->sendGET( $this->venues_url, [
			'search' => 'Content',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->venues );

		$I->sendGET( $this->venues_url, [
			'search' => 'Content 1',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->venues );
	}

	/**
	 * It should allow searching venues by excerpt
	 * @test
	 */
	public function it_should_allow_searching_venues_by_excerpt( Tester $I ) {
		$I->haveVenueInDatabase( [ 'post_excerpt' => 'Excerpt 1' ] );
		$I->haveVenueInDatabase( [ 'post_excerpt' => 'Excerpt 2' ] );
		$I->haveVenueInDatabase( [ 'post_excerpt' => 'Excerpt 3' ] );

		$I->sendGET( $this->venues_url, [
			'search' => 'foo',
		] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();

		$I->sendGET( $this->venues_url, [
			'search' => 'Excerpt',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->venues );

		$I->sendGET( $this->venues_url, [
			'search' => 'Excerpt 1',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 1, $response->venues );
	}

	/**
	 * It should allow searching venues by custom fields
	 * @test
	 */
	public function it_should_allow_searching_venues_by_custom_fields( Tester $I ) {
	}
}
