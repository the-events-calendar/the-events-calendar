<?php
namespace Tribe\Events\Pro\Recurrence;

use Tribe__Events__Pro__Recurrence__Series_Rules_Factory as Series_Rules_Factory;
use Tribe__Events__Pro__Recurrence__Custom_Types as Custom_Types;

class Series_Rules_FactoryTest extends \Codeception\TestCase\WPTestCase {

	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be initializable
	 */
	public function it_should_be_initializable() {
		$this->assertInstanceOf( 'Tribe__Events__Pro__Recurrence__Series_Rules_Factory', new Series_Rules_Factory() );
	}

	/**
	 * @test
	 * it should return a Date series rule if custom date recurrence
	 */
	public function it_should_return_a_date_series_rule_if_custom_date_recurrence() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type'   => Custom_Types::CUSTOM_TYPE,
			'custom' => [ 'type' => Custom_Types::DATE_CUSTOM_TYPE, 'date' => [ 'date' => 'today' ] ],
		    'end' => 'today',
		];;
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Date $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Date', $out );
		$this->assertEquals( strtotime( 'today' ), $out->get_date_timestamp() );
	}

	/**
	 * @test
	 * it should return a Day series rules if Every Day recurrence type
	 */
	public function it_should_return_a_day_series_rules_if_every_day_recurrence_type() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::EVERY_DAY_TYPE,
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Day $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Day', $out );
		$this->assertEquals( 1, $out->get_days_between() );
	}

	/**
	 * @test
	 * it should return Day series rule if custom type is daily
	 */
	public function it_should_return_day_series_rule_if_custom_type_is_daily() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type'   => Custom_Types::CUSTOM_TYPE,
			'custom' => [ 'type' => Custom_Types::DAILY_CUSTOM_TYPE, 'interval' => 23 ]
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Day $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Day', $out );
		$this->assertEquals( 23, $out->get_days_between() );
	}

	/**
	 * @test
	 * it should return Week series rule if Every Week recurrence type
	 */
	public function it_should_return_week_series_rule_if_every_week_recurrence_type() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::EVERY_WEEK_TYPE,
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Week $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Week', $out );
		$this->assertEquals( 1, $out->get_weeks_between() );
		$this->assertEquals( [ ], $out->get_days() );
	}

	/**
	 * @test
	 * it should return Week series rule if Weekly custom type
	 */
	public function it_should_return_week_series_rule_if_weekly_custom_type() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::CUSTOM_TYPE, 'custom' => [
				'type' => Custom_Types::WEEKLY_CUSTOM_TYPE, 'interval' => 3, 'week' => [ 'day' => [ 3, 5, 2 ] ]
			],
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Week $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Week', $out );
		$this->assertEquals( 3, $out->get_weeks_between() );
		$this->assertEquals( [ 2, 3, 5 ], $out->get_days() );
	}

	/**
	 * @test
	 * it should return Month series rule if Every Month recurrence type
	 */
	public function it_should_return_month_series_rule_if_every_month_recurrence_type() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::EVERY_MONTH_TYPE,
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Month $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Month', $out );
		$this->assertEquals( 1, $out->get_months_between() );
		$this->assertEquals( [ ], $out->get_days_of_month() );
		$this->assertNull( $out->get_week_of_month() );
		$this->assertNull( $out->get_day_of_week() );
	}

	/**
	 * @test
	 * it should return Month series rule if Monthly custom recurrence type
	 */
	public function it_should_return_month_series_rule_if_monthly_custom_recurrence_type() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::CUSTOM_TYPE, 'custom' => [
				'type' => Custom_Types::MONTHLY_CUSTOM_TYPE, 'interval' => 3, 'month' => [ 'number' => 10, 'day' => 4 ]
			],
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Month $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Month', $out );
		$this->assertEquals( 3, $out->get_months_between() );
		$this->assertEquals( [ 10 ], $out->get_days_of_month() );
		$this->assertNull( $out->get_week_of_month() );
		$this->assertEquals( 4, $out->get_day_of_week() );
	}

	/**
	 * @test
	 * it should return Month series rule if monthly custom recurrence type and ordinal month number
	 */
	public function it_should_return_month_series_rule_if_monthly_custom_recurrence_type_and_ordinal_month_number() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::CUSTOM_TYPE,
			'custom' => [
				'type'  => Custom_Types::MONTHLY_CUSTOM_TYPE, 'interval' => 3,
				'month' => [ 'number' => 'Third', 'day' => 4 ]
			],
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Month $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Month', $out );
		$this->assertEquals( 3, $out->get_months_between() );
		$this->assertEquals( [ ], $out->get_days_of_month() );
		$this->assertEquals( 3, $out->get_week_of_month() );
		$this->assertEquals( 4, $out->get_day_of_week() );
	}

	/**
	 * @test
	 * it should return Year series rule if Every Year type
	 */
	public function it_should_return_year_series_rule_if_every_year_type() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::EVERY_YEAR_TYPE,
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Year $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Year', $out );
		$this->assertEquals( 1, $out->get_years_between() );
		$this->assertEquals( [ ], $out->get_months_of_year() );
		$this->assertNull( $out->get_week_of_month() );
		$this->assertNull( $out->get_day_of_week() );
	}

	/**
	 * @test
	 * it should return Year series rule if Yearly custom type
	 */
	public function it_should_return_year_series_rule_if_yearly_custom_type() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::CUSTOM_TYPE,
						'custom' => [
							'type'  => Custom_Types::YEARLY_CUSTOM_TYPE,
							'interval' => 3,
							'year' => [ 'month' => [5,3,1] ]
						],
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Year $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Year', $out );
		$this->assertEquals( 3, $out->get_years_between() );
		$this->assertEquals( [1,3,5 ], $out->get_months_of_year() );
		$this->assertNull( $out->get_week_of_month() );
		$this->assertNull( $out->get_day_of_week() );
	}

	/**
	 * @test
	 * it should return Year series rule if Yearly custom type and day filter is active
	 */
	public function it_should_return_year_series_rule_if_yearly_custom_type_and_day_filter_is_active() {
		$sut        = new Series_Rules_Factory();
		$recurrence = [
			'type' => Custom_Types::CUSTOM_TYPE, 'custom' => [
				'type' => Custom_Types::YEARLY_CUSTOM_TYPE, 'interval' => 3,
				'year' => [ 'month' => [ 5, 3, 1 ], 'filter' => true, 'month-number' => 2, 'month-day' => 4 ]
			],
		];
		/** @var \Tribe__Events__Pro__Date_Series_Rules__Year $out */
		$out = $sut->build_from( $recurrence );
		$this->assertInstanceOf( 'Tribe__Events__Pro__Date_Series_Rules__Year', $out );
		$this->assertEquals( 3, $out->get_years_between() );
		$this->assertEquals( [ 1, 3, 5 ], $out->get_months_of_year() );
		$this->assertEquals( 2, $out->get_week_of_month() );
		$this->assertEquals( 4, $out->get_day_of_week() );
	}
}
