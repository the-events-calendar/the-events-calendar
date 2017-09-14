<?php

use Step\Restv1\RestGuy as Tester;

class EventArchiveCest extends BaseRestCest {

	/**
	 * @test
	 * it should return empty array if there are no events
	 */
	public function it_should_return_empty_array_if_there_are_no_events( Tester $I ) {
		$I->sendGET( $this->events_url );

		$I->seeResponseCodeIs( 404 );
	}

	/**
	 * @test
	 * it should return upcoming events if there are upcoming events
	 */
	public function it_should_return_upcoming_events_if_there_are_upcoming_events( Tester $I ) {
		$I->haveManyEventsInDatabase( 5 );
		$I->haveOptionInDatabase( 'posts_per_page', 10 );

		$I->sendGET( $this->events_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 5, $response->events );
	}

	/**
	 * @test
	 * it should return a number of upcoming events equal to the posts_per_page option by default
	 */
	public function it_should_return_a_number_of_upcoming_events_equal_to_the_posts_per_page_option_by_default( Tester $I ) {
		$I->haveManyEventsInDatabase( 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->events_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 5, $response->events );
	}

	/**
	 * @test
	 * it should allow defining the number of posts per pages to return
	 */
	public function it_should_allow_defining_the_number_of_posts_per_pages_to_return( Tester $I ) {
		$I->haveManyEventsInDatabase( 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->events_url . '?per_page=10' );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 7, $response->events );
	}

	/**
	 * @test
	 * it should limit the maximum number of posts per pages to return to 50 at most
	 */
	public function it_should_limit_the_maximum_number_of_posts_per_pages_to_return_to_50_at_most( Tester $I ) {
		$I->haveManyEventsInDatabase( 51 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->events_url . '?per_page=100' );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 50, $response->events );
	}

	/**
	 * @test
	 * it should return the identity rest url for the root archive url
	 */
	public function it_should_return_the_identity_rest_url_for_the_root_archive_url( Tester $I ) {
		$I->haveManyEventsInDatabase( 5 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->events_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->see_response_contains_url( 'rest_url', $this->events_url );
	}

	/**
	 * @test
	 * it should return the rest url to fetch next events if there are more events than requested
	 */
	public function it_should_return_the_rest_url_to_fetch_next_events_if_there_are_more_events_than_requested( Tester $I ) {
		$I->haveManyEventsInDatabase( 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->events_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->see_response_contains_url( 'next_rest_url', $this->events_url . '/?page=2' );
	}

	/**
	 * @test
	 * it should return the rest url to fetch previous events if there are more events than requested
	 */
	public function it_should_return_the_rest_url_to_fetch_previous_events_if_there_are_more_events_than_requested( Tester $I ) {
		$I->haveManyEventsInDatabase( 9 );
		$I->haveOptionInDatabase( 'posts_per_page', 3 );

		$I->sendGET( $this->events_url . '?page=3' );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->see_response_contains_url( 'previous_rest_url', $this->events_url . '/?page=2' );
	}

	/**
	 * @test
	 * it should return the archive root rest url if previous page is 1
	 */
	public function it_should_return_the_archive_root_rest_url_if_previous_page_is_1( Tester $I ) {
		$I->haveManyEventsInDatabase( 6 );
		$I->haveOptionInDatabase( 'posts_per_page', 3 );

		$I->sendGET( $this->events_url . '?page=2' );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->see_response_contains_url( 'previous_rest_url', $this->events_url . '/' );
	}

	/**
	 * @test
	 * it should return 404 if trying to fetch non existing page
	 */
	public function it_should_return_404_if_trying_to_fetch_non_existing_page( Tester $I ) {
		$I->haveManyEventsInDatabase( 3 );
		$I->haveOptionInDatabase( 'posts_per_page', 3 );

		$I->sendGET( $this->events_url . '?page=2' );

		$I->seeResponseCodeIs( 404 );
	}

	/**
	 * @test
	 * it should allow requesting events starting after a date in the future
	 */
	public function it_should_allow_requesting_events_starting_after_a_date_in_the_future( Tester $I ) {
		// 10 events each 1 week apart starting now
		$I->haveManyEventsInDatabase( 10, [], 24 * 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 20 );

		$I->sendGET( $this->events_url . '?start_date=' . date( 'U', strtotime( '+5 weeks' ) ) );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 6, $response->events );
	}

	/**
	 * @test
	 * it should allow requesting events ending after a date in the future
	 */
	public function it_should_allow_requesting_events_ending_after_a_date_in_the_future( Tester $I ) {
		// 10 events each 1 week apart starting now
		$I->haveManyEventsInDatabase( 10, [], 24 * 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 20 );

		$I->sendGET( $this->events_url . '?end_date=' . date( 'U', strtotime( '+5 weeks' ) ) );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 5, $response->events );
	}

	/**
	 * @test
	 * it should return error if start date is in bad format
	 */
	public function it_should_return_error_if_start_date_is_in_bad_format( Tester $I ) {
		// 10 events each 1 week apart starting now
		$I->haveManyEventsInDatabase( 10, [], 24 * 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 20 );

		$I->sendGET( $this->events_url . '?start_date="before my birthday"' );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return error if end date is in bad format
	 */
	public function it_should_return_error_if_end_date_is_in_bad_format( Tester $I ) {
		// 10 events each 1 week apart starting now
		$I->haveManyEventsInDatabase( 10, [], 24 * 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 20 );

		$I->sendGET( $this->events_url . '?end_date="after my birthday"' );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return error if page is not positive int
	 */
	public function it_should_return_error_if_page_is_not_positive_int( Tester $I ) {
		foreach ( $this->not_positive_integer_numbers() as $number ) {
			$I->sendGET( $this->events_url . '?page=' . $number );

			$I->seeResponseCodeIs( 400 );
			$I->seeResponseIsJson();
		}
	}

	protected function not_positive_integer_numbers() {
		return [ 'foo', - 1, 0, 'foo bar' ];
	}

	/**
	 * @test
	 * it should return error if per_page is not positive int
	 */
	public function it_should_return_error_if_per_page_is_not_positive_int( Tester $I ) {
		foreach ( $this->not_positive_integer_numbers() as $number ) {
			$I->sendGET( $this->events_url . '?per_page=' . $number );

			$I->seeResponseCodeIs( 400 );
			$I->seeResponseIsJson();
		}
	}

	/**
	 * @test
	 * it should allow using start date and end date to narrow down events
	 */
	public function it_should_allow_using_start_date_and_end_date_to_narrow_down_events( Tester $I ) {
		// 10 events each 1 week apart starting now
		// so 0, +8, +15, +22, +29 days...
		$I->haveManyEventsInDatabase( 10, [], 24 * 7 );
		$I->haveOptionInDatabase( 'posts_per_page', 20 );

		$params = array(
			'start_date' => strtotime( '+10 days' ),
			'end_date'   => strtotime( '+30 days' ),
		);
		$I->sendGET( $this->events_url, $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertCount( 3, $response->events );
	}

	/**
	 * @test
	 * it should allow searching the events
	 */
	public function it_should_allow_searching_the_events( Tester $I ) {
		$I->haveManyEventsInDatabase( 5, [ 'post_title' => 'foo' ] );
		$I->haveManyEventsInDatabase( 5, [ 'post_title' => 'foo bar' ] );
		$I->haveManyEventsInDatabase( 5, [ 'post_title' => 'bar' ] );
		$I->haveOptionInDatabase( 'posts_per_page', 20 );

		$I->sendGET( $this->events_url, array( 'search' => 'foo' ) );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$foo_events = $response->events;
		$I->assertCount( 10, $foo_events );

		$I->sendGET( $this->events_url, array( 'search' => 'bar' ) );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$bar_events = $response->events;
		$I->assertCount( 10, $bar_events );

		$ƒ = function ( $event ) {
			return $event->id;
		};

		$I->assertCount( 5, array_intersect( array_map( $ƒ, $foo_events ), array_map( $ƒ, $bar_events ) ) );
	}

	/**
	 * @test
	 * it should return totals in headers and data
	 */
	public function it_should_return_totals_in_headers_and_data( Tester $I ) {
		$I->haveManyEventsInDatabase( 10 );
		$I->haveOptionInDatabase( 'posts_per_page', 5 );

		$I->sendGET( $this->events_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse() );
		$I->assertEquals( 10, $response->total );
		$I->assertEquals( 2, $response->total_pages );
		$I->seeHttpHeader( 'X-TEC-Total', 10 );
		$I->seeHttpHeader( 'X-TEC-TotalPages', 2 );
	}
}
