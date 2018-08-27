<?php

use Step\Restv1\RestGuy as Tester;

class OrganizerSingleSlugCest extends BaseRestCest {
	/**
	 * It should allow getting a organizer by its post ID
	 *
	 * @test
	 */
	public function it_should_allow_getting_a_organizer_by_its_post_id( Tester $I ) {
		$organizer_id = $I->haveOrganizerInDatabase();
		$thumbnail_id = $I->factory()->attachment->create_upload_object( codecept_data_dir( 'images/featured-image.jpg' ) );
		$I->havePostmetaInDatabase( $organizer_id, '_thumbnail_id', $thumbnail_id );

		$post = get_post( $organizer_id );

		$I->sendGET( $this->organizers_url . "/by-slug/{$post->post_name}" );

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
			'status',
			'url',
			'organizer',
			'description',
			'excerpt',
			'image',
			'phone',
			'website',
			'email',
			'json_ld',
			'global_id',
			'global_id_lineage',
		];

		foreach ( $expected_keys as $key ) {
			$I->assertArrayHasKey( $key, $response );
		}
	}

	/**
	 * It should return bad request if trying to get organizer by non existing organizer post ID
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_get_organizer_by_non_existing_organizer_post_id( Tester $I ) {
		$I->sendGET( $this->organizers_url . '/by-slug/non-existent-slug' );
		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 401 when trying to get organizer that is not public
	 *
	 * @test
	 */
	public function it_should_return_401_when_trying_to_get_organizer_that_is_not_public( Tester $I ) {
		$organizer_id = $I->haveOrganizerInDatabase( [ 'post_status' => 'draft' ] );

		$post = get_post( $organizer_id );

		$I->sendGET( $this->organizers_url . "/by-slug/{$post->post_name}" );

		$I->seeResponseCodeIs( 401 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return the organizer if user can read organizer that is not public
	 *
	 * @test
	 */
	public function it_should_return_the_organizer_if_user_can_read_organizer_that_is_not_public( Tester $I ) {
		$organizer_id = $I->haveOrganizerInDatabase( [ 'post_status' => 'draft' ] );

		$post = get_post( $organizer_id );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendGET( $this->organizers_url . "/by-slug/{$post->post_name}" );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [ 'id' => $organizer_id ] );
	}
}
