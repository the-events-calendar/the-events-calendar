<?php

use Step\Restv1\RestGuy as Tester;

class OrganizerSingleCest extends BaseRestCest {
	/**
	 * It should allow getting a organizer by its post ID
	 *
	 * @test
	 */
	public function it_should_allow_getting_a_organizer_by_its_post_id( Tester $I ) {
		$organizer_id = $I->haveOrganizerInDatabase();
		$thumbnail_id = $I->factory()->attachment->create_upload_object( codecept_data_dir( 'images/featured-image.jpg' ) );
		$I->havePostmetaInDatabase( $organizer_id, '_thumbnail_id', $thumbnail_id );

		$I->sendGET( $this->organizers_url . "/{$organizer_id}" );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->seeResponseContainsJson( [ 'id' => $organizer_id ] );
		$expected_keys = [
			'author',
			'date',
			'date_utc',
			'modified',
			'modified_utc',
			'url',
			'organizer',
			'description',
			'excerpt',
			'image',
			'phone',
			'website',
			'email',
			'global_id',
			'global_id_lineage',
		];

		foreach ( $expected_keys as $key ) {
			$I->assertArrayHasKey( $key, $response );
		}
	}

	/**
	 * It should return 400 if trying to get organizer by bad post ID
	 *
	 * @test
	 * @example ["foo"]
	 * @example ["foo bar"]
	 * @example ["foo-bar"]
	 */
	public function it_should_return_404_if_trying_to_get_organizer_by_bad_post_id( Tester $I, \Codeception\Example $data ) {
		$I->sendGET( $this->organizers_url . "/{$data[0]}" );
		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return bad request if trying to get organizer by non existing organizer post ID
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_get_organizer_by_non_existing_organizer_post_id( Tester $I ) {
		$I->sendGET( $this->organizers_url . '/23' );
		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 403 when trying to get organizer that is not public
	 *
	 * @test
	 */
	public function it_should_return_403_when_trying_to_get_organizer_that_is_not_public( Tester $I ) {
		$organizer_id = $I->haveOrganizerInDatabase( [ 'post_status' => 'draft' ] );

		$I->sendGET( $this->organizers_url . "/{$organizer_id}" );

		$I->seeResponseCodeIs( 403 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return the organizer if user can read organizer that is not public
	 *
	 * @test
	 */
	public function it_should_return_the_organizer_if_user_can_read_organizer_that_is_not_public( Tester $I ) {
		$organizer_id = $I->haveOrganizerInDatabase( [ 'post_status' => 'draft' ] );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->organizers_url . "/{$organizer_id}" );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [ 'id' => $organizer_id ] );
	}
}
