<?php

use Step\Restv1\RestGuy as Tester;
use Tribe__Image__Uploader as Image;
use Tribe__Timezones as Timezones;

class EventInsertionCest extends BaseRestCest {
	/**
	 * It should allow inserting an event
	 *
	 * @test
	 */
	public function it_should_allow_inserting_an_event( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );
		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$start = 'tomorrow 9am';
		$end = 'tomorrow 11am';
		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'excerpt'     => 'An event excerpt',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( $end ) ),
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'          => 'An event',
			'description'    => trim( apply_filters( 'the_content', 'An event content' ) ),
			'excerpt'        => trim( apply_filters( 'the_excerpt', 'An event excerpt' ) ),
			'start_date'     => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'       => date( 'Y-m-d H:i:s', strtotime( $end ) ),
			'utc_start_date' => Timezones::convert_date_from_timezone( $start, $timezone, 'UTC', 'Y-m-d H:i:s' ),
			'utc_end_date'   => Timezones::convert_date_from_timezone( $end, $timezone, 'UTC', 'Y-m-d H:i:s' ),
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
		$I->generate_nonce_for_role( 'administrator' );

		$start = $data[0];
		$end = $data[1];

		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$I->sendPOST( $this->events_url, [
			'title'      => 'An event',
			'start_date' => $start,
			'end_date'   => $end,
		] );
		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();

		$I->canSeeResponseContainsJson( [
			'title'          => 'An event',
			'start_date'     => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'       => date( 'Y-m-d H:i:s', strtotime( $end ) ),
			'utc_start_date' => Timezones::convert_date_from_timezone( $start, $timezone, 'UTC', 'Y-m-d H:i:s' ),
			'utc_end_date'   => Timezones::convert_date_from_timezone( $end, $timezone, 'UTC', 'Y-m-d H:i:s' ),
		] );
	}

	/**
	 * It should allow specifying the timezone of the event to insert
	 *
	 * @test
	 *
	 * @example ["tomorrow 9am", "tomorrow 11am", "America/New_York"]
	 * @example ["tomorrow 11am", "tomorrow 1pm", "UTC"]
	 * @example ["next wednesday 4pm", "next wednesday 5pm","Australia/Darwin"]
	 * @example ["next wednesday 4pm", "next wednesday 5pm","Pacific/Bougainville"]
	 */
	public function it_should_allow_specifying_the_timezone_of_the_event_to_insert( Tester $I, \Codeception\Example $data ) {
		$I->generate_nonce_for_role( 'administrator' );

		// set the site to another timezone completely
		$I->haveOptionInDatabase( 'timezone_string', 'Australia/Darwin' );

		$start = $data[0];
		$end = $data[1];
		$timezone = $data[2];

		$I->sendPOST( $this->events_url, [
			'title'      => 'An event',
			'start_date' => $start,
			'end_date'   => $end,
			'timezone'   => $timezone,
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'          => 'An event',
			'timezone'       => $timezone,
			'start_date'     => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'       => date( 'Y-m-d H:i:s', strtotime( $end ) ),
			'utc_start_date' => Timezones::convert_date_from_timezone( $start, $timezone, 'UTC', 'Y-m-d H:i:s' ),
			'utc_end_date'   => Timezones::convert_date_from_timezone( $end, $timezone, 'UTC', 'Y-m-d H:i:s' ),
		] );
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
		$I->generate_nonce_for_role( 'administrator' );

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
	 * @example ["date", "foo"]
	 * @example ["date_utc", "foo"]
	 */
	public function it_should_return_bad_request_if_trying_to_set_optional_parameter_to_bad_value( Tester $I, \Codeception\Example $example ) {
		$I->generate_nonce_for_role( 'administrator' );

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
		$I->generate_nonce_for_role( 'administrator' );

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
	public function it_should_allow_to_set_the_post_date( Tester $I ) {
		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$I->generate_nonce_for_role( 'administrator' );

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
			'date' => $date->format( 'Y-m-d H:i:s' ),
		] );
	}

	/**
	 * It should allow setting the post GMT date
	 *
	 * @test
	 */
	public function it_should_allow_setting_the_post_gmt_date( Tester $I ) {
		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$I->generate_nonce_for_role( 'administrator' );

		$date = ( new \DateTime( 'tomorrow 9am', new DateTimeZone( 'UTC' ) ) );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
			'date_utc'   => 'tomorrow 9am',
		];

		$I->sendPOST( $this->events_url, $params );

		$I->seeResponseCodeIs( 201 );

		$I->seeResponseContainsJson( [
			'date_utc' => $date->format( 'Y-m-d H:i:s' ),
		] );
	}

	/**
	 * It should not allow overriding generated fields
	 *
	 * @example ["url", "http://example.com"]
	 * @example ["rest_url", "http://example.com/api/some/path/event"]
	 * @example ["utc_start_date", "foo"]
	 * @example ["utc_start_date_details", "foo"]
	 * @example ["utc_end_date", "foo"]
	 * @example ["utc_end_date_details", "foo"]
	 * @example ["start_date_details", "foo"]
	 * @example ["end_date_details", "foo"]
	 * @example ["timezone_abbr", "foo"]
	 * @example ["cost_details", "foo"]
	 *
	 * @test
	 */
	public function it_should_not_allow_overriding_generated_fields( Tester $I, \Codeception\Example $data ) {
		$key = $data[0];
		$value = $data[1];

		$I->generate_nonce_for_role( 'administrator' );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
			'date_utc'   => 'tomorrow 9am',
		];
		$params[ $key ] = $value;

		$I->sendPOST( $this->events_url, $params );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );

		$I->assertNotEquals( $value, $response[ $key ] );
	}

	/**
	 * It should allow specifying if an event is an all day one
	 *
	 * @test
	 */
	public function it_should_allow_specifying_if_an_event_is_an_all_day_one( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );
		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$start = 'tomorrow 9am';
		$end = 'tomorrow 11am';
		$all_day_start = 'tomorrow 00:00:00';
		$all_day_end = 'tomorrow 23:59:59';
		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( $end ) ),
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'          => 'An event',
			'description'    => trim( apply_filters( 'the_content', 'An event content' ) ),
			'start_date'     => date( 'Y-m-d H:i:s', strtotime( $all_day_start ) ),
			'end_date'       => date( 'Y-m-d H:i:s', strtotime( $all_day_end ) ),
			'utc_start_date' => Timezones::convert_date_from_timezone( $all_day_start, $timezone, 'UTC', 'Y-m-d H:i:s' ),
			'utc_end_date'   => Timezones::convert_date_from_timezone( $all_day_end, $timezone, 'UTC', 'Y-m-d H:i:s' ),
			'all_day'        => true,
		] );
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'id', $response );
	}

	/**
	 * It should allow inserting a multiday all day event
	 *
	 * @test
	 */
	public function it_should_allow_inserting_a_multiday_all_day_event( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );
		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$start = 'tomorrow 9am';
		$end = '+5 days 11am';
		$all_day_start = 'tomorrow 00:00:00';
		$all_day_end = '+5 days 23:59:59';
		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( $end ) ),
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'          => 'An event',
			'description'    => trim( apply_filters( 'the_content', 'An event content' ) ),
			'start_date'     => date( 'Y-m-d H:i:s', strtotime( $all_day_start ) ),
			'end_date'       => date( 'Y-m-d H:i:s', strtotime( $all_day_end ) ),
			'utc_start_date' => Timezones::convert_date_from_timezone( $all_day_start, $timezone, 'UTC', 'Y-m-d H:i:s' ),
			'utc_end_date'   => Timezones::convert_date_from_timezone( $all_day_end, $timezone, 'UTC', 'Y-m-d H:i:s' ),
			'all_day'        => true,
		] );
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'id', $response );
	}

	/**
	 * It should return bad request if trying to set image to bad value
	 *
	 * @example ["http://example.localhost/some-image.png"]
	 * @example ["foo bar"]
	 * @example ["23"]
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_set_image_to_bad_value( Tester $I, \Codeception\Example $data ) {
		$image = $data[0];

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'image'       => $image,
		] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return bad request if trying to set image to non supported MIME type
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_set_image_to_non_supported_image_type( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'image'       => ( new Image( codecept_data_dir( 'images/featured-image.raw' ) ) )->upload_and_get_attachment_id(),
		] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return bad request if trying to set image to ID of non attachment
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_trying_to_set_image_to_id_of_non_attachment( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'image'       => $I->havePostInDatabase(),
		] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should allow setting the image passing its attachment ID
	 *
	 * @test
	 */
	public function it_should_allow_setting_the_image_passing_its_attachment_id( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$image_path = codecept_data_dir( 'csv-import-test-files/featured-image/images/featured-image.jpg' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attachment_id = $I->factory()->attachment->create_upload_object( $image_path );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'image'       => $attachment_id,
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'image', $response );
		$I->assertEquals( $attachment_id, $response['image']['id'] );
	}

	/**
	 * It should allow setting the image setting passing a valid URL
	 *
	 * @test
	 */
	public function it_should_allow_setting_the_image_setting_passing_a_valid_url( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$image_path = codecept_data_dir( 'csv-import-test-files/featured-image/images/featured-image.jpg' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attachment_id = $I->factory()->attachment->create_upload_object( $image_path );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'image'       => wp_get_attachment_url( $attachment_id ),
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'image', $response );
		$I->assertNotEmpty( $attachment_id, $response['image']['id'] );
	}

	/**
	 * It should allow setting the event cost as a string
	 *
	 * @test
	 */
	public function it_should_allow_setting_the_event_cost_as_a_string( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'cost'        => '20$',
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [
			'cost'         => '20$',
			'cost_details' => [
				'currency_symbol'   => '$',
				'currency_position' => 'postfix',
				'values'            => [ 20 ],
			]
		] );
	}

	/**
	 * It should allow to insert the cost as an array of values
	 *
	 * @test
	 */
	public function it_should_allow_to_insert_the_cost_as_an_array_of_values( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url, [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'cost'        => [ '0$', '20$', '30$' ],
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [
			'cost'         => 'Free - 30$',
			'cost_details' => [
				'currency_symbol'   => '$',
				'currency_position' => 'postfix',
				'values'            => [ 0, 20, 30 ],
			]
		] );
	}
}
