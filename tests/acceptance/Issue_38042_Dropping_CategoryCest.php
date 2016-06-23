<?php


class Issue_38042_Dropping_CategoryCest {

	protected $term_slug = 'probably-not-in-database';

	public function _before( AcceptanceTester $I ) {
		// use the default events template and set the view to month
		$I->setTribeOption( 'tribeEventsTemplate', '' );
		$I->setTribeOption( 'viewOption', 'month' );

		$I->haveTermInDatabase( $this->term_slug, 'tribe_events_cat', [ 'slug' => $this->term_slug ] );

		$I->amOnPage( "/events/category/{$this->term_slug}" );

		// Why not inserting some posts and categories too?
		//This issue *should* be independent of posts and categories in the database.
	}

	/**
	 * @test
	 * it should not drop the category when using the Tribe search bar
	 */
	public function it_should_not_drop_the_category_when_using_the_tribe_search_bar( AcceptanceTester $I ) {
		$I->click( '#tribe-bar-collapse-toggle:not(.tribe-bar-filters-open)' );

		$I->fillField( 'input[name="tribe-bar-search"]', 'foo' );
		$I->click( 'input[name="submit-bar"]' );

		$I->waitForJqueryAjax( 10 );

		$href = $I->grabFullUrl();
		$I->assertContains( 'tribe-bar-search=foo', $href );
		$I->assertContains( 'tribe_events_cat=', $href );
	}

	/**
	 * @test
	 * it should not drop the category when using the month selector
	 */
	public function it_should_not_drop_the_category_when_using_the_month_selector( AcceptanceTester $I ) {
		$I->click( '#tribe-bar-collapse-toggle:not(.tribe-bar-filters-open)' );

		$I->waitForElement( 'input#tribe-bar-date' );

		$I->click( 'input#tribe-bar-date' );
		// I click the 5th month in the datepicker
		$I->click( 'body > .datepicker > div.datepicker-months span:nth-child(5)' );

		$I->waitForJqueryAjax( 10 );

		$href = $I->grabFullUrl();
		$I->assertContains( 'tribe_events_cat=', $href );
		$I->assertContains( 'tribe-bar-date', $href );
	}

	/**
	 * @test
	 * it should not drop the category when using the month selector then the search
	 */
	public function it_should_not_drop_the_category_when_using_the_month_selector_then_the_search( AcceptanceTester $I ) {
		$I->click( '#tribe-bar-collapse-toggle:not(.tribe-bar-filters-open)' );

		$I->click( 'input#tribe-bar-date' );
		// I click the 5th month in the datepicker, this will submit
		$I->click( 'body > .datepicker > div.datepicker-months span:nth-child(11)' );

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
		$I->click( '#tribe-bar-collapse-toggle:not(.tribe-bar-filters-open)' );

		$I->fillField( 'input[name="tribe-bar-search"]', 'foo' );
		$I->click( 'input[name="submit-bar"]' );

		$I->waitForJqueryAjax( 10 );

		$I->click( 'input#tribe-bar-date' );
		// I click the 5th month in the datepicker, this will submit
		$I->click( 'body > .datepicker > div.datepicker-months span:nth-child(5)' );

		$I->waitForJqueryAjax( 10 );

		$href = $I->grabFullUrl();
		$I->assertContains( 'tribe_events_cat=', $href );
		$I->assertContains( 'tribe-bar-date', $href );
		$I->assertContains( 'tribe-bar-search=foo', $href );
	}
}
