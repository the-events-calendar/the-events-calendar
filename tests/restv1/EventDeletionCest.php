<?php

use Step\Restv1\RestGuy as Tester;
use Tribe__Image__Uploader as Image;
use Tribe__Timezones as Timezones;

class EventDeletionCest extends BaseRestCest {
	/**
	 * It should return 400 if trying to delete event passing bad event ID
	 * @test
	 *
	 * @example ["23"]
	 * @example ["foo"]
	 */
	public function it_should_return_400_if_trying_to_delete_event_passing_bad_event_id( Tester $I, \Codeception\Example $data ) {
		$id = $data[0];
		$I->sendDELETE( $this->events_url . "/{$id}" );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 403 if user cannot delete events
	 * @test
	 */
	public function it_should_return_403_if_user_cannot_delete_events( Tester $I ) {
		$id = $I->haveEventInDatabase();

		$I->sendDELETE( $this->events_url . "/{$id}" );

		$I->seeResponseCodeIs( 403 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 200 when deleting event
	 * @test
	 */
	public function it_should_return_200_when_deleting_event( Tester $I ) {
		$id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendDELETE( $this->events_url . "/{$id}" );

		$I->seeResponseCodeIs( 200 );

		$I->seeResponseContainsJson( [ 'id' => $id ] );
	}

	/**
	 * It should return 410 when re-deleting a deleted event
	 * @test
	 */
	public function it_should_return_410_when_re_deleting_a_deleted_event( Tester $I ) {
		$id = $I->havePostInDatabase( [ 'post_status' => 'trash' ] );

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendDELETE( $this->events_url . "/{$id}" );

		$I->seeResponseCodeIs( 410 );
	}
}
