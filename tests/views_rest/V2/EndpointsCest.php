<?php

namespace V2;


use Views_restTester as Tester;

class EndpointsCest extends Base{

	/**
	 * It should provide an HTML endpoint on the REST API
	 *
	 * @test
	 */
	public function should_provide_an_html_endpoint_on_the_rest_api( Tester $I ) {
		$query_args = [ 'view' => 'list' ];
		$url = add_query_arg( $query_args, $this->home_url );
		$nonce = $I->generate_nonce_for_role( 'visitor' );

		$I->sendGET( $this->endpoint . '/html', [
			'url'   => $url,
			'nonce' => $nonce,
		] );

		$I->seeResponseCodeIs( 200 );
	}

	/**
	 * It should block requests not providing a nonce
	 *
	 * @test
	 */
	public function should_block_requests_not_providing_a_nonce( Tester $I ) {
		$query_args = [ 'view' => 'list' ];
		$url = add_query_arg( $query_args, $this->home_url );

		$I->sendGET( $this->endpoint . '/html', [
			'url' => $url,
		] );

		$I->seeResponseCodeIs( 401 );
	}
}
