<?php

namespace V2;

use Views_restTester as Tester;

class ContextCest extends Base {

	/**
	 * It should alter the view Context according to REST request parameters
	 *
	 * @test
	 */
	public function should_alter_the_view_context_according_to_rest_request_parameters(Tester $I) {
		$query_args = [ 'view' => 'reflector' ];
		$url = add_query_arg( $query_args, $this->home_url );
		$nonce = $I->generate_nonce_for_role( 'visitor' );

		$I->sendGET( $this->endpoint . '/html', [
			'url'       => $url,
			'_wpnonce'  => $nonce,
			'view_data' => [
				'one'         => 'two',
				'twentythree' => 89,
			],
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson( [
			'view'      => 'reflector',
			'url'       => $url,
			'_wpnonce'  => $nonce,
			'view_data' => [
				'one'         => 'two',
				'twentythree' => 89,
			],
		] );
	}
}
