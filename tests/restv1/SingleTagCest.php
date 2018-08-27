<?php

use Codeception\Example;
use Step\Restv1\RestGuy as Tester;

class SingleTagCest extends BaseRestCest {

	/**
	 * It should allow getting a single tag term
	 *
	 * @test
	 */
	public function should_allow_getting_a_single_tag_term( Tester $I ) {
		list( $id ) = $I->haveTermInDatabase( 'foo', 'post_tag', [
			'description' => 'Term description',
			'slug'        => 'foo-bar',
			'count'       => 2,
		] );

		$url = $this->tags_url . "/{$id}";
		$I->sendGET( $url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$expected = [
			'id'          => $id,
			'name'        => 'foo',
			'count'       => 2,
			'description' => 'Term description',
			'url'         => get_term_link( $id, 'post_tag' ),
			'slug'        => 'foo-bar',
			'taxonomy'    => 'post_tag',
			'meta'        => [],
			'urls'        => [
				'self'       => $url,
				'collection' => $this->tags_url,
			],
		];
		foreach ( $expected as $key => $value ) {
			$I->seeResponseContainsJson( [ $key => $value ] );
		}
	}

	/**
	 * It should return bad request if trying to get non existing single tag term
	 *
	 * @test
	 */
	public function should_return_bad_request_if_trying_to_get_non_existing_single_tag_term( Tester $I ) {
		list( $id ) = $I->haveTermInDatabase( 'foo', 'foo-tax' );

		$I->sendGET( $this->tags_url . "/{$id}" );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();

		$url = $this->tags_url . "/2389";
		$I->sendGET( $url );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 404 if supplying not int values for single tag term
	 *
	 * @test
	 * @example ["foo"]
	 * @example ["foo bar"]
	 * @example [""]
	 */
	public function should_return_404_if_supplying_not_int_values_for_single_tag_term( Tester $I, Example $example ) {
		$url = $this->tags_url . "/{$example[0]}";
		$I->sendGET( $url );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}
}
