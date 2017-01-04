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
		$I->haveEventInDatabase( [ 'post_name' => 'event-01' ] );

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
}
