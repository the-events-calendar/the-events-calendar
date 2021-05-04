<?php

class ListStyleHeadingCest {
	public function _before( AcceptanceTester $I ) {
		$I->haveOptionInDatabase( 'permalink_structure', '/%postname%/' );
		$I->setTribeOption( 'views_v2_enabled', true );
		$I->setTribeOption( 'postsPerPage', 12 );
	}

	/**
	 * i do see the upcoming header when there are no additional pages of events
	 *
	 * @test
	 */
	public function i_do_see_the_upcoming_header_when_there_are_no_additional_pages_of_events( AcceptanceTester $I ) {
		$I->haveManyEventsInDatabase( 10, [ 'when' => 'tomorrow' ] );
		$I->amOnPage( '/events/list/' );

		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 1 );
		$I->dontSeeElement( '.tribe-events-c-top-bar__datepicker-separator' );
		$I->see( 'Upcoming', \Codeception\Util\Locator::firstElement( 'time.tribe-events-c-top-bar__datepicker-time' ) );
	}

	/**
	 * should render only the now label when all the events are happening o
	 *
	 * @test
	 */
	public function should_render_only_the_now_label_when_all_the_events_are_happening_today( AcceptanceTester $I ) {
		$I->haveManyEventsInDatabase( 14, [ 'when' => 'now' ] );

		$I->amOnPage( '/events/list/' );

		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 1 );
		$I->dontSeeElement( '.tribe-events-c-top-bar__datepicker-separator' );
		$I->see( 'Now', \Codeception\Util\Locator::firstElement( 'time.tribe-events-c-top-bar__datepicker-time' ) );
	}


	/**
	 * i do see the dates header on the first page when there are more than one page of results
	 *
	 * @test
	 */
	public function i_do_see_the_dates_header_on_the_first_page_when_there_are_more_than_one_page_of_results( AcceptanceTester $I ) {
		$ids                           = $I->haveManyEventsInDatabase( 14, [ 'when' => 'tomorrow' ], 24 );
		$last_occurrence_of_first_page = $ids[11]; // 12 event 0 based index.
		$date                          = tribe_get_start_date( $last_occurrence_of_first_page, false, tribe_get_date_option( 'dateWithoutYearFormat', 'F j' ) );

		$I->amOnPage( '/events/list/' );
		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 2 );
		$I->seeNumberOfElements( '.tribe-events-c-top-bar__datepicker-separator', 1 );

		codecept_debug( "Looking for label: 'Now - {$date}'" );

		$I->see( 'Now', \Codeception\Util\Locator::firstElement( 'time.tribe-events-c-top-bar__datepicker-time' ) );
		$I->see( $date, \Codeception\Util\Locator::elementAt( 'time.tribe-events-c-top-bar__datepicker-time', 2 ) );
	}

	/**
	 * should render the onwards label on the last page of results
	 *
	 * @test
	 */
	public function should_render_the_onwards_label_on_the_last_page_of_results( AcceptanceTester $I ) {
		$ids                           = $I->haveManyEventsInDatabase( 14, [ 'when' => 'tomorrow' ], 24 );
		$last_occurrence_of_first_page = $ids[12]; // 13 event
		$date                          = tribe_get_start_date( $last_occurrence_of_first_page, false, tribe_get_date_option( 'dateWithoutYearFormat', 'F j' ) );

		$I->amOnPage( '/events/list/page/2/' );
		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 1 );
		$I->dontSeeElement( '.tribe-events-c-top-bar__datepicker-separator' );

		codecept_debug( "Looking for label: '{$date} onwards'" );
		$I->see( "{$date} onwards", \Codeception\Util\Locator::firstElement( 'time.tribe-events-c-top-bar__datepicker-time' ) );
	}
}
