<?php

use Tribe\Events\Views\V2\View;
use Views_uiTester as Tester;

class MainQueryRenderCest {

	public function _before( Tester $I ) {
		$I->setTribeOption( View::$option_enabled, true );
	}

	/**
	 * It should correctly render a mock List view
	 *
	 * @test
	 */
	public function should_correctly_render_a_mock_list_view(Tester $I)
	{
		$slug = 'test-list';
		$code = file_get_contents( codecept_data_dir( 'Views/V2/mu-plugins/test-list-view.php' ) );
		$I->setTribeOption( View::$option_default, $slug );
		$I->haveMuPlugin( 'test-list-view.php', $code );
		$I->wait_for_container_to_sync_files();

		$I->amOnPage(add_query_arg([
			'view' => 'test-list',
		], '/events'));

		$I->seeElement( '.tribe-view' );
		$I->seeElement( '.tribe-view--' . $slug );
	}
}
