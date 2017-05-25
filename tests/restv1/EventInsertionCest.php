<?php

use Step\Restv1\Auth as Tester;

class EventInsertionCest extends BaseRestCest {
	/**
	 * It should allow inserting an event
	 *
	 * @test
	 */
	public function it_should_allow_inserting_an_event( Tester $I ) {
		$I->authenticate_with_role( 'administrator' );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( 'tomorrow 9am' ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( 'tomorrow 11am' ) ),
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'       => 'An event',
			'description' => trim( apply_filters( 'the_content', 'An event content' ) ),
		] );
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'id', $response );
	}

	/**
	 * It should allow to set the start date using natural language
	 *
	 * @test
	 * @example ["tomorrow 9am", "tomorrow 11am"]
	 * @example ["tomorrow 11am", "tomorrow 1pm"]
	 * @example ["next wednesday 4pm", "next wednesday 5pm"]
	 */
	public function it_should_allow_to_set_the_start_date_using_natural_language( Tester $I, \Codeception\Example $data ) {
		$I->authenticate_with_role( 'administrator' );

		$start_string = $data[0];
		$end_string = $data[1];

		$I->sendPOST( $this->events_url, [
			'title'      => 'An event',
			'start_date' => $start_string,
			'end_date'   => $end_string,
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'      => 'An event',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( $start_string ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( $end_string ) ),
		] );
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'id', $response );
	}

	/**
	 * It should mark bad request if required param is missing or bad
	 *
	 * @test
	 *
	 * @example ["title", false]
	 * @example ["title", ""]
	 * @example ["start_date", false]
	 * @example ["start_date", ""]
	 * @example ["start_date", "not a strtotime parsable string"]
	 * @example ["end_date", false]
	 * @example ["end_date", ""]
	 * @example ["end_date", "not a strtotime parsable string"]
	 */
	public function it_should_mark_bad_request_if_required_param_is_missing_or_bad( Tester $I, \Codeception\Example $data ) {
		$I->authenticate_with_role( 'administrator' );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
		];

		if ( false === $data[1] ) {
			unset( $params[ $data[0] ] );
		} else {
			$params[ $data[0] ] = $data[1];
		}

		$I->sendPOST( $this->events_url, $params );

		$I->seeResponseCodeIs( 400 );
	}

	/**
	 * It should return bad request if trying to set optional parameter to bad value
	 *
	 * @test
	 * @example ["author", 23]
	 * @example ["author", ""]
	 */
	public function it_should_return_bad_request_if_trying_to_set_optional_parameter_to_bad_value( Tester $I, \Codeception\Example $example ) {
		$I->authenticate_with_role( 'administrator' );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
		];

		$params[ $example[0] ] = $example[1];

		$I->sendPOST( $this->events_url, $params );

		$I->seeResponseCodeIs( 400 );
	}

	/**
	 * It should allow to set the event author
	 *
	 * @test
	 */
	public function it_should_allow_to_set_the_event_author( Tester $I ) {
		$I->authenticate_with_role( 'administrator' );

		$user_id = $I->haveUserInDatabase( 'author', 'author' );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
			'author'     => $user_id,
		];

		$I->sendPOST( $this->events_url, $params );

		$I->seeResponseCodeIs( 201 );

		$I->seeResponseContainsJson( [ 'author' => $user_id ] );
	}

	/**
	 * It should allow to set the post date
	 *
	 * @test
	 */
	public function it_should_allow_to_set_the_post_date(Tester $I) {
		$timezone = 'America/New_York';
		$I->haveOptionInDatabase('timezone_string', $timezone );

		$I->authenticate_with_role( 'administrator' );

		$date = ( new \DateTime( 'tomorrow 9am', new DateTimeZone( $timezone ) ) );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
			'date'       => 'tomorrow 9am',
		];

		$I->sendPOST( $this->events_url, $params );

		$I->seeResponseCodeIs( 201 );

		$I->seeResponseContainsJson( [
			'date'     => $date->format( 'Y-m-d H:i:s' ),
		] );
	}
}
