<?php

use Tribe\Events\Views\V2\Manager;
use Views_uiTester as Tester;

class MainQueryRenderCest {

	public function _before( Tester $I ) {
		$I->setTribeOption( Manager::$option_enabled, true );
		$I->setTribeOption( 'tribeEventsTemplate', 'events' );
	}

	/**
	 * @test
	 */
	public function should_correctly_render_a_mock_list_view( Tester $I, $scenario ) {
		$scenario->skip();
		$I->comment( 'Skipped due to revision to how basic template should work' );


		$slug = 'test-list';
		$code = file_get_contents( codecept_data_dir( 'Views/V2/mu-plugins/test-list-view.php' ) );
		$I->setTribeOption( Manager::$option_default, $slug );
		$I->haveMuPlugin( 'test-list-view.php', $code );
		$I->wait_for_container_to_sync_files();

		$I->amOnPage( add_query_arg( [
			'tribe_view' => 'test-list',
		], '/events' ) );

		$I->seeElement( '.tribe-view' );
		$I->seeElement( '.tribe-view--' . $slug );
	}
}
