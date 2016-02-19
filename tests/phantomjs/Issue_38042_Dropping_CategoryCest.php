<?php


class Issue_38042_Dropping_CategoryCest {

	protected $an_existing_event_category = 'barbecue';
	protected $settings_backup;

	public function _before( PhantomjsTester $I ) {
		$this->settings_backup = $I->grabOptionFromDatabase( 'tribe_events_calendar_options' );
		codecept_debug( "Settings backup: " . json_encode( $this->settings_backup ) );

		// use the default events template and set the view to month
		$options = $I->getDefaultCoreOptions( [ 'tribeEventsTemplate' => '', 'viewOption' => 'month' ] );
		$I->haveOptionInDatabase( 'tribe_events_calendar_options', $options );

		// let's resize the window not to incurr in mobile breakpoints
		$I->resizeWindow( 1200, 1000 );

		$I->amOnPage( "/events/category/{$this->an_existing_event_category}" );

		// Why not inserting some posts and categories too?
		//This issue *should* be independent of posts and categories in the database.
	}

	public function _after( PhantomjsTester $I ) {
	}

	/**
	 * @test
	 * it should not drop the category when using the Tribe search bar
	 */
	public function it_should_not_drop_the_category_when_using_the_tribe_search_bar( PhantomjsTester $I ) {
		$I->fillField( 'input[name="tribe-bar-search"]', 'foo' );
		$I->click( 'input[name="submit-bar"]' );

		$I->waitForJqueryAjax( 10 );

		$href = $I->grabFullUrl();
		$I->assertContains( 'tribe-bar-search=foo', $href );
		$I->assertContains( 'tribe_events_cat=', $href );
	}

	/**
	 * @test
	 * it should it should not drop the category when unsing the month selector
	 */
	public function it_should_it_should_not_drop_the_category_when_unsing_the_month_selector( PhantomjsTester $I ) {
		$I->click( [ 'css' => 'input#tribe-bar-date' ] );
		// I click the 5th month in the datepicker
		$I->click( [ 'css' => 'body > .datepicker > div.datepicker-months span:nth-child(5)' ] );

		$I->waitForJqueryAjax( 10 );

		$href = $I->grabFullUrl();
		$I->assertContains( 'tribe_events_cat=', $href );
		$I->assertContains( 'tribe-bar-date', $href );
	}

	/**
	 * @test
	 * it should not drop the category when using the month selector then the search
	 */
	public function it_should_not_drop_the_category_when_using_the_month_selector_and_the_search( PhantomjsTester $I ) {
		$I->click( [ 'css' => 'input#tribe-bar-date' ] );
		// I click the 5th month in the datepicker, this will submit
		$I->click( [ 'css' => 'body > .datepicker > div.datepicker-months span:nth-child(11)' ] );

		$I->waitForJqueryAjax( 10 );

		$I->fillField( 'input[name="tribe-bar-search"]', 'foo' );
		$I->click( 'input[name="submit-bar"]' );

		$I->waitForJqueryAjax( 10 );

		$href = $I->grabFullUrl();
		$I->assertContains( 'tribe_events_cat=', $href );
		$I->assertContains( 'tribe-bar-date', $href );
		$I->assertContains( 'tribe-bar-search=foo', $href );
	}

	/**
	 * @test
	 * it should not drop category when using the search then the month selector
	 */
	public function it_should_not_drop_category_when_using_the_search_then_the_month_selector( PhantomjsTester $I ) {

		$I->fillField( 'input[name="tribe-bar-search"]', 'foo' );
		$I->click( 'input[name="submit-bar"]' );

		$I->waitForJqueryAjax( 10 );

		$I->click( [ 'css' => 'input#tribe-bar-date' ] );
		// I click the 5th month in the datepicker, this will submit
		$I->click( [ 'css' => 'body > .datepicker > div.datepicker-months span:nth-child(5)' ] );

		$I->waitForJqueryAjax( 10 );

		$href = $I->grabFullUrl();
		$I->assertContains( 'tribe_events_cat=', $href );
		$I->assertContains( 'tribe-bar-date', $href );
		$I->assertContains( 'tribe-bar-search=foo', $href );
	}
}
