<?php

use Codeception\Example;
use Step\Restv1\RestGuy as Tester;
use Tribe__Events__Main as Main;

class SingleCategoryCest extends BaseRestCest {

	/**
	 * It should allow getting a single category term
	 *
	 * @test
	 */
	public function should_allow_getting_a_single_category_term( Tester $I ) {
		list( $parent ) = $I->haveTermInDatabase( 'foo-parent', Main::TAXONOMY );
		list( $id ) = $I->haveTermInDatabase( 'foo', Main::TAXONOMY, [
			'description' => 'Term description',
			'slug'        => 'foo-bar',
			'parent'      => $parent,
			'count'       => 2,
		] );

		$url = $this->categories_url . "/{$id}";
		$I->sendGET( $url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$expected = [
			'id'          => $id,
			'name'        => 'foo',
			'count'       => 2,
			'description' => 'Term description',
			'url'         => get_term_link( $id, Main::TAXONOMY ),
			'slug'        => 'foo-bar',
			'taxonomy'    => Main::TAXONOMY,
			'parent'      => $parent,
			'meta'        => [],
			'urls'        => [
				'self'       => $url,
				'collection' => $this->categories_url,
				'up'         => $this->categories_url . "/{$parent}",
			],
		];
		foreach ( $expected as $key => $value ) {
			$I->seeResponseContainsJson( [ $key => $value ] );
		}
	}

	/**
	 * It should return bad request if trying to get non existing single category term
	 *
	 * @test
	 */
	public function should_return_bad_request_if_trying_to_get_non_existing_single_category_term( Tester $I ) {
		list( $id ) = $I->haveTermInDatabase( 'foo', 'foo-tax' );

		$I->sendGET( $this->categories_url . "/{$id}" );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();

		$url = $this->categories_url . "/2389";
		$I->sendGET( $url );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 404 if supplying not int values for single category term
	 *
	 * @test
	 * @example ["foo"]
	 * @example ["foo bar"]
	 * @example [""]
	 */
	public function should_return_404_if_supplying_not_int_values_for_single_category_term( Tester $I, Example $example ) {
		$url = $this->categories_url . "/{$example[0]}";
		$I->sendGET( $url );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}
}
