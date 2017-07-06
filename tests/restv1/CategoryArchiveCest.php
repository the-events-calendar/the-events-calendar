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
		$I->sendGET( $this->categories_url );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return available event categories
	 *
	 * @test
	 */
	public function should_return_available_event_categories( Tester $I ) {
		$I->haveManyTermsInDatabase( 20, 'Event Category {{n}}', Main::TAXONOMY );

		$I->sendGET( $this->categories_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 20, $response['categories'] );
	}
}
