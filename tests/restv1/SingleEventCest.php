<?php


class SingleEventCest extends BaseRestCest {

	/**
	 * @test
	 * it should return a bad request status if hitting a non existing single event endpoint
	 */
	public function it_should_return_a_bad_request_status_if_hitting_a_non_existing_single_event_endpoint( Restv1Tester $I ) {
		$I->sendGET( $this->events_url . '/13' );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return a not found status if id is not of an event
	 */
	public function it_should_return_a_not_found_status_if_id_is_not_of_an_event( Restv1Tester $I ) {
		$id = $I->havePostInDatabase();

		$I->sendGET( $this->events_url . '/' . $id );

		$I->seeResponseCodeIs( 404 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return invalid auth status if event is not accessible
	 */
	public function it_should_return_invalid_auth_status_if_event_is_not_accessible( Restv1Tester $I ) {
		$id = $I->haveEventInDatabase( [ 'post_status' => 'draft' ] );

		$I->sendGET( $this->events_url . '/' . $id );

		$I->seeResponseCodeIs( 403 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return event data if event accessible
	 */
	public function it_should_return_event_data_if_event_accessible( Restv1Tester $I ) {
		$image_id = $I->havePostInDatabase( [
			'post_type'      => 'attachment',
			'post_title'     => 'image',
			'guid'           => $this->site_url . '/images/image.png',
			'post_mime_type' => 'image/png',
			'meta_input'     => [
				'_wp_attached_file' => 'images/image.png',
			]
		] );
		$id = $I->haveEventInDatabase( [
			'post_author'       => '12',
			'post_title'        => 'Event 01',
			'post_name'         => 'event-01',
			'post_content'      => 'Event 01 description',
			'post_excerpt'      => 'Event 01 excerpt',
			'post_date'         => '2017-01-05 14:23:36',
			'post_date_gmt'     => '2017-01-05 14:23:36',
			'post_modified'     => '2017-01-05 14:23:36',
			'post_modified_gmt' => '2017-01-05 14:23:36',
			'meta_input'        => [
				'_thumbnail_id'          => $image_id,
				'_EventTimezone'         => 'America/New_York',
				'_EventTimezoneAbbr'     => 'EST',
				'_EventCost'             => '23',
				'_EventCurrencySymbol'   => '$',
				'_EventCurrencyPosition' => 'prefix',
				'_EventURL'              => 'http://tri.be',
				'_EventShowMap'          => '1',
				'_EventShowMapLink'      => '1',
				'_EventStartDate'        => '2017-01-05 14:23:36',
				'_EventEndDate'          => '2017-01-05 16:23:36',
				'_EventStartDateUTC'     => '2017-01-05 14:23:36',
				'_EventEndDateUTC'       => '2017-01-05 16:23:36',
				'_EventDuration'         => '7200'
			],
		] );

		$I->sendGET( $this->events_url . '/' . $id );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [ 'ID' => $id ] );
		$I->seeResponseContainsJson( [ 'author' => '12' ] );
		$I->seeResponseContainsJson( [ 'date' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [ 'date_utc' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [ 'modified' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [ 'modified_utc' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [ 'link' => $this->site_url . 'event/event-01/' ] );
		$I->seeResponseContainsJson( [ 'rest_url' => $this->rest_url . 'events/' . $id ] );
		$I->seeResponseContainsJson( [ 'title' => 'Event 01' ] );
		$I->seeResponseContainsJson( [ 'description' => '<p>Event 01 description</p>' ] );
		$I->seeResponseContainsJson( [ 'excerpt' => '<p>Event 01 excerpt</p>' ] );
		$I->seeResponseContainsJson( [ 'featured_image' => $this->site_url . 'wp-content/uploads/images/image.png' ] );
		$I->seeResponseContainsJson( [ 'start_date' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [
			'start_date_details' => [
				'year'    => '2017',
				'month'   => '01',
				'day'     => '05',
				'hour'    => '14',
				'minutes' => '23',
				'seconds' => '36',
			]
		] );
		$I->seeResponseContainsJson( [ 'end_date' => '2017-01-05 16:23:36' ] );
		$I->seeResponseContainsJson( [
			'end_date_details' => [
				'year'    => '2017',
				'month'   => '01',
				'day'     => '05',
				'hour'    => '16',
				'minutes' => '23',
				'seconds' => '36',
			]
		] );
		$I->seeResponseContainsJson( [ 'utc_start_date' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [
			'utc_start_date_details' => [
				'year'    => '2017',
				'month'   => '01',
				'day'     => '05',
				'hour'    => '14',
				'minutes' => '23',
				'seconds' => '36',
			]
		] );
		$I->seeResponseContainsJson( [ 'utc_end_date' => '2017-01-05 16:23:36' ] );
		$I->seeResponseContainsJson( [
			'utc_end_date_details' => [
				'year'    => '2017',
				'month'   => '01',
				'day'     => '05',
				'hour'    => '16',
				'minutes' => '23',
				'seconds' => '36',
			]
		] );
		$I->seeResponseContainsJson( [ 'timezone' => 'America/New_York' ] );
		$I->seeResponseContainsJson( [ 'timezone_abbr' => 'EST' ] );
		$I->seeResponseContainsJson( [ 'cost' => '23' ] );
		$I->seeResponseContainsJson( [
			'cost_details' => [
				'currency_symbol'   => '$',
				'currency_position' => 'prefix',
				'cost'              => '23'
			]
		] );
		$I->seeResponseContainsJson( [ 'website' => 'http://tri.be' ] );
		$I->seeResponseContainsJson( [ 'show_map' => '1' ] );
		$I->seeResponseContainsJson( [ 'show_map_link' => '1' ] );

	}
}
