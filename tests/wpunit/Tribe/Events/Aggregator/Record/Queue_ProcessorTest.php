<?php

namespace Tribe\Events\Aggregator\Record;

use Tribe__Events__Aggregator__Record__Abstract as Record;
use Tribe__Events__Aggregator__Record__Async_Queue as Async_Queue;
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
