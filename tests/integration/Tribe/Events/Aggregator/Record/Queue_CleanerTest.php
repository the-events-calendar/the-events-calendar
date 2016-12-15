<?php
namespace Tribe\Events\Aggregator\Record;

use Tribe__Events__Aggregator__Record__Abstract as Record;
use Tribe__Events__Aggregator__Record__Queue_Cleaner as Cleaner;
use Tribe__Events__Aggregator__Records as Records;

class Queue_CleanerTest extends \Codeception\TestCase\WPTestCase {

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
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Cleaner::class, $sut );
	}

	/**
	 * remove_duplicate_records_pending_for will return empty array if record ID is missing from meta
	 */
	public function test_remove_duplicate_records_pending_for_will_return_empty_array_if_record_id_is_missing_from_meta() {
		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$record->meta = [];

		$sut = $this->make_instance();
		$deleted = $sut->remove_duplicate_pending_records_for( $record );

		$this->assertEquals( [], $deleted );
	}

	/**
	 * remove_duplicate_records_pending_for will return empty array if there is only one record pending
	 */
	public function test_remove_duplicate_records_pending_for_will_return_empty_array_if_there_is_only_one_record_pending() {

		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$import_id = uniqid();
		$record->meta = [ 'import_id' => $import_id ];
		$pending = Records::$status->pending;
		$record->post = $this->factory()->post->create_and_get( [ 'post_type' => Records::$post_type, 'post_status' => $pending ] );
		add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );

		$sut = $this->make_instance();
		$deleted = $sut->remove_duplicate_pending_records_for( $record );

		$this->assertEquals( [], $deleted );
	}

	/**
	 * remove_duplicate_records_pending_for will return empty array if record is not pending
	 */
	public function test_remove_duplicate_records_pending_for_will_return_empty_array_if_record_is_not_pending() {
		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$import_id = uniqid();
		$record->meta = [ 'import_id' => $import_id ];
		$status = Records::$status->failed;
		$record->post = $this->factory()->post->create_and_get( [ 'post_type' => Records::$post_type, 'post_status' => $status ] );
		add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );

		$sut = $this->make_instance();
		$deleted = $sut->remove_duplicate_pending_records_for( $record );

		$this->assertEquals( [], $deleted );
	}

	/**
	 * remove_duplicate_records_pending_for will return empty array if record is schedule
	 */
	public function test_remove_duplicate_records_pending_for_will_return_empty_array_if_record_is_schedule() {
		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$import_id = uniqid();
		$record->meta = [ 'import_id' => $import_id ];
		$status = Records::$status->schedule;
		$record->post = $this->factory()->post->create_and_get( [ 'post_type' => Records::$post_type, 'post_status' => $status ] );
		add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );

		$sut = $this->make_instance();
		$deleted = $sut->remove_duplicate_pending_records_for( $record );

		$this->assertEquals( [], $deleted );
	}

	/**
	 * remove_duplicate_records_pending_for will remove duplicate pending records
	 */
	public function test_remove_duplicate_records_pending_for_will_remove_duplicate_pending_records() {
		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$import_id = uniqid();
		$record->meta = [ 'import_id' => $import_id ];
		$status = Records::$status->pending;
		$post_array = [ 'post_type' => Records::$post_type, 'post_status' => $status ];
		$record->post = $this->factory()->post->create_and_get( $post_array );
		add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );
		$more_pending = $this->factory()->post->create_many( 3, $post_array );
		foreach ( $more_pending as $id ) {
			add_post_meta( $id, '_tribe_aggregator_import_id', $import_id );
		}

		$sut = $this->make_instance();
		$deleted = $sut->remove_duplicate_pending_records_for( $record );

		$this->assertCount( 3, $deleted );
	}

	/**
	 * remove_duplicate_records_pending_for will only remove duplicate pending records
	 */
	public function test_remove_duplicate_records_pending_for_will_only_remove_duplicate_pending_records() {
		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$import_id = uniqid();
		$record->meta = [ 'import_id' => $import_id ];
		$status = Records::$status->pending;
		$post_array = [ 'post_type' => Records::$post_type, 'post_status' => $status ];
		$record->post = $this->factory()->post->create_and_get( $post_array );
		add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );
		$more_pending = $this->factory()->post->create_many( 3, $post_array );
		foreach ( $more_pending as $id ) {
			add_post_meta( $id, '_tribe_aggregator_import_id', $import_id );
		}
		$schedule_post_arr = [
			'post_type'   => Records::$post_type,
			'post_status' => Records::$status->schedule
		];
		$schedule = $this->factory()->post->create( $schedule_post_arr );
		$failed_post_arr = [
			'post_type'   => Records::$post_type,
			'post_status' => Records::$status->failed
		];
		$failed = $this->factory()->post->create_many( 4, $failed_post_arr );

		$sut = $this->make_instance();
		$deleted = $sut->remove_duplicate_pending_records_for( $record );

		$this->assertCount( 3, $deleted );
		$this->assertCount( 1, array_diff( array_merge( $more_pending, [ $record->post->ID ] ), $deleted ) );
	}

	/**
	 * maybe_fail_stalled_record will not fail record that has not been stalling for long
	 */
	public function test_maybe_fail_stalled_record_will_not_fail_record_that_has_not_been_stalling_for_long() {
		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$import_id = uniqid();
		$record->meta = [ 'import_id' => $import_id ];
		$post_array = [ 'post_type' => Records::$post_type, 'post_status' => Records::$status->pending ];
		$record->post = $this->factory()->post->create_and_get( $post_array );
		add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );

		$sut = $this->make_instance();
		$sut->set_stall_limit( HOUR_IN_SECONDS );
		$failed = $sut->maybe_fail_stalled_record( $record );

		$this->assertFalse( $failed );
		$this->assertEquals( Records::$status->pending, ( get_post( $record->post->ID ) )->post_status );
	}

	/**
	 * maybe_fail_stalled_record will fail record that has been stalling for too long
	 */
	public function test_maybe_fail_stalled_record_will_fail_record_that_has_been_stalling_for_too_long() {
		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$import_id = uniqid();
		$record->meta = [ 'import_id' => $import_id ];
		$status = Records::$status->pending;
		$hours_ago = date( 'Y-m-d H:i:s', time() - 4 * HOUR_IN_SECONDS );
		$post_array = [ 'post_type' => Records::$post_type, 'post_status' => $status, 'post_date' => $hours_ago ];
		$record->post = $this->factory()->post->create_and_get( $post_array );
		add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );
		add_post_meta( $record->post->ID, '_tribe_aggregator_queue', 'fetch' );
		\Tribe__Post_Transient::instance()->set( $record->post->ID, '_tribe_aggregator_queue', HOUR_IN_SECONDS );

		$sut = $this->make_instance();
		$sut->set_stall_limit( HOUR_IN_SECONDS );
		$failed = $sut->maybe_fail_stalled_record( $record );

		$this->assertTrue( $failed );
		$this->assertEquals( Records::$status->failed, ( get_post( $record->post->ID ) )->post_status );
		$this->assertEmpty( get_post_meta( $record->post->ID, '_tribe_aggregator_queue', true ) );
		$this->assertEmpty( get_post_meta( $record->post->ID, '_transient_tribe_aggregator_queue', true ) );
		$this->assertEmpty( get_post_meta( $record->post->ID, '_transient_timeout_tribe_aggregator_queue', true ) );
	}

	    /**
	         * maybe_fail_stalled_record will empty queue meta for failed records
	         */
	        public function test_maybe_fail_stalled_record_will_empty_queue_meta_for_failed_records()
	        {
				$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
				$import_id = uniqid();
				$record->meta = [ 'import_id' => $import_id ];
				$status = Records::$status->failed;
				$post_array = [ 'post_type' => Records::$post_type, 'post_status' => $status];
				$record->post = $this->factory()->post->create_and_get( $post_array );
				add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );
				add_post_meta( $record->post->ID, '_tribe_aggregator_queue', 'fetch' );
				\Tribe__Post_Transient::instance()->set( $record->post->ID, '_tribe_aggregator_queue', HOUR_IN_SECONDS );

				$sut = $this->make_instance();
				$sut->set_stall_limit( HOUR_IN_SECONDS );
				$failed = $sut->maybe_fail_stalled_record( $record );

				$this->assertTrue( $failed );
				$this->assertEquals( Records::$status->failed, ( get_post( $record->post->ID ) )->post_status );
				$this->assertEmpty( get_post_meta( $record->post->ID, '_tribe_aggregator_queue', true ) );
				$this->assertEmpty( get_post_meta( $record->post->ID, '_transient_tribe_aggregator_queue', true ) );
				$this->assertEmpty( get_post_meta( $record->post->ID, '_transient_timeout_tribe_aggregator_queue', true ) );
	        }

	/**
	 * maybe_fail_stalled_record will not change status of record that is not pending
	 */
	public function test_maybe_fail_stalled_record_will_not_change_status_of_record_that_is_not_pending() {
		$record = $this->getMockBuilder( Record::class )->disableOriginalConstructor()->getMock();
		$import_id = uniqid();
		$record->meta = [ 'import_id' => $import_id ];
		$status = Records::$status->schedule;
		$hours_ago = date( 'Y-m-d H:i:s', time() - 4 * HOUR_IN_SECONDS );
		$post_array = [ 'post_type' => Records::$post_type, 'post_status' => $status, 'post_date' => $hours_ago ];
		$record->post = $this->factory()->post->create_and_get( $post_array );
		add_post_meta( $record->post->ID, '_tribe_aggregator_import_id', $import_id );

		$sut = $this->make_instance();
		$sut->set_stall_limit( HOUR_IN_SECONDS );
		$failed = $sut->maybe_fail_stalled_record( $record );

		$this->assertFalse( $failed );
		$this->assertEquals( Records::$status->schedule, ( get_post( $record->post->ID ) )->post_status );
	}

	/**
	 * @return Cleaner
	 */
	private function make_instance() {
		return new Cleaner();
	}
}
