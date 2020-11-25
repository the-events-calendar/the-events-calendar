<?php

namespace Tribe\Events\Aggregator\Record;

use Tribe__Events__Aggregator__Record__Abstract as Record;
use Tribe__Events__Aggregator__Record__Async_Queue as Async_Queue;
use Tribe__Events__Aggregator__Record__Queue;
use Tribe__Events__Aggregator__Record__Queue_Processor as Processor;
use Tribe__Events__Aggregator__Record__Void_Queue as Void_Queue;

class Queue_ProcessorTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * It should return an Async_Queue instance when passing valid record
	 *
	 * @test
	 */
	public function should_return_an_async_queue_instance_when_passing_valid_record() {
		$record = $this->prophesize( Record::class );

		$queue = Processor::build_queue( $record->reveal(), [ 'foo' => 'bar' ], false );

		$this->assertInstanceOf( Async_Queue::class, $queue );
	}

	/**
	 * should return async queue if async queue is enabled
	 *
	 * @test
	 */
	 public function should_return_async_queue_if_async_queue_is_enabled() {
		$record = $this->prophesize( Record::class );

		add_filter(
			'tribe_get_option',
			function ( $value, $key ) {
				if ( $key === 'tribe_aggregator_import_process_system' ) {
					return 'async';
				}

				return $value;
			},
			10,
			2
		);

		$queue = Processor::build_queue( $record->reveal(), [ 'foo' => 'bar' ], false );

		$this->assertInstanceOf( Async_Queue::class, $queue );
	}

	/**
	 * should return cron system if batch import is not enabled
	 *
	 * @test
	 */
	 public function should_return_cron_system_if_batch_import_is_not_enabled() {
		 add_filter(
			 'tribe_get_option',
			 function ( $value, $key ) {
				 if ( $key === 'tribe_aggregator_import_process_system' ) {
					 return 'cron';
				 }

				 return $value;
			 },
			 10,
			 2
		 );

		 $record = $this->prophesize( Record::class );
		 $reveal = $record->reveal();
		 $reveal->post = get_post(wp_insert_post([
			 'post_title'   => 'Test post',
			 'post_content' => 'Test post content',
			 'post_status'  => 'publish',
		 ]));
		 $queue = Processor::build_queue( $reveal, [ 'foo' => 'bar' ], false );

		 $this->assertInstanceOf( Tribe__Events__Aggregator__Record__Queue::class, $queue );
	 }

	/**
	 * should return a batch queue if legacy system is enabled
	 *
	 * @test
	 */
	 public function should_return_a_batch_queue_if_legacy_system_is_enabled() {
		 add_filter(
			 'tribe_get_option',
			 function ( $value, $key ) {
				 if ( $key === 'tribe_aggregator_import_process_system' ) {
					 return 'cron';
				 }

				 return $value;
			 },
			 10,
			 2
		 );
		 $record = $this->prophesize( Record::class );
		 $reveal = $record->reveal();
		 $reveal->meta['allow_batch_push'] = true;
		 $reveal->post = get_post(wp_insert_post([
			 'post_title'   => 'Test post',
			 'post_content' => 'Test post content',
			 'post_status'  => 'publish',
		 ]));

		 $queue = Processor::build_queue( $reveal, [ 'foo' => 'bar' ], false );

		 $this->assertInstanceOf( Batch_Queue::class, $queue );
	 }

	/**
	 * It should return a void queue when the passed record ID is invalid
	 *
	 * @test
	 */
	public function should_return_a_void_queue_when_the_passed_record_id_is_invalid() {
		$record = 23;
		$items  = [ 'foo' => 'bar' ];
		$queue  = Processor::build_queue( $record, $items, false );

		$this->assertNotInstanceOf( Async_Queue::class, $queue );
		$this->assertInstanceOf( Void_Queue::class, $queue );
	}
}
