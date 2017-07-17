<?php

use Step\Restv1\RestGuy as Tester;
use Tribe__Events__Main as Main;

class CategoryArchiveCest extends BaseRestCest {

	/**
	 * It should return 404 if no event category is in db
	 *
	 * @test
	 */
	public function should_return_404_if_no_event_category_is_in_db( Tester $I ) {
		$I->sendGET( $this->categories_url, [ 'hide_empty' => 'false' ] );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return available event categories
	 *
	 * @test
	 */
	public function should_return_available_event_categories( Tester $I ) {
		$I->haveManyTermsInDatabase( 5, 'Event Category {{n}}', Main::TAXONOMY );

		$I->sendGET( $this->categories_url, [
			'hide_empty' => false
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 5, $response['categories'] );
	}

	/**
	 * It should allow specifying the category archive page to get
	 *
	 * @test
	 */
	public function should_allow_specifying_the_category_archive_page_to_get( Tester $I ) {
		$terms    = $I->haveManyTermsInDatabase( 6, 'Event Category {{n}}', Main::TAXONOMY );
		$term_ids = array_column( $terms, 0 );

		$I->sendGET( $this->categories_url, [
			'hide_empty' => false,
			'per_page'   => 3,
			'page' => 1,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 3, $response['categories'] );
		$page_1_terms_ids = array_column( $response['categories'], 'id' );
		$I->assertCount( 3, array_intersect( $page_1_terms_ids, $term_ids ) );

		$term_ids = array_diff( $term_ids, $page_1_terms_ids );

		$I->sendGET( $this->categories_url, [
			'hide_empty' => false,
			'per_page'   => 3,
			'page'       => 2,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 3, $response['categories'] );
		$page_2_terms_ids = array_column( $response['categories'], 'id' );
		$I->assertCount( 3, array_intersect( $page_2_terms_ids, $term_ids ) );
	}
}
