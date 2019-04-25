<?php

use Tribe\Events\Views\V2\View;
use Views_uiTester as Tester;

class MainQueryRenderCest {

	public function _before( Tester $I ) {
		$I->setTribeOption( View::$option_enabled, true );
	}

	/**
	 * It should correctly render the default main query view
	 *
	 * @test
	 */
	public function should_correctly_render_the_default_main_query_view( Tester $I ) {
		$slug = 'main-query-render-1';
		$I->setTribeOption( View::$option_default, $slug );
		$code = file_get_contents( codecept_data_dir( 'Views/V2/mu-plugins/MainQueryRenderCest-1.php' ) );
		$I->haveMuPlugin( 'main-query-render-1.php', $code );
		$I->wait_for_container_to_sync_files();

		$I->amOnPage( '/events' );

		$I->seeElement( '.tribe-view' );
		$I->seeElement( '.tribe-view--' . $slug );
	}
}
