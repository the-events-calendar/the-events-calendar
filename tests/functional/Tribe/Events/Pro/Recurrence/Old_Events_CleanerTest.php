<?php
namespace Tribe\Events\Pro\Recurrence;
class Old_Events_CleanerTest extends \WP_UnitTestCase {

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
	 * it should default scheduler if not injected
	 */
	public function it_should_default_scheduler_if_not_injected() {
		$sut = new \Tribe__Events__Pro__Recurrence__Old_Events_Cleaner();
		$this->assertInstanceOf( 'Tribe__Events__Pro__Recurrence_Scheduler', $sut->get_scheduler() );
	}

	/**
	 * @test
	 * it should set teh before range on the scheduler
	 */
	public function it_should_set_the_before_range_on_the_scheduler() {
		$scheduler = $this->getMock( 'Tribe__Events__Pro__Recurrence_Scheduler' );
		$scheduler->expects( $this->once() )->method( 'set_before_range' )->with( 12 );
		$sut = new \Tribe__Events__Pro__Recurrence__Old_Events_Cleaner( $scheduler );

		$sut->clean_up_old_recurring_events( [ 'recurrenceMaxMonthsBefore' => 24 ], [ 'recurrenceMaxMonthsBefore' => 12 ] );
	}

	/**
	 * @test
	 * it should call the cleanup method on the scheduler if new value smaller than old value
	 */
	public function it_should_call_the_cleanup_method_on_the_scheduler_if_new_value_smaller_than_old_value() {
		$scheduler = $this->getMock( 'Tribe__Events__Pro__Recurrence_Scheduler' );
		$scheduler->expects( $this->once() )->method( 'clean_up_old_recurring_events' );
		$sut = new \Tribe__Events__Pro__Recurrence__Old_Events_Cleaner( $scheduler );

		$sut->clean_up_old_recurring_events( [ 'recurrenceMaxMonthsBefore' => 24 ], [ 'recurrenceMaxMonthsBefore' => 12 ] );
	}


	/**
	 * @test
	 * it should not call cleanup method on the scheduler if new value bigger than old value
	 */
	public function it_should_not_call_cleanup_method_on_the_scheduler_if_new_value_bigger_than_old_value() {
		$scheduler = $this->getMock( 'Tribe__Events__Pro__Recurrence_Scheduler' );
		$scheduler->expects( $this->never() )->method( 'clean_up_old_recurring_events' );
		$sut = new \Tribe__Events__Pro__Recurrence__Old_Events_Cleaner( $scheduler );

		$sut->clean_up_old_recurring_events( [ 'recurrenceMaxMonthsBefore' => 12 ], [ 'recurrenceMaxMonthsBefore' => 24 ] );
	}
}