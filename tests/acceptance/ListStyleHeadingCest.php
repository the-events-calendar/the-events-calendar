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
	 * should render only the now label when all the events are happening today
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
		$last_occurrence_of_first_page = $ids[11]; // 12th event, 0 based index.
		$date                          = tribe_get_start_date( $last_occurrence_of_first_page, false, tribe_get_date_option( 'dateWithoutYearFormat', 'F j' ) );

		$I->amOnPage( '/events/list/' );
		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 2 );
		$I->seeNumberOfElements( '.tribe-events-c-top-bar__datepicker-separator', 1 );

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
		$last_occurrence_of_first_page = $ids[12]; // 13th event

		// Account for year-end.
		$now  = (int) Tribe__Date_Utils::build_date_object()->format('Y');
		$then = (int) tribe_get_start_date( $last_occurrence_of_first_page, false, 'Y' );

		if ( $then === $now ) {
			$format = tribe_get_date_option( 'dateWithoutYearFormat', 'F j' );
		} else {
			$format = tribe_get_date_option( 'dateWithYearFormat', 'F j, Y' );
		}

		$date = tribe_get_start_date( $last_occurrence_of_first_page, false, $format );

		$I->amOnPage( '/events/list/page/2/' );
		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 1 );
		$I->dontSeeElement( '.tribe-events-c-top-bar__datepicker-separator' );

		$I->see( "{$date} onwards", \Codeception\Util\Locator::firstElement( '.tribe-events-c-top-bar__datepicker-desktop' ) );
	}

	/**
	 * only has a single page of events  and filters by date
	 *
	 * @test
	 */
	public function only_has_a_single_page_of_events_and_filters_by_date( AcceptanceTester $I ) {
		$ids                           = $I->haveManyEventsInDatabase( 10, [ 'when' => 'tomorrow' ], 24 );
		$last_occurrence_of_first_page = reset( $ids );
		$date                          = tribe_get_start_date( $last_occurrence_of_first_page, false, tribe_get_date_option( 'dateWithoutYearFormat', 'F j' ) );

		$I->amOnPage( '/events/list/?tribe-bar-date=' . tribe_get_start_date( $last_occurrence_of_first_page, false, Tribe__Date_Utils::DBDATEFORMAT ) );
		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 1 );
		$I->dontSeeElement( '.tribe-events-c-top-bar__datepicker-separator' );

		$I->see( "{$date} onwards", \Codeception\Util\Locator::firstElement( '.tribe-events-c-top-bar__datepicker-desktop' ) );
	}

	/**
	 * renders the dates ranges if there are multiple pages of events with date filtering
	 *
	 * @test
	 */
	public function renders_the_dates_ranges_if_there_are_multiple_pages_of_events_with_date_filtering( AcceptanceTester $I ) {
		$ids                           = $I->haveManyEventsInDatabase( 14, [ 'when' => 'tomorrow' ], 24 );
		$last_occurrence_of_first_page = $ids[11]; // Last element on the first page of a 12 events per page setup.

		// Account for year-end.
		$now  = (int) Tribe__Date_Utils::build_date_object()->format('Y');
		$then = (int) tribe_get_start_date( $last_occurrence_of_first_page, false, 'Y' );

		if ( $then === $now ) {
			$format = tribe_get_date_option( 'dateWithoutYearFormat', 'F j' );
		} else {
			$format = tribe_get_date_option( 'dateWithYearFormat', 'F j, Y' );
		}

		$start_date                    = tribe_get_start_date( $ids[0], false, $format );
		$end_date                      = tribe_get_start_date( $last_occurrence_of_first_page, false, $format );

		$I->amOnPage( '/events/list/?tribe-bar-date=' . tribe_get_start_date( $ids[0], false, Tribe__Date_Utils::DBDATEFORMAT ) );
		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 2 );
		$I->seeNumberOfElements( '.tribe-events-c-top-bar__datepicker-separator', 1 );

		$I->see( $start_date, \Codeception\Util\Locator::firstElement( 'time.tribe-events-c-top-bar__datepicker-time' ) );
		$I->see( $end_date, \Codeception\Util\Locator::elementAt( 'time.tribe-events-c-top-bar__datepicker-time', 2 ) );
	}

	/**
	 * display onwards when visiting the last page of events and the date picker has been used
	 *
	 * @test
	 */
	public function display_onwards_when_visiting_the_last_page_of_events_and_the_date_picker_has_been_used( AcceptanceTester $I ) {
		$ids                          = $I->haveManyEventsInDatabase( 14, [ 'when' => 'tomorrow' ], 24 );
		$first_occurrence_second_page = $ids[12]; // First element on the second page of events

		// Account for year-end.
		$now  = (int) Tribe__Date_Utils::build_date_object()->format('Y');
		$then = (int) tribe_get_start_date( $first_occurrence_second_page, false, 'Y' );

		if ( $then === $now ) {
			$format = tribe_get_date_option( 'dateWithoutYearFormat', 'F j' );
		} else {
			$format = tribe_get_date_option( 'dateWithYearFormat', 'F j, Y' );
		}

		$start_date = tribe_get_start_date( $first_occurrence_second_page, false, $format );

		$I->amOnPage( '/events/list/page/2/?tribe-bar-date=' . tribe_get_start_date( $ids[0], false, Tribe__Date_Utils::DBDATEFORMAT ) );
		$I->seeNumberOfElements( 'time.tribe-events-c-top-bar__datepicker-time', 1 );
		$I->dontSeeElement( '.tribe-events-c-top-bar__datepicker-separator' );

		$I->see( "{$start_date} onwards", \Codeception\Util\Locator::firstElement( 'time.tribe-events-c-top-bar__datepicker-time' ) );
	}
}
