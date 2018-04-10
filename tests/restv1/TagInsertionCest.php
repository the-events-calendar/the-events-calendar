<?php

use Codeception\Example;
use Step\Restv1\RestGuy as Tester;

class TagInsertionCest extends BaseRestCest {

	/**
	 * It should allow inserting an event tag
	 *
	 * @test
	 */
	public function should_allow_inserting_an_event_tag( Tester $I ) {
		$I->generate_nonce_for_role( 'editor' );
		$I->sendPOST( $this->tags_url, [
			'name'        => 'foo',
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		] );

		$I->seeResponseCodeIs( 201 );
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
	 * It should return 401 if user cannot insert terms
	 *
	 * @test
	 */
	public function should_return_401_if_user_cannot_insert_terms( Tester $I ) {
		$I->generate_nonce_for_role( 'subscriber' );
		$I->sendPOST( $this->tags_url, [
			'name'        => 'foo',
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		] );

		$I->seeResponseCodeIs( 401 );
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
		$I->generate_nonce_for_role( 'editor' );
		$params = [
			'name'        => 'foo',
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		];
		$I->sendPOST( $this->tags_url, array_merge( $params, [ $example[0] => $example[1] ] ) );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}
}
