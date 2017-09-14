<?php

use Step\Restv1\RestGuy as Tester;

class VenueSingleCest extends BaseRestCest {
	/**
	 * It should allow getting a venue by its post ID
	 *
	 * @test
	 */
	public function it_should_allow_getting_a_venue_by_its_post_id( Tester $I ) {
		$venue_id = $I->haveVenueInDatabase();
		$thumbnail_id = $I->factory()->attachment->create_upload_object( codecept_data_dir( 'images/featured-image.jpg' ) );
		$I->havePostmetaInDatabase( $venue_id, '_thumbnail_id', $thumbnail_id );

		$I->sendGET( $this->venues_url . "/{$venue_id}" );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->seeResponseContainsJson( [ 'id' => $venue_id ] );
		$expected_keys = [
			'author',
			'date',
			'date_utc',
			'modified',
			'modified_utc',
			'url',
			'venue',
			'description',
			'excerpt',
			'show_map',
			'show_map_link',
			'address',
			'city',
			'country',
			'province',
			'state',
			'zip',
			'phone',
			'website',
			'stateprovince',
			'global_id',
			'global_id_lineage',
			'image',
		];

		foreach ( $expected_keys as $key ) {
			$I->assertArrayHasKey( $key, $response );
		}
	}

	/**
	 * It should return 400 if trying to get venue by bad post ID
	 *
	 * @test
	 * @example ["foo"]
	 * @example ["foo bar"]
	 * @example ["foo-bar"]
	 */
	public function it_should_return_404_if_trying_to_get_venue_by_bad_post_id( Tester $I, \Codeception\Example $data ) {
		$I->sendGET( $this->venues_url . "/{$data[0]}" );
		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return bad request if trying to get venue by non existing venue post ID
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_get_venue_by_non_existing_venue_post_id( Tester $I ) {
		$I->sendGET( $this->venues_url . '/23' );
		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 403 when trying to get venue that is not public
	 *
	 * @test
	 */
	public function it_should_return_403_when_trying_to_get_venue_that_is_not_public( Tester $I ) {
		$venue_id = $I->haveVenueInDatabase( [ 'post_status' => 'draft' ] );

		$I->sendGET( $this->venues_url . "/{$venue_id}" );

		$I->seeResponseCodeIs( 403 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return the venue if user can read venue that is not public
	 *
	 * @test
	 */
	public function it_should_return_the_venue_if_user_can_read_venue_that_is_not_public( Tester $I ) {
		$venue_id = $I->haveVenueInDatabase( [ 'post_status' => 'draft' ] );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->venues_url . "/{$venue_id}" );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [ 'id' => $venue_id ] );
	}
}
