<?php


class Issue_38042_Dropping_CategoryCest {

	protected $an_existing_event_category = 'barbecue';

	public function _before( AcceptanceTester $I ) {
		$I->loginAsAdmin();
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
		$I->waitForJS( 'return jQuery.active == 0', 10 );
		$href = $I->executeJS( 'return location.href' );
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
		$I->waitForJS( 'return jQuery.active == 0', 10 );
		$href = $I->executeJS( 'return location.href' );
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

		$I->waitForJS( 'return jQuery.active == 0', 10 );

		$I->fillField( 'input[name="tribe-bar-search"]', 'foo' );
		$I->click( 'input[name="submit-bar"]' );

		$I->waitForJS( 'return jQuery.active == 0', 10 );

		$href = $I->executeJS( 'return location.href' );
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

		$I->waitForJS( 'return jQuery.active == 0', 10 );

		$I->click( [ 'css' => 'input#tribe-bar-date' ] );
		// I click the 5th month in the datepicker, this will submit
		$I->click( [ 'css' => 'body > .datepicker > div.datepicker-months span:nth-child(5)' ] );

		$I->waitForJS( 'return jQuery.active == 0', 10 );

		$href = $I->executeJS( 'return location.href' );
		$I->assertContains( "tribe_events_cat={$this->an_existing_event_category}", $href );
		$I->assertContains( 'tribe-bar-date', $href );
		$I->assertContains( 'tribe-bar-search=foo', $href );
	}
}
