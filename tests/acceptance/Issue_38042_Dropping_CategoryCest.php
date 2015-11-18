<?php


class Issue_38042_Dropping_CategoryCest {

	protected $an_existing_event_category = 'barbecue';
	protected $settings_backup;

	public function _before( AcceptanceTester $I ) {
		// Make sure the WPDb module is not pointing to the test database but to the one currently serving the domain the acceptance test is hitting!
		$this->settings_backup = $I->grabOptionFromDatabase( 'tribe_events_calendar_options' );
		codecept_debug( "Settings backup: " . json_encode( $this->settings_backup ) );
		$option_initial_state = "a:27:{s:14:\"schema-version\";s:8:\"4.0beta2\";s:27:\"recurring_events_are_hidden\";s:7:\"exposed\";s:21:\"previous_ecp_versions\";a:1:{i:0;s:1:\"0\";}s:18:\"latest_ecp_version\";s:8:\"4.0beta2\";s:29:\"disable_metabox_custom_fields\";s:4:\"show\";s:18:\"pro-schema-version\";s:8:\"4.0beta2\";s:16:\"stylesheetOption\";s:5:\"tribe\";s:19:\"tribeEventsTemplate\";s:0:\"\";s:16:\"tribeEnableViews\";a:6:{i:0;s:4:\"list\";i:1;s:5:\"month\";i:2;s:4:\"week\";i:3;s:3:\"day\";i:4;s:3:\"map\";i:5;s:5:\"photo\";}s:10:\"viewOption\";s:5:\"month\";s:20:\"tribeDisableTribeBar\";b:0;s:18:\"hideLocationSearch\";b:0;s:17:\"hideRelatedEvents\";b:0;s:23:\"week_view_hide_weekends\";b:0;s:16:\"monthEventAmount\";s:1:\"3\";s:23:\"enable_month_view_cache\";b:0;s:18:\"dateWithYearFormat\";s:6:\"F j, Y\";s:21:\"dateWithoutYearFormat\";s:3:\"F j\";s:18:\"monthAndYearFormat\";s:3:\"F Y\";s:13:\"weekDayFormat\";s:4:\"D jS\";s:17:\"dateTimeSeparator\";s:3:\" @ \";s:18:\"timeRangeSeparator\";s:3:\" - \";s:16:\"datepickerFormat\";s:1:\"0\";s:21:\"tribeEventsBeforeHTML\";s:0:\"\";s:20:\"tribeEventsAfterHTML\";s:0:\"\";s:13:\"earliest_date\";s:19:\"2015-09-03 00:00:00\";s:11:\"latest_date\";s:19:\"2016-04-21 23:59:59\";}";
		$I->haveOptionInDatabase( 'tribe_events_calendar_options', $option_initial_state );
		$I->amOnPage( "/events/category/{$this->an_existing_event_category}" );

		// Why not inserting some posts and categories too?
		//This issue *should* be independent of posts and categories in the database.
	}

	public function _after( AcceptanceTester $I ) {
	}

	/**
	 * @test
	 * it should not drop the category when using the Tribe search bar
	 */
	public function it_should_not_drop_the_category_when_using_the_tribe_search_bar( AcceptanceTester $I ) {
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
	public function it_should_it_should_not_drop_the_category_when_unsing_the_month_selector( AcceptanceTester $I ) {
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
	public function it_should_not_drop_the_category_when_using_the_month_selector_and_the_search( AcceptanceTester $I ) {
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
	public function it_should_not_drop_category_when_using_the_search_then_the_month_selector( AcceptanceTester $I ) {

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
