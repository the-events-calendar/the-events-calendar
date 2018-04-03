<?php

use Step\Restv1\RestGuy as Tester;

class VenueArchiveCest extends BaseRestCest {

	/**
	 * @test
	 * it should return empty array if there are no venues
	 */
	public function it_should_return_empty_array_if_there_are_no_venues( Tester $I ) {
		$I->sendGET( $this->venues_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->venues );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
	}


	/**
	 * It should return a list of venues if there are venues on the site
	 * @test
	 */
	public function it_should_return_a_list_of_venues_if_there_are_venues_on_the_site( Tester $I ) {
		$per_page = 5;
		$I->haveOptionInDatabase( 'posts_per_page', $per_page );

		$venues = $I->haveManyVenuesInDatabase( 10 );

		$I->sendGET( $this->venues_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'venues', $response );
		$I->assertCount( $per_page, $response['venues'] );
		$fetched_venues_ids = array_column( $response['venues'], 'id' );
		$I->assertCount( 5, array_intersect( $venues, $fetched_venues_ids ) );
		$venues = array_diff( $venues, $fetched_venues_ids );
		$I->assertEquals( 10, $response['total'] );
		$I->assertEquals( 2, $response['total_pages'] );
		$I->seeHttpHeader( 'X-TEC-Total', 10 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 2 );

		$I->sendGET( $this->venues_url, [
			'page' => 2,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'venues', $response );
		$I->assertCount( 5, $response['venues'] );
		$fetched_venues_ids = array_column( $response['venues'], 'id' );
		sort( $fetched_venues_ids );
		$I->assertEquals( $venues, $fetched_venues_ids );
		$I->assertEquals( 10, $response['total'] );
		$I->assertEquals( 2, $response['total_pages'] );
		$I->seeHttpHeader( 'X-TEC-Total', 10 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 2 );
	}

	/**
	 * @test
	 * it should allow defining the number of posts per pages to return
	 */
	public function it_should_allow_defining_the_number_of_posts_per_pages_to_return( Tester $I ) {
		$per_page = 5;
		$I->haveOptionInDatabase( 'posts_per_page', $per_page );

		$venues = $I->haveManyVenuesInDatabase( 10 );

		$I->sendGET( $this->venues_url, [
			'per_page' => 4,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'venues', $response );
		$I->assertCount( 4, $response['venues'] );
		$fetched_venues_ids = array_column( $response['venues'], 'id' );
		$I->assertCount( 4, array_intersect( $venues, $fetched_venues_ids ) );
		$I->assertEquals( 10, $response['total'] );
		$I->assertEquals( 3, $response['total_pages'] );
		$I->seeHttpHeader( 'X-TEC-Total', 10 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 3 );

		$I->sendGET( $this->venues_url, [
			'per_page' => 2,
			'page'     => 2,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'venues', $response );
		$I->assertCount( 2, $response['venues'] );
		$fetched_venues_ids = array_column( $response['venues'], 'id' );
		$I->assertCount( 2, array_intersect( $venues, $fetched_venues_ids ) );
		$I->assertEquals( 10, $response['total'] );
		$I->assertEquals( 5, $response['total_pages'] );
		$I->seeHttpHeader( 'X-TEC-Total', 10 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 5 );
	}

	/**
	 * @test
	 * it should limit the maximum number of posts per pages to return to 50 at most
	 */
	public function it_should_limit_the_maximum_number_of_posts_per_pages_to_return_to_50_at_most( Tester $I ) {
		$I->haveManyVenuesInDatabase( 51 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->venues_url, [
			'per_page' => 100,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 50, $response->venues );
	}

	/**
	 * @test
	 * it should return the identity rest url for the root archive url
	 */
	public function it_should_return_the_identity_rest_url_for_the_root_archive_url( Tester $I ) {
		$I->haveManyVenuesInDatabase( 5 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->venues_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->see_response_contains_url( 'rest_url', $this->venues_url );
	}

	/**
	 * @test
	 * it should return the rest url to fetch next venues if there are more venues than requested
	 */
	public function it_should_return_the_rest_url_to_fetch_next_venues_if_there_are_more_venues_than_requested( Tester $I ) {
		$I->haveManyVenuesInDatabase( 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->venues_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->see_response_contains_url( 'next_rest_url', $this->venues_url . '/?page=2' );
	}

	/**
	 * @test
	 * it should return the rest url to fetch previous venues if there are more venues than requested
	 */
	public function it_should_return_the_rest_url_to_fetch_previous_venues_if_there_are_more_venues_than_requested( Tester $I ) {
		$I->haveManyVenuesInDatabase( 9 );
		$I->haveOptionInDatabase( 'posts_per_page', 3 );

		$I->sendGET( $this->venues_url, [
			'page' => 3,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->see_response_contains_url( 'previous_rest_url', $this->venues_url . '/?page=2' );
	}

	/**
	 * @test
	 * it should return the archive root rest url if previous page is 1
	 */
	public function it_should_return_the_archive_root_rest_url_if_previous_page_is_1( Tester $I ) {
		$I->haveManyVenuesInDatabase( 6 );
		$I->haveOptionInDatabase( 'posts_per_page', 3 );

		$I->sendGET( $this->venues_url, [
			'page' => 2,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->see_response_contains_url( 'previous_rest_url', $this->venues_url . '/' );
	}

	/**
	 * @test
	 * it should return 404 if trying to fetch non existing page
	 */
	public function it_should_return_404_if_trying_to_fetch_non_existing_page( Tester $I ) {
		$I->haveManyVenuesInDatabase( 3 );
		$I->haveOptionInDatabase( 'posts_per_page', 3 );

		$I->sendGET( $this->venues_url, [
			'page' => 2,
		] );

		$I->seeResponseCodeIs( 404 );
	}

	protected function not_positive_integer_numbers() {
		return [ 'foo', - 1, 0, 'foo bar' ];
	}

	/**
	 * @test
	 * it should return error if page is not positive int
	 */
	public function it_should_return_error_if_page_is_not_positive_int( Tester $I ) {
		foreach ( $this->not_positive_integer_numbers() as $number ) {
			$I->sendGET( $this->venues_url, [
				'page' => $number,
			] );

			$I->seeResponseCodeIs( 400 );
			$I->seeResponseIsJson();
		}
	}

	/**
	 * @test
	 * it should return error if per_page is not positive int
	 */
	public function it_should_return_error_if_per_page_is_not_positive_int( Tester $I ) {
		foreach ( $this->not_positive_integer_numbers() as $number ) {
			$I->sendGET( $this->venues_url, [
				'per_page' => $number,
			] );

			$I->seeResponseCodeIs( 400 );
			$I->seeResponseIsJson();
		}
	}

	/**
	 * @test
	 * it should return totals in headers and data
	 */
	public function it_should_return_totals_in_headers_and_data( Tester $I ) {
		$I->haveManyVenuesInDatabase( 10 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->venues_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertEquals( 10, $response->total );
		$I->assertEquals( 2, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 10 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 2 );
	}

	/**
	 * It should not show non public venues
	 * @test
	 */
	public function it_should_not_show_non_public_venues( Tester $I ) {
		$I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'draft' ] );

		$I->sendGET( $this->venues_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );

		$I->assertCount( 0, $response->venues );
		$I->assertEquals( 0, $response->total );
		$I->assertEquals( 0, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 0 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 0 );
		$I->assertArrayNotHasKey( 'previous_rest_url', (array) $response );
		$I->assertArrayNotHasKey( 'next_rest_url', (array) $response );
	}

	/**
	 * It should show non public venues to user with authorization
	 * @test
	 */
	public function it_should_show_non_public_venues_to_user_with_authorization( Tester $I ) {
		$I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'draft' ] );

		$I->generate_nonce_for_role('editor');
		$I->sendGET( $this->venues_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->venues );
	}

	/**
	 * It should allow getting venues by status
	 *
	 * @test
	 */
	public function should_allow_getting_venues_by_status( Tester $I ) {
		$published = $I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'publish' ] );
		$draft     = $I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'draft' ] );
		$pending   = $I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'pending' ] );

		$I->haveHttpHeader( 'X-WP-Nonce', $I->generate_nonce_for_role( 'editor' ) );

		$I->sendGET( $this->venues_url, [ 'status' => 'publish' ] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 3, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( $published, $response_ids );

		$I->sendGET( $this->venues_url, [ 'status' => 'publish, draft' ] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 6, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( array_merge( $published, $draft ), $response_ids );

		$I->sendGET( $this->venues_url, [ 'status' => [ 'publish', 'draft', 'pending' ] ] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 9, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( array_merge( $published, $draft, $pending ), $response_ids );
	}

	/**
	 * It should return bad request when trying to query venues by non existing post status
	 *
	 * @test
	 */
	public function should_return_bad_request_when_trying_to_query_venues_by_non_existing_post_status( Tester $I ) {
		$I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'publish' ] );

		$I->haveHttpHeader( 'X-WP-Nonce', $I->generate_nonce_for_role( 'editor' ) );

		$I->sendGET( $this->venues_url, [ 'status' => 'foo' ] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );

		$I->sendGET( $this->venues_url, [ 'status' => 'foo, bar' ] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );

		$I->sendGET( $this->venues_url, [ 'status' => 'foo, bar, publish' ] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );
	}

	/**
	 * It should return 400 when user tries to query venues by status user cannot read
	 *
	 * @test
	 */
	public function should_return_400_when_user_tries_to_query_venues_by_status_user_cannot_read( Tester $I ) {
		$published = $I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'publish' ] );
		$I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'draft' ] );
		$I->haveManyVenuesInDatabase( 3, [ 'post_status' => 'pending' ] );

		$I->sendGET( $this->venues_url, [ 'status' => 'publish' ] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );
		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 3, $response['venues'] );
		$response_ids = array_column( $response['venues'], 'id' );
		sort( $response_ids );
		$I->assertEquals( $published, $response_ids );

		$I->sendGET( $this->venues_url, [ 'status' => 'publish, draft' ] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 400 );
	}
}
