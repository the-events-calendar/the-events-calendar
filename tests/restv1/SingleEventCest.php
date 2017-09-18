<?php


class SingleEventCest extends BaseRestCest {

	/**
	 * It should return bad request if event ID is is missing
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_event_id_is_missing(Restv1Tester $I) {
		$I->sendGET( $this->events_url . '/' );

		$I->seeResponseCodeIs( 404 ); // as it will not match any registered route
		$I->seeResponseIsJson();
	}

	/**
	 * It should return bad request if event ID is 0
	 *
	 * @test
	 */
	public function it_should_return_bad_request_if_event_id_is_0(Restv1Tester $I) {
		$I->sendGET( $this->events_url . '/0' );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return bad request if hitting a non existing single event endpoint
	 */
	public function it_should_return_bad_request_if_hitting_a_non_existing_single_event_endpoint( Restv1Tester $I ) {
		$I->sendGET( $this->events_url . '/13' );

		$I->seeResponseCodeIs( 400 );
		$I->seeResponseIsJson();
	}

	/**
	 * @test
	 * it should return bad request if id is not of an event
	 */
	public function it_should_return_bad_request_if_id_is_not_of_an_event( Restv1Tester $I ) {
		$id = $I->havePostInDatabase();

		$I->sendGET( $this->events_url . '/' . $id );

		$I->seeResponseCodeIs( 400 );
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

		$venue_id = $I->haveVenueInDatabase( [
			'post_author'       => '12',
			'post_title'        => 'Venue 01',
			'post_name'         => 'venue-01',
			'post_content'      => 'Venue 01 description',
			'post_excerpt'      => 'Venue 01 excerpt',
			'post_date'         => '2017-01-05 14:23:36',
			'post_date_gmt'     => '2017-01-05 14:23:36',
			'post_modified'     => '2017-01-05 14:23:36',
			'post_modified_gmt' => '2017-01-05 14:23:36',
			'meta_input'        => [
				'_EventShowMap'       => '1',
				'_EventShowMapLink'   => '1',
				'_VenueAddress'       => 'address',
				'_VenueCity'          => 'city',
				'_VenueCountry'       => 'country',
				'_VenueProvince'      => 'province',
				'_VenueState'         => 'state',
				'_VenueZip'           => 'zip',
				'_VenuePhone'         => 'phone',
				'_VenueURL'           => 'url',
				'_VenueStateProvince' => 'state_province',
			],
		] );

		$organizer_id = $I->haveOrganizerInDatabase([
			'post_author'       => '12',
			'post_title'        => 'Organizer 01',
			'post_name'         => 'organizer-01',
			'post_content'      => 'Organizer 01 description',
			'post_excerpt'      => 'Organizer 01 excerpt',
			'post_date'         => '2017-01-05 14:23:36',
			'post_date_gmt'     => '2017-01-05 14:23:36',
			'post_modified'     => '2017-01-05 14:23:36',
			'post_modified_gmt' => '2017-01-05 14:23:36',
			'meta_input'        => [
				'_OrganizerPhone'   => 'phone',
				'_OrganizerWebsite' => 'website',
				'_OrganizerEmail'   => 'email',
			],
		] );

		$tag_1 = $I->haveTermInDatabase( 'tag-1', 'post_tag', [ 'slug' => 'tag-1', 'description' => 'Tag 1 description' ] )[0];
		$tag_2 = $I->haveTermInDatabase( 'tag-2', 'post_tag', [ 'slug' => 'tag-2', 'description' => 'Tag 2 description' ] )[0];

		$category_1 = $I->haveTermInDatabase( 'category-1', 'tribe_events_cat', [ 'slug' => 'category-1', 'description' => 'Category 1 description' ] )[0];
		$category_2 = $I->haveTermInDatabase( 'category-2', 'tribe_events_cat', [ 'slug' => 'category-2', 'description' => 'Category 2 description' ] )[0];

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
			'tax_input'         => [
				'post_tag'         => [ 'tag-1', 'tag-2' ],
				'tribe_events_cat' => [ 'category-1', 'category-2' ],
			],
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
				'_EventDuration'         => '7200',
				'_EventVenueID'          => $venue_id,
				'_EventOrganizerID'      => $organizer_id,
			],
		] );

		$I->sendGET( $this->events_url . '/' . $id );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [ 'id' => $id ] );
		$I->seeResponseContainsJson( [ 'author' => '12' ] );
		$I->seeResponseContainsJson( [ 'date' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [ 'date_utc' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [ 'modified' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [ 'modified_utc' => '2017-01-05 14:23:36' ] );
		$I->seeResponseContainsJson( [ 'status' => 'publish' ] );
		$I->seeResponseContainsJson( [ 'url' => $this->site_url . '/event/event-01/' ] );
		$I->seeResponseContainsJson( [ 'rest_url' => $this->rest_url . 'events/' . $id ] );
		$I->seeResponseContainsJson( [ 'title' => 'Event 01' ] );
		$I->seeResponseContainsJson( [ 'description' => '<p>Event 01 description</p>' ] );
		$I->seeResponseContainsJson( [ 'excerpt' => '<p>Event 01 excerpt</p>' ] );
		$I->seeResponseContainsJson( [ 'image' => [ 'url' => $this->site_url . '/wp-content/uploads/images/image.png' ] ] );
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
		$I->seeResponseContainsJson( [ 'cost' => '$23' ] );
		$I->seeResponseContainsJson( [
			'cost_details' => [
				'currency_symbol'   => '$',
				'currency_position' => 'prefix',
				'values'              => [ '23'  ]
			]
		] );
		$I->seeResponseContainsJson( [ 'website' => 'http://tri.be' ] );
		$I->seeResponseContainsJson( [ 'show_map' => true ] );
		$I->seeResponseContainsJson( [ 'show_map_link' => true ] );
		$I->seeResponseContainsJson( [
			'venue' => [
				'id'             => $venue_id,
				'author'         => '12',
				'date'           => '2017-01-05 14:23:36',
				'date_utc'       => '2017-01-05 14:23:36',
				'modified'       => '2017-01-05 14:23:36',
				'modified_utc'   => '2017-01-05 14:23:36',
				'status'         => 'publish',
				'url'            => $this->site_url . '/venue/venue-01/',
				'venue'          => 'Venue 01',
				'description'    => '<p>Venue 01 description</p>',
				'excerpt'        => '<p>Venue 01 excerpt</p>',
				'show_map'       => true,
				'show_map_link'  => true,
				'address'        => 'address',
				'city'           => 'city',
				'country'        => 'country',
				'province'       => 'province',
				'state'          => 'state',
				'zip'            => 'zip',
				'phone'          => 'phone',
				'website'        => 'url',
				'stateprovince' => 'state_province',
			],
		] );
		$I->seeResponseContainsJson( [
			'organizer' => [
				[
					'id'           => $organizer_id,
					'author'       => '12',
					'date'         => '2017-01-05 14:23:36',
					'date_utc'     => '2017-01-05 14:23:36',
					'modified'     => '2017-01-05 14:23:36',
					'modified_utc' => '2017-01-05 14:23:36',
					'status'       => 'publish',
					'url'          => $this->site_url . '/organizer/organizer-01/',
					'organizer'    => 'Organizer 01',
					'description'  => '<p>Organizer 01 description</p>',
					'excerpt'      => '<p>Organizer 01 excerpt</p>',
					'phone'        => 'phone',
					'website'      => 'website',
					'email'        => 'email',
				],
			]
		] );
		$I->seeResponseContainsJson( [
			'tags' => [
				[
					'id'          => $tag_1,
					'name'        => 'tag-1',
					'slug'        => 'tag-1',
					'taxonomy'    => 'post_tag',
					'description' => 'Tag 1 description',
				],
				[
					'id' => $tag_2,
					'name'        => 'tag-2',
					'slug'        => 'tag-2',
					'taxonomy'    => 'post_tag',
					'description' => 'Tag 2 description',
				],
			]
		] );
		$I->seeResponseContainsJson( [
			'categories' => [
				[
					'id'          => $category_1,
					'name'        => 'category-1',
					'slug'        => 'category-1',
					'taxonomy'    => 'tribe_events_cat',
					'description' => 'Category 1 description',
				],
				[
					'id' => $category_2,
					'name'        => 'category-2',
					'slug'        => 'category-2',
					'taxonomy'    => 'tribe_events_cat',
					'description' => 'Category 2 description',
				],
			]
		] );
	}
}
