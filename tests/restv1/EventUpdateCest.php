<?php

use Step\Restv1\RestGuy as Tester;
use Tribe__Image__Uploader as Image;
use Tribe__Timezones as Timezones;

class EventUpdateCest extends BaseRestCest
{
	/**
	 * It should return 401 if user cannot update events
	 *
	 * @test
	 */
	public function it_should_return_401_if_user_cannot_update_events( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->sendPOST( $this->events_url ."/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'excerpt'     => 'An event excerpt',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
		] );

		$I->seeResponseCodeIs( 401 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should allow updating an event
	 * @test
	 */
	public function it_should_allow_updating_an_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$start = 'tomorrow 9am';
		$end = 'tomorrow 11am';
		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'excerpt'     => 'An event excerpt',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( $end ) ),
		] );

		$I->seeResponseCodeIs( 200 );
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
	 * It should allow updating an event slug
	 * @test
	 */
	public function it_should_allow_updating_an_event_slug( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$start = 'tomorrow 9am';
		$end = 'tomorrow 11am';
		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'slug'        => 'an-interesting-event',
			'description' => 'An event content',
			'excerpt'     => 'An event excerpt',
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( $end ) ),
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'          => 'An event',
			'slug'           => 'an-interesting-event',
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$start = $data[0];
		$end = $data[1];

		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'      => 'An event',
			'start_date' => $start,
			'end_date'   => $end,
		] );
		$I->seeResponseCodeIs( 200 );
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
	 * It should allow specifying the timezone of the event to update
	 *
	 * @test
	 *
	 * @example ["tomorrow 9am", "tomorrow 11am", "America/New_York"]
	 * @example ["tomorrow 11am", "tomorrow 1pm", "UTC"]
	 * @example ["next wednesday 4pm", "next wednesday 5pm","Australia/Darwin"]
	 * @example ["next wednesday 4pm", "next wednesday 5pm","Europe/Rome"]
	 */
	public function it_should_allow_specifying_the_timezone_of_the_event_to_update( Tester $I, \Codeception\Example $data ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		// set the site to another timezone completely
		$I->haveOptionInDatabase( 'timezone_string', 'Australia/Darwin' );

		$start = $data[0];
		$end = $data[1];
		$timezone = $data[2];

		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'      => 'An event',
			'start_date' => $start,
			'end_date'   => $end,
			'timezone'   => $timezone,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'          => 'An event',
			'timezone'       => $timezone,
			'start_date'     => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'       => date( 'Y-m-d H:i:s', strtotime( $end ) ),
			'utc_start_date' => Timezones::convert_date_from_timezone( $start, $timezone, 'UTC', 'Y-m-d H:i:s' ),
			'utc_end_date'   => Timezones::convert_date_from_timezone( $end, $timezone, 'UTC', 'Y-m-d H:i:s' ),
		] );

		$new_timezone = 'Europe/Paris';

		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'      => 'An event',
			'start_date' => $start,
			'end_date'   => $end,
			'timezone'   => $new_timezone,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'title'          => 'An event',
			'timezone'       => $new_timezone,
			'start_date'     => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'       => date( 'Y-m-d H:i:s', strtotime( $end ) ),
			'utc_start_date' => Timezones::convert_date_from_timezone( $start, $new_timezone, 'UTC', 'Y-m-d H:i:s' ),
			'utc_end_date'   => Timezones::convert_date_from_timezone( $end, $new_timezone, 'UTC', 'Y-m-d H:i:s' ),
		] );

	}

	/**
	 * It should mark bad request if id is bad
	 *
	 * @test
	 */
	public function it_should_mark_bad_request_if_id_is_bad( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
		];

		$I->sendPOST( $this->events_url . '/2389', $params );

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
	 * @example ["website", "foo"]
	 */
	public function it_should_return_bad_request_if_trying_to_set_optional_parameter_to_bad_value( Tester $I, \Codeception\Example $example ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
		];

		$params[ $example[0] ] = $example[1];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 400 );
	}

	/**
	 * It should allow to set the event author
	 *
	 * @test
	 */
	public function it_should_allow_to_set_the_event_author( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$user_id = $I->haveUserInDatabase( 'author', 'author' );

		$params = [
			'title'      => 'An event title',
			'start_date' => 'tomorrow 9am',
			'end_date'   => 'tomorrow 11am',
			'author'     => $user_id,
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );

		$I->seeResponseContainsJson( [ 'author' => $user_id ] );
	}

	/**
	 * It should allow to set the post date
	 *
	 * @test
	 */
	public function it_should_allow_to_set_the_post_date( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

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

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );

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
		$event_id = $I->haveEventInDatabase();

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

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );

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
		$event_id = $I->haveEventInDatabase();

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

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );
		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$start = 'tomorrow 9am';
		$end = 'tomorrow 11am';
		$all_day_start = 'tomorrow 00:00:00';
		$all_day_end = 'tomorrow 23:59:59';
		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( $end ) ),
		] );

		$I->seeResponseCodeIs( 200 );
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );
		$timezone = 'America/New_York';
		$I->haveOptionInDatabase( 'timezone_string', $timezone );

		$start = 'tomorrow 9am';
		$end = '+5 days 11am';
		$all_day_start = 'tomorrow 00:00:00';
		$all_day_end = '+5 days 23:59:59';
		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => date( 'Y-m-d H:i:s', strtotime( $start ) ),
			'end_date'    => date( 'Y-m-d H:i:s', strtotime( $end ) ),
		] );

		$I->seeResponseCodeIs( 200 );
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
		$event_id = $I->haveEventInDatabase();

		$image = $data[0];

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$image_path = codecept_data_dir( 'csv-import-test-files/featured-image/images/featured-image.jpg' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attachment_id = $I->factory()->attachment->create_upload_object( $image_path );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'image'       => $attachment_id,
		] );

		$I->seeResponseCodeIs( 200 );
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$image_path = codecept_data_dir( 'csv-import-test-files/featured-image/images/featured-image.jpg' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attachment_id = $I->factory()->attachment->create_upload_object( $image_path );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'image'       => wp_get_attachment_url( $attachment_id ),
		] );

		$I->seeResponseCodeIs( 200 );
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'cost'        => '20$',
		] );

		$I->seeResponseCodeIs( 200 );
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
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'cost'        => [ '0$', '20$', '30$' ],
		] );

		$I->seeResponseCodeIs( 200 );
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

	/**
	 * It should allow setting the event website
	 *
	 * @test
	 */
	public function it_should_allow_setting_the_event_website( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->events_url . "/{$event_id}", [
			'title'       => 'An event',
			'description' => 'An event content',
			'all_day'     => true,
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'website'     => 'http://example.com',
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [
			'website' => 'http://example.com',
		] );
	}

	/**
	 * It should allow inserting presentation meta to users that can `publish_posts` and `edit_other_posts`
	 *
	 * @test
	 *
	 * @example ["show_map", true ]
	 * @example ["show_map_link", true]
	 * @example ["hide_from_listings", true]
	 * @example ["sticky", true]
	 * @example ["featured", true]
	 */
	public function it_should_allow_inserting_presentation_meta_to_users_that_can_publish_posts_and_edit_other_posts( Tester $I, \Codeception\Example $data ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'editor' );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
		];
		$params[ $data[0] ] = (bool) $data[1];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [ $data[0] => $data[1] ] );
	}

	/**
	 * It should allow inserting presentation meta to users that can `publish_posts`
	 *
	 * @test
	 * @example ["show_map", true ]
	 * @example ["show_map_link", true]
	 * @example ["hide_from_listings", true]
	 * @example ["sticky", true]
	 * @example ["featured", true]
	 */
	public function it_should_allow_inserting_presentation_meta_to_users_that_can_publish_posts( Tester $I, \Codeception\Example $data ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'author' );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
		];
		$params[ $data[0] ] = $data[1];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [ $data[0] => true ] );
	}

	/**
	 * It should set the post status to draft if user cannot publish posts
	 *
	 * @test
	 */
	public function it_should_set_the_post_status_to_draft_if_user_cannot_publish_posts( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'contributor' );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'status'      => 'publish',
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->seePostInDatabase( [ 'ID' => $response['id'], 'post_status' => 'pending' ] );
	}

	/**
	 * It should allow a user that can publish to set status to publish
	 *
	 * @test
	 * @example ["administrator", "publish" ]
	 * @example ["editor", "publish"]
	 * @example ["author", "publish"]
	 */
	public function it_should_allow_a_user_that_can_publish_to_set_status_to_publish( Tester $I, \Codeception\Example $data ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( $data[0] );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'status'      => $data[1],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->seePostInDatabase( [ 'ID' => $response['id'], 'post_status' => $data[1] ] );
	}

	/**
	 * It should allow inserting an existing venue ID with the event
	 *
	 * @test
	 */
	public function it_should_allow_inserting_an_existing_venue_id_with_the_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$venue_id = $I->haveVenueInDatabase();

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'venue'       => $venue_id,
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [
			'venue' => [ 'id' => $venue_id ],
		] );
	}

	/**
	 * It should allow inserting a venue along with the event
	 *
	 * @test
	 */
	public function it_should_allow_inserting_a_venue_along_with_the_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'venue'       => [
				'venue' => 'A venue',
			],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'venue', $response );
		$venue_response = $response['venue'];
		$I->assertArrayHasKey( 'id', $venue_response );
	}

	/**
	 * It should allow linking the inserted event to an existing organizer
	 *
	 * @test
	 */
	public function it_should_allow_linking_the_inserted_event_to_an_existing_organizer( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$organizer_id = $I->haveOrganizerInDatabase();

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'organizer'   => $organizer_id
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'organizer', $response );
		$organizer_response = $response['organizer'];
		$I->assertCount( 1, $organizer_response );
		$I->assertEquals( $organizer_id, $organizer_response[0]['id'] );
	}

	/**
	 * It should allow linking the event to multiple existing organizers
	 *
	 * @test
	 */
	public function it_should_allow_linking_the_event_to_multiple_existing_organizers( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$organizer_id_1 = $I->haveOrganizerInDatabase();
		$organizer_id_2 = $I->haveOrganizerInDatabase();

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'organizer'   => [ $organizer_id_1, $organizer_id_2 ]
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'organizer', $response );
		$organizer_response = $response['organizer'];
		$I->assertCount( 2, $organizer_response );
		$I->assertEquals( $organizer_id_1, $organizer_response[0]['id'] );
		$I->assertEquals( $organizer_id_2, $organizer_response[1]['id'] );
	}

	/**
	 * It should allow creating the event organizer while inserting the event
	 *
	 * @test
	 */
	public function it_should_allow_creating_the_event_organizer_while_inserting_the_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'organizer'   => [ [ 'organizer' => 'Organizer A' ] ],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'organizer', $response );
		$organizer_response = $response['organizer'];
		$I->assertCount( 1, $organizer_response );
		$I->assertArrayHasKey( 'id', $organizer_response[0] );
	}

	/**
	 * It should allow creating multiple event organizers while inserting the event
	 *
	 * @test
	 */
	public function it_should_allow_creating_multiple_event_organizers_while_inserting_the_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'organizer'   => [
				[ 'organizer' => 'Organizer A' ],
				[ 'organizer' => 'Organizer B' ],
				[ 'organizer' => 'Organizer C' ],
			],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'organizer', $response );
		$organizer_response = $response['organizer'];
		$I->assertCount( 3, $organizer_response );
	}

	/**
	 * It should allow creating multiple organizers and linking existing ones while inserting the event
	 *
	 * @test
	 */
	public function it_should_allow_creating_multiple_organizers_and_linking_existing_ones_while_inserting_the_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$organizer_id_1 = $I->haveOrganizerInDatabase();
		$organizer_id_2 = $I->haveOrganizerInDatabase();

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'organizer'   => [
				[ 'organizer' => 'Organizer A' ],
				[ 'id' => $organizer_id_1 ],
				[ 'id' => $organizer_id_2 ],
				[ 'organizer' => 'Organizer C' ],
			],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'organizer', $response );
		$organizer_response = $response['organizer'];
		$I->assertCount( 4, $organizer_response );
		$response_organizer_ids = array_column( $organizer_response, 'id' );
		$I->assertContains( $organizer_id_1, $response_organizer_ids );
		$I->assertContains( $organizer_id_2, $response_organizer_ids );
	}

	/**
	 * It should allow setting the linked posts images when creating them with the event
	 *
	 * @test
	 */
	public function it_should_allow_setting_the_linked_posts_images_when_creating_them_with_the_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$image_path = codecept_data_dir( 'csv-import-test-files/featured-image/images/featured-image.jpg' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		// WordPress does not care if the image is the same
		$attachment_id_1 = $I->factory()->attachment->create_upload_object( $image_path );
		$attachment_id_2 = $I->factory()->attachment->create_upload_object( $image_path );
		$attachment_id_3 = $I->factory()->attachment->create_upload_object( $image_path );

		$I->assertNotEquals( $attachment_id_1, $attachment_id_2 );
		$I->assertNotEquals( $attachment_id_1, $attachment_id_3 );
		$I->assertNotEquals( $attachment_id_2, $attachment_id_3 );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'venue'       => [
				'venue' => 'A venue',
				'image' => wp_get_attachment_url( $attachment_id_1 )
			],
			'organizer'   => [
				[ 'organizer' => 'Organizer A', 'image' => wp_get_attachment_url( $attachment_id_2 ) ],
				[ 'organizer' => 'Organizer C', 'image' => wp_get_attachment_url( $attachment_id_3 ) ],
			],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'venue', $response );
		$venue_response = $response['venue'];
		$I->assertNotEmpty( $venue_response['image'] );
		$I->assertEquals( wp_get_attachment_url( $attachment_id_1 ), $venue_response['image']['url'] );
		$I->assertArrayHasKey( 'organizer', $response );
		$organizer_response = $response['organizer'];
		$I->assertCount( 2, $organizer_response );
		$I->assertNotEmpty( $organizer_response[0]['image'] );
		$I->assertEquals( wp_get_attachment_url( $attachment_id_2 ), $organizer_response[0]['image']['url'] );
		$I->assertNotEmpty( $organizer_response[1]['image'] );
		$I->assertEquals( wp_get_attachment_url( $attachment_id_3 ), $organizer_response[1]['image']['url'] );
	}

	/**
	 * It should allow assigning existing event categories to an inserted event
	 *
	 * @test
	 */
	public function it_should_allow_assigning_existing_event_categories_to_an_inserted_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$cat_1 = $I->haveTermInDatabase( 'cat1', Tribe__Events__Main::TAXONOMY );
		$cat_2 = $I->haveTermInDatabase( 'cat2', Tribe__Events__Main::TAXONOMY );

		$cat_1_id = reset( $cat_1 );
		$cat_2_id = reset( $cat_2 );
		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'categories'  => [ $cat_1_id, $cat_2_id ],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertNotEmpty( $response['categories'] );
		$I->assertCount( 2, $response['categories'] );
		$I->assertEquals( $cat_1_id, $response['categories'][0]['id'] );
		$I->assertEquals( $cat_2_id, $response['categories'][1]['id'] );
	}

	/**
	 * It should allow creating event categories while inserting an event
	 *
	 * @test
	 */
	public function it_should_allow_creating_event_categories_while_inserting_an_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'categories'  => 'cat1,cat2',
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertNotEmpty( $response['categories'] );
		$I->assertCount( 2, $response['categories'] );
	}

	/**
	 * It should allow assigning existing categories and creating new categories while inserting an event
	 *
	 * @test
	 */
	public function it_should_allow_assigning_existing_categories_and_creating_new_categories_while_inserting_an_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$cat_1 = $I->haveTermInDatabase( 'cat1', Tribe__Events__Main::TAXONOMY );

		$cat_1_id = reset( $cat_1 );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'categories'  => [ $cat_1_id, 'cat2' ],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertNotEmpty( $response['categories'] );
		$I->assertCount( 2, $response['categories'] );
		$I->assertEquals( $cat_1_id, $response['categories'][0]['id'] );
	}

	/**
	 * It should allow assigning existing event tags to an inserted event
	 *
	 * @test
	 */
	public function it_should_allow_assigning_existing_event_tags_to_an_inserted_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$tag_1 = $I->haveTermInDatabase( 'tag1', 'post_tag' );
		$tag_2 = $I->haveTermInDatabase( 'tag2', 'post_tag' );

		$tag_1_id = reset( $tag_1 );
		$tag_2_id = reset( $tag_2 );
		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'tags'  => [ $tag_1_id, $tag_2_id ],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertNotEmpty( $response['tags'] );
		$I->assertCount( 2, $response['tags'] );
		$I->assertEquals( $tag_1_id, $response['tags'][0]['id'] );
		$I->assertEquals( $tag_2_id, $response['tags'][1]['id'] );
	}

	/**
	 * It should allow creating event tags while inserting an event
	 *
	 * @test
	 */
	public function it_should_allow_creating_event_tags_while_inserting_an_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'tags'        => 'tag1,tag2',
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertNotEmpty( $response['tags'] );
		$I->assertCount( 2, $response['tags'] );
	}

	/**
	 * It should allow assigning existing tags and creating new tags while inserting an event
	 *
	 * @test
	 */
	public function it_should_allow_assigning_existing_tags_and_creating_new_tags_while_inserting_an_event( Tester $I ) {
		$event_id = $I->haveEventInDatabase();

		$I->generate_nonce_for_role( 'administrator' );

		$tag_1 = $I->haveTermInDatabase( 'tag1', 'post_tag' );

		$tag_1_id = reset( $tag_1 );

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'tags'  => [ $tag_1_id, 'tag2' ],
		];

		$I->sendPOST( $this->events_url . "/{$event_id}", $params );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertNotEmpty( $response['tags'] );
		$I->assertCount( 2, $response['tags'] );
		$I->assertEquals( $tag_1_id, $response['tags'][0]['id'] );
	}
}
