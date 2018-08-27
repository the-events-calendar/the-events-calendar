<?php


class DiscoveryCest extends BaseRestCest {

	/**
	 * @test
	 * it should return a custom headers for discovery
	 */
	public function it_should_return_custom_headers_for_discovery( Restv1Tester $I ) {
		$I->sendHEAD( $this->site_url );

		$I->seeHttpHeader( 'X-TEC-API-VERSION', 'v1' );
		$I->seeHttpHeader( 'X-TEC-API-ROOT', $this->rest_url );
	}

	/**
	 * @test
	 * it should return custom headers for discovery on single event links
	 */
	public function it_should_return_custom_headers_for_discovery_on_single_event_links( Restv1Tester $I ) {
		$id = $I->haveEventInDatabase( [ 'post_name' => 'event-01' ] );

		$I->sendHEAD( $this->site_url . '/event/event-01' );

		$I->seeHttpHeader( 'X-TEC-API-VERSION', 'v1' );
		$I->seeHttpHeader( 'X-TEC-API-ROOT', $this->rest_url . "events/{$id}" );
	}

	/**
	 * @test
	 * it should return disabled header if TEC REST API is disabled via option
	 */
	public function it_should_return_disabled_header_if_tec_rest_api_is_disabled_via_option( Restv1Tester $I ) {
		$I->haveOptionInDatabase( $this->tec_option, [ $this->rest_disable_option => true ] );

		$I->sendHEAD( $this->site_url );

		$I->seeHttpHeader( 'X-TEC-API-VERSION', 'disabled' );
		$I->dontSeeHttpHeader( 'X-TEC-API-ROOT', $this->rest_url );
	}

	/**
	 * @test
	 * it should return a category filtered root when hitting an event category page
	 */
	public function it_should_return_a_category_filtered_root_when_hitting_an_event_category_page(Restv1Tester $I) {
		$I->haveTermInDatabase( 'cat1', 'tribe_events_cat', [ 'slug' => 'cat1' ] );

		$I->sendHEAD( $this->site_url . '/events/category/cat1/' );

		$I->seeHttpHeader( 'X-TEC-API-VERSION', 'v1' );
		$I->seeHttpHeader( 'X-TEC-API-ROOT', $this->rest_url . 'events/?categories=cat1' );
	}

	/**
	 * @test
	 * it should return a tag filtered root when hitting and event tag page
	 */
	public function it_should_return_a_tag_filtered_root_when_hitting_and_event_tag_page(Restv1Tester $I) {
		$I->haveTermInDatabase( 'tag1', 'post_tag', [ 'slug' => 'tag1' ] );

		$I->sendHEAD( $this->site_url . '/tag/tag1/' );

		$I->seeHttpHeader( 'X-TEC-API-VERSION', 'v1' );
		$I->seeHttpHeader( 'X-TEC-API-ROOT', $this->rest_url .'events/?tags=tag1');
	}
}
