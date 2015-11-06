<?php


class Issue_38042_Dropping_CategoryCest {

	protected $an_existing_event_category = 'barbecue';

	public function _before( AcceptanceTester $I ) {
		$option_initial_state = "a:49:{s:16:\"tribeEnableViews\";a:3:{i:0;s:4:\"list\";i:1;s:5:\"month\";i:2;s:3:\"day\";}s:14:\"schema-version\";s:3:\"4.0\";s:27:\"recurring_events_are_hidden\";s:7:\"exposed\";s:21:\"previous_ecp_versions\";a:5:{i:0;s:1:\"0\";i:1;s:6:\"3.12a1\";i:2;s:7:\"3.12rc1\";i:3;s:6:\"3.12.3\";i:4;s:6:\"3.12.4\";}s:18:\"latest_ecp_version\";s:3:\"4.0\";s:19:\"last-update-message\";s:3:\"4.0\";s:13:\"earliest_date\";s:19:\"2015-09-02 00:00:00\";s:11:\"latest_date\";s:19:\"2016-02-14 12:00:00\";s:16:\"stylesheetOption\";s:5:\"tribe\";s:19:\"tribeEventsTemplate\";s:0:\"\";s:10:\"viewOption\";s:5:\"month\";s:20:\"tribeDisableTribeBar\";b:0;s:16:\"monthEventAmount\";s:1:\"3\";s:23:\"enable_month_view_cache\";b:0;s:18:\"dateWithYearFormat\";s:6:\"F j, Y\";s:21:\"dateWithoutYearFormat\";s:3:\"F j\";s:18:\"monthAndYearFormat\";s:3:\"F Y\";s:17:\"dateTimeSeparator\";s:3:\" @ \";s:18:\"timeRangeSeparator\";s:3:\" - \";s:16:\"datepickerFormat\";s:1:\"0\";s:21:\"tribeEventsBeforeHTML\";s:0:\"\";s:20:\"tribeEventsAfterHTML\";s:0:\"\";s:29:\"disable_metabox_custom_fields\";s:4:\"show\";s:18:\"pro-schema-version\";s:3:\"4.0\";s:18:\"hideLocationSearch\";b:0;s:17:\"hideRelatedEvents\";b:0;s:23:\"week_view_hide_weekends\";b:0;s:13:\"weekDayFormat\";s:4:\"D jS\";s:21:\"events_filters_layout\";s:8:\"vertical\";s:28:\"events_filters_default_state\";s:4:\"open\";s:11:\"donate-link\";b:0;s:12:\"postsPerPage\";s:2:\"10\";s:17:\"liveFiltersUpdate\";b:1;s:32:\"hideSubsequentRecurrencesDefault\";b:0;s:31:\"userToggleSubsequentRecurrences\";b:0;s:25:\"recurrenceMaxMonthsBefore\";s:2:\"24\";s:24:\"recurrenceMaxMonthsAfter\";s:2:\"24\";s:12:\"showComments\";b:0;s:20:\"showEventsInMainLoop\";b:0;s:10:\"eventsSlug\";s:6:\"events\";s:15:\"singleEventSlug\";s:5:\"event\";s:14:\"multiDayCutoff\";s:5:\"00:00\";s:21:\"defaultCurrencySymbol\";s:1:\"$\";s:23:\"reverseCurrencyPosition\";b:0;s:15:\"embedGoogleMaps\";b:1;s:23:\"geoloc_default_geofence\";s:2:\"25\";s:19:\"geoloc_default_unit\";s:5:\"miles\";s:19:\"embedGoogleMapsZoom\";s:2:\"10\";s:11:\"debugEvents\";b:0;}";
		$I->haveOptionInDatabase( 'tribe_events_calendar_options', $option_initial_state );
		$I->amOnPage( "/events/category/{$this->an_existing_event_category}" );
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
		$I->assertContains( "tribe_events_cat={$this->an_existing_event_category}", $href );
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
		$I->assertContains( "tribe_events_cat={$this->an_existing_event_category}", $href );
		$I->assertContains( 'tribe-bar-date', $href );
	}

	/**
	 * @test
	 * it should not drop the category when using the month selector then the search
	 */
	public function it_should_not_drop_the_category_when_using_the_month_selector_and_the_search( AcceptanceTester $I ) {
		$I->click( [ 'css' => 'input#tribe-bar-date' ] );
		// I click the 5th month in the datepicker, this will submit
		$I->click( [ 'css' => 'body > .datepicker > div.datepicker-months span:nth-child(5)' ] );

		$I->waitForJqueryAjax( 10 );

		$I->fillField( 'input[name="tribe-bar-search"]', 'foo' );
		$I->click( 'input[name="submit-bar"]' );

		$I->waitForJqueryAjax( 10 );

		$href = $I->grabFullUrl();
		$I->assertContains( "tribe_events_cat={$this->an_existing_event_category}", $href );
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
		$I->assertContains( "tribe_events_cat={$this->an_existing_event_category}", $href );
		$I->assertContains( 'tribe-bar-date', $href );
		$I->assertContains( 'tribe-bar-search=foo', $href );
	}
}
