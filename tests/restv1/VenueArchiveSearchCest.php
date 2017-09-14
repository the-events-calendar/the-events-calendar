<?php

use Step\Restv1\RestGuy as Tester;

class VenueArchiveSearchCest extends BaseRestCest {
	/**
	 * It should allow searching venues by title
	 * @test
	 */
	public function it_should_allow_searching_venues_by_title( Tester $I ) {
		$I->haveVenueInDatabase( [ 'post_title' => 'Venue foo' ] );
		$I->haveVenueInDatabase( [ 'post_title' => 'Venue baz' ] );
		$I->haveVenueInDatabase( [ 'post_title' => 'Venue bar' ] );

		$I->sendGET( $this->venues_url, [
			'search' => 'woo',
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
			'search' => 'Venue foo',
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
		$I->haveVenueInDatabase( [ 'post_content' => 'Content foo' ] );
		$I->haveVenueInDatabase( [ 'post_content' => 'Content baz' ] );
		$I->haveVenueInDatabase( [ 'post_content' => 'Content bar' ] );

		$I->sendGET( $this->venues_url, [
			'search' => 'woo',
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
			'search' => 'Content foo',
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
		$I->haveVenueInDatabase( [ 'post_excerpt' => 'Excerpt foo' ] );
		$I->haveVenueInDatabase( [ 'post_excerpt' => 'Excerpt baz' ] );
		$I->haveVenueInDatabase( [ 'post_excerpt' => 'Excerpt bar' ] );

		$I->sendGET( $this->venues_url, [
			'search' => 'woo',
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
			'search' => 'Excerpt foo',
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
		$I->haveVenueInDatabase( [
			                         'post_title'   => 'Venue Foo',
			                         'post_content' => 'lorem dolor',
			                         'post_excerpt' => 'sed nunqua',
			                         'meta_input'   => [
				                         '_VenueAddress'       => '221b Baker Street',
				                         '_VenueCity'          => 'London',
				                         '_VenueProvince'      => 'Greater London',
				                         '_VenueState'         => 'England',
				                         '_VenueStateProvince' => 'England, Greater London',
				                         '_VenueZip'           => '223345',
				                         '_VenuePhone'         => '111111',
			                         ],
		                         ] );
		$I->haveVenueInDatabase( [
			                         'post_title'   => 'Venue Bar',
			                         'post_content' => 'dolor sit',
			                         'post_excerpt' => 'altera via',
			                         'meta_input'   => [
				                         '_VenueAddress'       => '10, Piccadilly Circus',
				                         '_VenueCity'          => 'London',
				                         '_VenueProvince'      => 'Greater London',
				                         '_VenueState'         => 'England',
				                         '_VenueStateProvince' => 'England, Greater London',
				                         '_VenueZip'           => '223345',
				                         '_VenuePhone'         => '22222222',
			                         ],
		                         ] );
		$I->haveVenueInDatabase( [
			                         'post_title'   => 'Venue Baz',
			                         'post_content' => 'sit nunqua',
			                         'post_excerpt' => 'Caesar docet',
			                         'meta_input'   => [
				                         '_VenueAddress'       => '100, Avenue du Temple',
				                         '_VenueCity'          => 'Paris',
				                         '_VenueProvince'      => 'Ile de France',
				                         '_VenueState'         => 'France',
				                         '_VenueStateProvince' => 'France, Ile de France',
				                         '_VenueZip'           => '23443',
				                         '_VenuePhone'         => '3333333',
			                         ],
		                         ] );

		$I->sendGET( $this->venues_url, [
			'search' => 'woo',
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

		$searches = [
			'london'         => 2,
			'3333333'        => 1,
			'temple avenue'  => 1,
			'greater london' => 2,
			'france'         => 1,
			'france, ile de' => 1,
			'223345'         => 2,
		];
		foreach ( $searches as $s => $expected ) {
			$I->sendGET( $this->venues_url, [
				'search' => $s,
			] );

			$I->seeResponseCodeIs( 200 );
			$I->seeResponseIsJson();
			$response = json_decode( $I->grabResponse() );
			$I->assertCount( $expected, $response->venues );
		}
	}
}
