<?php
namespace Tribe\Events\Pro\Recurrence;
class SchedulerTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

	protected $defaultBeforeRange = 24;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->defaultBeforeRange = \Tribe__Events__Pro__Recurrence_Scheduler::$default_before_range;
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should default before range to 24 months
	 */
	public function it_should_default_before_range_to_24_months() {
		$this->assertEquals( $this->defaultBeforeRange, ( new \Tribe__Events__Pro__Recurrence_Scheduler() )->get_before_range() );
	}

	/**
	 * @test
	 * it should default after range to 24 months
	 */
	public function it_should_default_after_range_to_24_months() {
		$this->assertEquals( $this->defaultBeforeRange, ( new \Tribe__Events__Pro__Recurrence_Scheduler() )->get_after_range() );
	}

	/**
	 * @test
	 * it should allow setting range before
	 */
	public function it_should_allow_setting_range_before() {
		$sut = new \Tribe__Events__Pro__Recurrence_Scheduler( 2 );
		$this->assertEquals( 2, $sut->get_before_range() );
	}

	/**
	 * @test
	 * it should allow setting range after
	 */
	public function it_should_allow_setting_range_after() {
		$sut = new \Tribe__Events__Pro__Recurrence_Scheduler( 24, 2 );
		$this->assertEquals( 2, $sut->get_after_range() );
	}

	public function notNumeric() {
		return array_map( function ( $val ) {
			return [ $val ];
		}, [
			'foo',
			- 123,
			array(),
			new \stdClass()
		] );
	}

	/**
	 * @test
	 * it should not allow non numeric before ranges
	 * @dataProvider notNumeric
	 */
	public function it_should_not_allow_non_numeric_before_ranges( $val ) {
		$sut = new \Tribe__Events__Pro__Recurrence_Scheduler( $val );
		$this->assertEquals( $this->defaultBeforeRange, $sut->get_before_range() );
	}

	/**
	 * @test
	 * it should not allow non numeric after ranges
	 * @dataProvider notNumeric
	 */
	public function it_should_not_allow_non_numeric_after_ranges( $val ) {
		$sut = new \Tribe__Events__Pro__Recurrence_Scheduler( $val );
		$this->assertEquals( $this->defaultBeforeRange, $sut->get_after_range() );
	}

	/**
	 * @test
	 * it should not allow setting non numeric before ranges
	 * @dataProvider notNumeric
	 */
	public function it_should_not_allow_setting_non_numeric_before_ranges( $val ) {
		$sut = new \Tribe__Events__Pro__Recurrence_Scheduler( $val );
		$sut->set_before_range( $val );
		$this->assertEquals( $this->defaultBeforeRange, $sut->get_before_range() );
	}

	/**
	 * @test
	 * it should not allow setting non numeric after ranges
	 * @dataProvider notNumeric
	 */
	public function it_should_not_allow_setting_non_numeric_after_ranges( $val ) {
		$sut = new \Tribe__Events__Pro__Recurrence_Scheduler( $val );
		$sut->set_after_range( $val );
		$this->assertEquals( $this->defaultBeforeRange, $sut->get_after_range() );
	}
}