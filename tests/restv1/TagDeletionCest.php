<?php

use Codeception\Example;
use Step\Restv1\RestGuy as Tester;
use Tribe__Events__Main as Main;

class TagDeletionCest extends BaseRestCest {

	/**
	 * It should allow deleting an event tag
	 *
	 * @test
	 */
	public function should_allow_deleting_an_event_tag( Tester $I ) {
		list( $id ) = $I->haveTermInDatabase( 'foo', 'post_tag', [
			'count'       => 3,
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		] );
		$link = get_term_link( $id, 'post_tag' );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendDELETE( $this->tags_url . "/{$id}" );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );

		$I->assertArrayHasKey( 'id', $response );

		$id       = $response['id'];
		$expected = [
			'id'          => $id,
			'name'        => 'foo',
			'count'       => 3,
			'description' => 'Term description',
			'url'         => $link,
			'slug'        => 'foo-bar',
			'taxonomy'    => 'post_tag',
			'meta'        => [],
			'urls'        => [
				'self'       => $this->tags_url . "/{$id}",
				'collection' => $this->tags_url,
			],
		];
		foreach ( $expected as $key => $value ) {
			$I->seeResponseContainsJson( [ $key => $value ] );
		}
	}

	/**
	 * It should return 403 if user cannot delete event tag
	 *
	 * @test
	 */
	public function should_return_403_if_user_cannot_delete_event_tag( Tester $I ) {
		list( $id ) = $I->haveTermInDatabase( 'foo', 'post_tag' );

		$I->generate_nonce_for_role( 'subscriber' );
		$I->sendDELETE( $this->tags_url . "/{$id}" );

		$I->seeResponseCodeIs( 403 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return bad request if term ID parameter is bad
	 *
	 * @test
	 * @example [23]
	 * @example ["23"]
	 */
	public function should_return_bad_request_if_term_id_parameter_is_bad( Tester $I, Example $example ) {
		$I->haveTermInDatabase( 'foo', 'post_tag' );

		$I->generate_nonce_for_role( 'subscriber' );
		$I->sendDELETE( $this->tags_url . "/{$example[0]}" );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}
}
