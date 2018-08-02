<?php

use Step\Restv1\RestGuy as Tester;

class RequestChecksumCest extends BaseRestCest{

	/**
	 * It should return the single event checksum when getting the single event
	 *
	 * @test
	 */
	public function should_return_the_single_event_checksum_when_getting_the_single_event( Tester $I ) {
		$event = $I->haveEventInDatabase();

		$expected_checksum = tribe_post_checksum( $event );

		$I->sendGET( $this->events_url . "/{$event}" );

		$I->seeResponseCodeIs( 200 );
		$I->canSeeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'checksum' => $expected_checksum,
		] );
	}

	/**
	 * It should return archive checksums
	 *
	 * @test
	 */
	public function should_return_archive_checksums( Tester $I ) {
		$all_ids = $I->haveManyEventsInDatabase( 10 );
		$expected_request_checksum = tribe_posts_checksum( $all_ids );

		$I->sendGET( $this->events_url, [ 'per_page' => 2 ] );

		$I->seeResponseCodeIs( 200 );
		$I->canSeeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );
		$page_ids = array_column( $response['events'], 'id' );
		$req_1_pg_checksum = tribe_posts_checksum( $page_ids );

		$I->canSeeResponseContainsJson( [
			'request_checksum' => $expected_request_checksum,
			'page_checksum'    => $req_1_pg_checksum,
		] );

		$I->sendGET( $this->events_url, [ 'per_page' => 5, ] );

		$I->seeResponseCodeIs( 200 );
		$I->canSeeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );
		$page_ids = array_column( $response['events'], 'id' );

		$req_2_pg_checksum = tribe_posts_checksum( $page_ids );
		$I->canSeeResponseContainsJson( [
			'request_checksum' => $expected_request_checksum,
			'page_checksum'    => $req_2_pg_checksum,
		] );

		$I->assertNotEquals( $req_1_pg_checksum, $req_2_pg_checksum );

		$I->sendGET( $this->events_url, [ 'per_page' => 5, 'page' => 2 ] );

		$I->seeResponseCodeIs( 200 );
		$I->canSeeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );
		$page_ids = array_column( $response['events'], 'id' );

		$req_3_pg_checksum = tribe_posts_checksum( $page_ids );

		$I->canSeeResponseContainsJson( [
				'request_checksum' => $expected_request_checksum,
				'page_checksum'    => $req_3_pg_checksum,
			]
		);

		$I->assertNotEquals( $req_1_pg_checksum, $req_3_pg_checksum );
		$I->assertNotEquals( $req_2_pg_checksum, $req_3_pg_checksum );
	}

	/**
	 * It should return empty checksum if archive does not contain any results
	 *
	 * @test
	 */
	public function should_return_empty_checksum_if_archive_does_not_contain_any_results( Tester $I ) {
		update_option( 'tribe_disable_shindig', true );

		$I->sendGET( $this->events_url, [ 'search' => 'sljdlsdkdjflskjdfsdffjd' ] );

		$I->seeResponseCodeIs( 200 );
		$I->canSeeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );

		$I->canSeeResponseContainsJson( [
			'request_checksum' => null,
			'page_checksum'    => null,
		] );
	}
}