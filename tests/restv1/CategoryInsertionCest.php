<?php

use Codeception\Example;
use Step\Restv1\RestGuy as Tester;
use Tribe__Events__Main as Main;

class CategoryInsertionCest extends BaseRestCest {

	/**
	 * It should allow inserting an event category
	 *
	 * @test
	 */
	public function should_allow_inserting_an_event_category( Tester $I ) {
		list( $parent ) = $I->haveTermInDatabase( 'foo-parent', Main::TAXONOMY );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendPOST( $this->categories_url, [
			'parent'      => $parent,
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
			'parent'      => $parent,
			'description' => 'Term description',
			'url'         => get_term_link( $id, Main::TAXONOMY ),
			'slug'        => 'foo-bar',
			'taxonomy'    => Main::TAXONOMY,
			'meta'        => [],
			'urls'        => [
				'self'       => $this->categories_url . "/{$id}",
				'collection' => $this->categories_url,
				'up'         => $this->categories_url . "/{$parent}",
			],
		];
		foreach ( $expected as $key => $value ) {
			$I->seeResponseContainsJson( [ $key => $value ] );
		}
	}

	/**
	 * It should return 403 if user cannot insert terms
	 *
	 * @test
	 */
	public function should_return_403_if_user_cannot_insert_terms( Tester $I ) {
		$I->generate_nonce_for_role( 'subscriber' );
		$I->sendPOST( $this->categories_url, [
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
	 * @example ["parent", ""]
	 * @example ["parent", "2389"]
	 * @example ["parent", "foo"]
	 */
	public function should_return_bad_request_if_passing_bad_request_parameters( Tester $I, Example $example ) {
		$I->generate_nonce_for_role( 'editor' );
		$params = [
			'name'        => 'foo',
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		];
		$I->sendPOST( $this->categories_url, array_merge( $params, [ $example[0] => $example[1] ] ) );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}
}
