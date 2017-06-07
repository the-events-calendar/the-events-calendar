<?php

use Step\Restv1\RestGuy as Tester;

class VenueSingleCest extends BaseRestCest {
	/**
	 * It should allow getting a venue by its post ID
	 *
	 * @test
	 */
	public function it_should_allow_getting_a_venue_by_its_post_id( Tester $I ) {
		$venue_id = $I->haveVenueInDatabase();
		$thumbnail_id = tribe_upload_image( codecept_data_dir( 'images/featured-image.jpg' ) );
		$I->havePostmetaInDatabase( $venue_id, '_thumbnail_id', $thumbnail_id );

		$I->sendGET( $this->venues_url . "/{$venue_id}" );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->seeResponseContainsJson( [ 'id' => $venue_id ] );
		$expected_keys = [
			'author',
			'date',
			'date_utc',
			'modified',
			'modified_utc',
			'url',
			'venue',
			'description',
			'excerpt',
			'show_map',
			'show_map_link',
			'address',
			'city',
			'country',
			'province',
			'state',
			'zip',
			'phone',
			'website',
			'stateprovince',
			'global_id',
			'global_id_lineage',
			'image',
		];

		foreach ( $expected_keys as $key ) {
			$I->assertArrayHasKey( $key, $response );
		}
	}
}
