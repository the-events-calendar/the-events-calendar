<?php


/**
 * Class DatePickerFrontEndCest
 *
 * Tests that back-end date picker format settings will be used and honored on the site front-end
 */
class DatePickerFrontEndCest {

	/**
	 * The possible date formats in the same order and number used by the date picker format select in Events > Settings > Display > Datepicker Date Format
	 *
	 * @var array
	 */
	protected $datepicker_formats = [
		'Y-m-d',
		'n/j/Y',
		'm/d/Y',
		'j/n/Y',
		'd/m/Y',
		'n-j-Y',
		'm-d-Y',
		'j-n-Y',
		'd-m-Y',
	];

	/**
	 * @var DateTime
	 */
	protected $today_date = '';

	public function _before( AcceptanceTester $I ) {
		$I->setTribeOption( 'eventsSlug', 'events' );
		$this->today_date = $this->today_date();
	}

	/**
	 * @test
	 * it should show date picker date using Y-m-d format
	 */
	public function it_should_show_date_picker_date_using_y_m_d_format( AcceptanceTester $I ) {
		$datepicker_index  = 0;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	/**
	 * @test
	 * it should show date picker date using n/j/Y format
	 */
	public function it_should_show_date_picker_date_using_n_j_y_format( AcceptanceTester $I ) {
		$datepicker_index  = 1;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	/**
	 * @test
	 * it should show date picker date using m/d/Y format
	 */
	public function it_should_show_date_picker_date_using_m_d_y_format( AcceptanceTester $I ) {
		$datepicker_index  = 2;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	/**
	 * @test
	 * it should show date picker date using j/n/Y format
	 */
	public function it_should_show_date_picker_date_using_j_n_y_format( AcceptanceTester $I ) {
		$datepicker_index  = 3;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	/**
	 * @test
	 * it should show date picker date using d/m/Y format
	 */
	public function it_should_show_date_picker_date_using_d_m_y_format( AcceptanceTester $I ) {
		$datepicker_index  = 4;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	/**
	 * @test
	 * it should show date picker date using n-j-Y format
	 */
	public function it_should_show_date_picker_date_using_dashed_n_j_y_format( AcceptanceTester $I ) {
		$datepicker_index  = 5;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	/**
	 * @test
	 * it should show date picker date using m-d-Y format
	 */
	public function it_should_show_date_picker_date_using_dashed_m_d_y_format( AcceptanceTester $I ) {
		$datepicker_index  = 6;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	/**
	 * @test
	 * it should show date picker date using j-n-Y format
	 */
	public function it_should_show_date_picker_date_using_dashed_j_n_Y_format( AcceptanceTester $I ) {
		$datepicker_index  = 7;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	/**
	 * @test
	 * it should show date picker date using d/m/Y format
	 */
	public function it_should_show_date_picker_date_using_dashed_d_m_y_format( AcceptanceTester $I ) {
		$datepicker_index  = 8;
		$datepicker_format = $this->datepicker_formats[ $datepicker_index ];

		$I->am( 'a visitor of the events calendar page' );
		$I->wantToTest( 'that the date picker display format is the "' . $datepicker_format . '" one' );

		$I->setTribeOption( 'datepickerFormat', '' . $datepicker_index );

		$I->amOnPage( '/events/today' );
		$expected_display_date = $this->today_date->format( $datepicker_format );
		$display_date          = $I->executeJS( 'return jQuery("#tribe-bar-date").val()' );
		$I->assertEquals( $display_date, $expected_display_date );
	}

	public function _after( AcceptanceTester $I ) {
	}

	/**
	 * @return DateTime
	 */
	protected function today_date() {
		return new DateTime();
	}
}
