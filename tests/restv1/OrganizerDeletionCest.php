<?php

use Step\Restv1\RestGuy as Tester;
use Tribe__Image__Uploader as Image;
use Tribe__Timezones as Timezones;

class OrganizerDeletionCest extends BaseRestCest {
	/**
	 * It should return 400 if trying to delete organizer passing bad organizer ID
	 * @test
	 */
	public function it_should_return_400_if_trying_to_delete_organizer_passing_bad_organizer_id( Tester $I ) {
		// pass an ID that does not exist
		$I->sendDELETE( $this->organizers_url . "/23" );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 401 if user cannot delete organizers
	 * @test
	 */
	public function it_should_return_401_if_user_cannot_delete_organizers( Tester $I ) {
		$id = $I->haveOrganizerInDatabase();

		$I->sendDELETE( $this->organizers_url . "/{$id}" );

		$I->seeResponseCodeIs( 401 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 200 when deleting organizer
	 * @test
	 */
	public function it_should_return_200_when_deleting_organizer( Tester $I ) {
		$id = $I->haveOrganizerInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendDELETE( $this->organizers_url . "/{$id}" );

		$I->seeResponseCodeIs( 200 ); $I->seeResponseContainsJson( [ 'id' => $id ] );
	}

	/**
	 * It should return 410 when re-deleting a deleted organizer
	 * @test
	 */
	public function it_should_return_410_when_re_deleting_a_deleted_organizer( Tester $I ) {
		$id = $I->haveOrganizerInDatabase( [ 'post_status' => 'trash' ] );

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendDELETE( $this->organizers_url . "/{$id}" );

		$I->seeResponseCodeIs( 410 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should remove the organizer from related events when deleting it
	 * @test
	 */
	 public function it_should_remove_the_organizer_from_related_events_when_deleting_it(Tester $I) {
		 $organizer_id = $I->haveOrganizerInDatabase();
		 $event_id = $I->haveEventInDatabase( [ 'organizer' => $organizer_id ] );

		 $organizer_meta_criteria = [ 'post_id' => $event_id, 'meta_key' => '_EventOrganizerID', 'meta_value' => $organizer_id ];

		 $I->seePostMetaInDatabase( $organizer_meta_criteria );

		 $I->generate_nonce_for_role( 'administrator' );

		 $I->sendDELETE( $this->organizers_url . "/{$organizer_id}" );

		 $I->seeResponseCodeIs( 200 );
		 $I->seeResponseIsJson();

		 $I->dontSeePostMetaInDatabase( $organizer_meta_criteria );
	 }
}
