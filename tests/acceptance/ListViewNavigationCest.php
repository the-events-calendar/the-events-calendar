<?php
class ListViewNavigationCest {
	public function _before( AcceptanceTester $I ) {
		$I->haveOptionInDatabase( 'permalink_structure', '/%postname%/' );
		$I->setTribeOption( 'views_v2_enabled', false );
		$I->setTribeOption( 'postsPerPage', 1 );
	}

	/**
	 * @test
	 */
	public function i_see_previous_link_on_list_page_when_there_are_past_events( AcceptanceTester $I ) {
		$I->haveEventInDatabase( [
			'when' => '2 months ago',
		] );

		$I->haveEventInDatabase( [
			'when' => 'tomorrow',
		] );

		$I->amOnPage('/events/list/');
		$I->seeElement( '.tribe-events-nav-previous a[rel="prev"]' );
	}

	/**
	 * @test
	 */
	public function i_do_not_see_previous_link_on_list_page_when_there_are_no_past_events( AcceptanceTester $I ) {
		$I->haveEventInDatabase( [
			'when' => 'tomorrow',
		] );

		$I->amOnPage('/events/list/');
		$I->dontSeeElement( '.tribe-events-nav-previous a[rel="prev"]' );
	}

	/**
	 * @test
	 */
	public function i_see_next_link_on_list_page_when_there_are_future_events( AcceptanceTester $I ) {
		$I->haveEventInDatabase( [
			'when' => 'tomorrow',
		] );

		$I->haveEventInDatabase( [
			'when' => '+2 months',
		] );

		$I->amOnPage('/events/list/');
		$I->seeElement( '.tribe-events-nav-next a[rel="next"]' );
	}

	/**
	 * @test
	 */
	public function i_do_not_see_next_link_on_list_page_when_there_are_no_future_events( AcceptanceTester $I ) {
		$I->haveEventInDatabase( [
			'when' => 'tomorrow',
		] );

		$I->amOnPage('/events/list/');
		$I->dontSeeElement( '.tribe-events-nav-next a[rel="next"]' );
	}
}
