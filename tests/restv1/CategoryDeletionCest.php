<?php

use Codeception\Example;
use Step\Restv1\RestGuy as Tester;
use Tribe__Events__Main as Main;

class CategoryDeletionCest extends BaseRestCest {

	/**
	 * It should allow deleting an event category
	 *
	 * @test
	 */
	public function should_allow_deleting_an_event_category( Tester $I ) {
		list( $parent ) = $I->haveTermInDatabase( 'foo-parent', Main::TAXONOMY );
		list( $id ) = $I->haveTermInDatabase( 'foo', Main::TAXONOMY, [
			'count'       => 3,
			'parent'      => $parent,
			'description' => 'Term description',
			'slug'        => 'foo-bar',
		] );
		$link = get_term_link( $id, Main::TAXONOMY );

		$I->generate_nonce_for_role( 'editor' );
		$I->sendDELETE( $this->categories_url . "/{$id}" );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );

		$I->assertArrayHasKey( 'id', $response );

		$id       = $response['id'];
		$expected = [
			'id'          => $id,
			'name'        => 'foo',
			'count'       => 3,
			'parent'      => $parent,
			'description' => 'Term description',
			'url'         => $link,
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
	 * It should return 403 if user cannot delete event category
	 *
	 * @test
	 */
	public function should_return_403_if_user_cannot_delete_event_category( Tester $I ) {
		list( $id ) = $I->haveTermInDatabase( 'foo', Main::TAXONOMY );

		$I->generate_nonce_for_role( 'subscriber' );
		$I->sendDELETE( $this->categories_url . "/{$id}" );

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
		$I->haveTermInDatabase( 'foo', Main::TAXONOMY );

		$I->generate_nonce_for_role( 'subscriber' );
		$I->sendDELETE( $this->categories_url . "/{$example[0]}" );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}
}
