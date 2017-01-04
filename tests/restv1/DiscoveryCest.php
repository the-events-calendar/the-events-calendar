<?php


use Codeception\Configuration;

class DiscoveryCest {

	/**
	 * @var string
	 */
	protected $tec_option = 'tribe_events_calendar_options';

	/**
	 * @var string
	 */
	protected $rest_disable_option = 'rest-v1-disabled';

	/**
	 * @var string The site full URL to the REST API root.
	 */
	protected $rest_url;

	/**
	 * @var string The site full URL to the homepage.
	 */
	protected $site_url;

	public function _before( Restv1Tester $I ) {
		$configuration = Configuration::config();
		$this->rest_url = $configuration['modules']['config']['REST']['url'];
		$this->site_url = str_replace( '/wp-json/tribe/events/v1', '', $this->rest_url );
	}

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
		$tomorrow = date( 'Y-m-d ', strtotime( '+1 day' ) );
		$meta_input = [
			'_EventStartDate'    => $tomorrow . '08:00:00',
			'_EventEndDate'      => $tomorrow . '17:00:00',
			'_EventStartDateUTC' => $tomorrow . '08:00:00',
			'_EventEndDateUTC'   => $tomorrow . '17:00:00',
			'_EventDuration'     => '32400',
		];

		$id = $I->havePostInDatabase( [ 'post_type' => 'tribe_events', 'post_name' => 'event-01', 'meta_input' => $meta_input ] );
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
