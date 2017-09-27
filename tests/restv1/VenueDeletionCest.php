<?php

use Step\Restv1\RestGuy as Tester;
use Tribe__Image__Uploader as Image;
use Tribe__Timezones as Timezones;

class VenueDeletionCest extends BaseRestCest {
	/**
	 * It should return 400 if trying to delete venue passing bad venue ID
	 * @test
	 */
	public function it_should_return_400_if_trying_to_delete_venue_passing_bad_venue_id( Tester $I ) {
		// pass an ID that does not exist
		$I->sendDELETE( $this->venues_url . "/23" );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 403 if user cannot delete venues
	 * @test
	 */
	public function it_should_return_403_if_user_cannot_delete_venues( Tester $I ) {
		$id = $I->haveVenueInDatabase();

		$I->sendDELETE( $this->venues_url . "/{$id}" );

		$I->seeResponseCodeIs( 403 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 200 when deleting venue
	 * @test
	 */
	public function it_should_return_200_when_deleting_venue( Tester $I ) {
		$id = $I->haveVenueInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendDELETE( $this->venues_url . "/{$id}" );

		$I->seeResponseCodeIs( 200 );

		$I->seeResponseContainsJson( [ 'id' => $id ] );
	}

	/**
	 * It should return 410 when re-deleting a deleted venue
	 * @test
	 */
	public function it_should_return_410_when_re_deleting_a_deleted_venue( Tester $I ) {
		$id = $I->haveVenueInDatabase( [ 'post_status' => 'trash' ] );

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendDELETE( $this->venues_url . "/{$id}" );

		$I->seeResponseCodeIs( 410 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should remove the venue from related events when deleting it
	 * @test
	 */
	 public function it_should_remove_the_venue_from_related_events_when_deleting_it(Tester $I) {
		 $venue_id = $I->haveVenueInDatabase();
		 $event_id = $I->haveEventInDatabase( [ 'venue' => $venue_id ] );

		 $venue_meta_criteria = [ 'post_id' => $event_id, 'meta_key' => '_EventVenueID', 'meta_value' => $venue_id ];

		 $I->seePostMetaInDatabase( $venue_meta_criteria );

		 $I->generate_nonce_for_role( 'administrator' );

		 $I->sendDELETE( $this->venues_url . "/{$venue_id}" );

		 $I->seeResponseCodeIs( 200 );
		 $I->seeResponseIsJson();

		 $I->dontSeePostMetaInDatabase( $venue_meta_criteria );
	 }
}
