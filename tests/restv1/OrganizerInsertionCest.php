<?php

use Codeception\Example;
use Step\Restv1\RestGuy as Tester;

class VenueInsertionCest extends BaseRestCest {
	/**
	 * It should allow insertin a venue
	 *
	 * @test
	 */
	public function it_should_allow_inserting_a_venue( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->venues_url, [
			'venue' => 'A venue',
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'venue' => 'A venue',
		] );
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'id', $response );
	}

	/**
	 * It should allow inserting post fields along with the venue
	 *
	 * @test
	 */
	public function it_should_allow_inserting_post_fields_along_with_the_venue( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$editor = $I->haveUserInDatabase( 'author', 'editor' );

		$date = new DateTime( 'tomorrow 9am', new DateTimeZone( 'America/New_York' ) );
		$utc_date = new DateTime( 'tomorrow 9am', new DateTimeZone( 'UTC' ) );

		$I->sendPOST( $this->venues_url, [
			'venue'       => 'A venue',
			'author'      => $editor,
			'date'        => $date->format( 'U' ),
			'date_utc'    => $utc_date->format( 'U' ),
			'description' => 'Venue description',
			'status'      => 'draft',
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'venue'       => 'A venue',
			'author'      => (string) $editor,
			'date'        => date( 'Y-m-d H:i:s', $date->format( 'U' ) ),
			'date_utc'    => $utc_date->format( 'Y-m-d H:i:s' ),
			'description' => trim( apply_filters( 'the_content', 'Venue description' ) ),
		] );
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'id', $response );
		$id = $response['id'];
		$I->seePostInDatabase( [ 'ID' => $id, 'post_status' => 'draft' ] );
	}

	/**
	 * It should return 400 if venue is empty
	 *
	 * @test
	 */
	public function it_should_return_400_if_venue_is_empty( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->venues_url, [
			'venue' => '',
		] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should return 400 if passing bad post field values
	 *
	 * @test
	 * @example ["author" , 23]
	 * @example ["author" , "foo"]
	 * @example ["date" , "foo"]
	 * @example ["date_utc" , "foo"]
	 * @example ["status" , "kinda_thinking_about_it"]
	 */
	public function it_should_return_400_if_passing_bad_post_field_values( Tester $I, Example $data ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->venues_url, [
			'venue'  => 'A venue',
			$data[0] => $data[1],
		] );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * It should allow inserting optional meta along with the venue
	 *
	 * @test
	 */
	public function it_should_allow_inserting_optional_meta_along_with_the_venue( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendPOST( $this->venues_url, [
			'venue'         => 'A venue',
			'show_map'      => 'false',
			'show_map_link' => 'false',
			'address'       => 'Venue address',
			'city'          => 'Venue city',
			'country'       => 'Venue country',
			'province'      => 'Venue province',
			'state'         => 'Venue state',
			'zip'           => 'Venue zip',
			'phone'         => 'Venue phone',
			'stateprovince' => 'Venue stateprovince',
			'website'       => 'http://venue.com',
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson( [
			'venue'         => 'A venue',
			'show_map'      => false,
			'show_map_link' => false,
			'address'       => 'Venue address',
			'city'          => 'Venue city',
			'country'       => 'Venue country',
			'province'      => 'Venue province',
			'state'         => 'Venue state',
			'stateprovince' => 'Venue stateprovince',
			'zip'           => 'Venue zip',
			'phone'         => 'Venue phone',
			'website'       => 'http://venue.com',
		] );
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'id', $response );
	}

	/**
	 * It should allow inserting the image as an attachment ID along with the venue
	 *
	 * @test
	 */
	public function it_should_allow_inserting_the_image_as_an_attachment_id_along_with_the_venue( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$image_id = $I->factory()->attachment->create_upload_object( codecept_data_dir( 'images/featured-image.jpg' ) );

		$I->sendPOST( $this->venues_url, [
			'venue' => 'A venue',
			'image' => $image_id,
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'image', $response );
		$I->assertEquals( $image_id, $response['image']['id'] );
	}

	/**
	 * It should allow inserting th image as a URL along with the venue
	 *
	 * @test
	 */
	public function it_should_allow_inserting_th_image_as_a_url_along_with_the_venue( Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$image_id = $I->factory()->attachment->create_upload_object( codecept_data_dir( 'images/featured-image.jpg' ) );
		$image_url = wp_get_attachment_url( $image_id );

		$I->sendPOST( $this->venues_url, [
			'venue' => 'A venue',
			'image' => $image_url,
		] );

		$I->seeResponseCodeIs( 201 );
		$I->seeResponseIsJson();
		$response = json_decode( $I->grabResponse(), true );
		$I->assertArrayHasKey( 'image', $response );
		$I->assertEquals( $image_id, $response['image']['id'] );
	}
}
