<?php

use Codeception\Example;
use Step\Restv1\RestGuy as Tester;

class TagUpdateCest extends BaseRestCest {

	/**
	 * It should allow updating an event tag
	 *
	 * @test
	 */
	public function should_allow_updating_an_event_tag( Tester $I ) {
		list( $id ) = $I->haveTermInDatabase( 'old-foo', 'post_tag' );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendPOST( $this->tags_url . "/{$id}", [
			'name'        => 'foo',
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );

		$I->assertArrayHasKey( 'id', $response );

		$id       = $response['id'];
		$expected = [
			'id'          => $id,
			'name'        => 'foo',
			'count'       => 0,
			'description' => 'Term description',
			'url'         => get_term_link( $id, 'post_tag' ),
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
	 * It should return 403 if user cannot update terms
	 *
	 * @test
	 */
	public function should_return_403_if_user_cannot_update_terms( Tester $I ) {
		list( $id ) = $I->haveTermInDatabase( 'old-foo', 'post_tag' );

		$I->generate_nonce_for_role( 'subscriber' );
		$I->sendPOST( $this->tags_url . "/{$id}", [
			'name'        => 'foo',
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		] );

		$I->seeResponseCodeIs( 403 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return bad request if passing bad request parameters
	 *
	 * @test
	 * @example ["name", ""]
	 * @example ["description", ""]
	 * @example ["slug", ""]
	 */
	public function should_return_bad_request_if_passing_bad_request_parameters( Tester $I, Example $example ) {
		list( $id ) = $I->haveTermInDatabase( 'old-foo', 'post_tag' );

		$I->generate_nonce_for_role( 'editor' );
		$params = [
			'name'        => 'foo',
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		];
		$I->sendPOST( $this->tags_url . "/{$id}", array_merge( $params, [ $example[0] => $example[1] ] ) );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}
}
