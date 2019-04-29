<?php

namespace V2;

use Views_restTester as Tester;

class ViewHtmlCest extends Base {
	/**
	 * It should return a view HTML when requested
	 *
	 * @test
	 */
	public function should_return_a_view_html_when_requested( Tester $I ) {
		$code = file_get_contents( codecept_data_dir( 'Views/V2/mu-plugins/view-html.php' ) );
		$code = preg_replace( '/^\<\?php\\s*/', '', $code );
		$I->haveMuPlugin( 'view-html.php', $code );
		$I->wait_for_container_to_sync_files();
		$query_args = [ 'view' => 'test' ];
		$url = add_query_arg( $query_args, $this->home_url );
		$nonce = $I->generate_nonce_for_role( 'visitor' );

		$I->sendGET( $this->endpoint . '/html', [
			'url'   => $url,
			'nonce' => $nonce,
		] );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseEquals('<p>Test View 1 HTML output</p>');
	}
}
