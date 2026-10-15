<?php

namespace Tribe\Events\Aggregator\Tabs;

use Tribe\Events\Test\Testcases\Aggregator\V1\Aggregator_TestCase;
use Tribe\Events\Test\Traits\Aggregator\AggregatorMaker;
use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use Tribe__Events__Aggregator__Service as Service;
use Tribe__Events__Aggregator__Tabs__Scheduled as Scheduled;

class ScheduledTest extends Aggregator_TestCase {
	use RecordMaker;
	use AggregatorMaker;

	/**
	 * @var mixed
	 */
	private $service_backup;

	public function setUp() {
		parent::setUp();

		$this->service_backup = tribe( 'events-aggregator.service' );

		$service = $this->prophesize( Service::class );
		$service->api()->willReturn( true );
		$service->is_over_limit( true )->willReturn( false );
		tribe_register( 'events-aggregator.service', $service->reveal() );
	}

	public function tearDown() {
		tribe_register( 'events-aggregator.service', $this->service_backup );
		$this->restore_aggregator();

		parent::tearDown();
	}

	/**
	 * Creates a scheduled record whose child record cannot be created: its frequency is not registered.
	 *
	 * @return \Tribe__Events__Aggregator__Record__gCal
	 */
	private function make_record_that_cannot_spawn_a_child() {
		$record = $this->make_schedule_record( uniqid( 'import_id', true ) );
		$record->update_meta( 'frequency', uniqid( 'unknown-frequency-', true ) );

		return $record;
	}

	/**
	 * @test
	 */
	public function should_report_an_error_instead_of_fataling_when_the_child_record_cannot_be_created() {
		$record = $this->make_record_that_cannot_spawn_a_child();

		list( $success, $errors ) = Scheduled::instance()->action_run_import( [ $record->id ] );

		$this->assertSame( [], $success );
		$this->assertArrayHasKey( $record->id, $errors );
		$this->assertWPError( $errors[ $record->id ] );
	}

	/**
	 * @test
	 */
	public function should_mark_the_scheduled_record_as_failed_when_the_child_record_cannot_be_created() {
		$record = $this->make_record_that_cannot_spawn_a_child();

		Scheduled::instance()->action_run_import( [ $record->id ] );

		$this->assertSame(
			'error:import-failed',
			tribe( 'events-aggregator.records' )->get_by_post_id( $record->id )->meta['last_import_status']
		);
	}

	/**
	 * @test
	 */
	public function should_keep_running_the_remaining_imports_after_one_child_record_fails() {
		$failing = $this->make_record_that_cannot_spawn_a_child();
		$healthy = $this->make_schedule_record( uniqid( 'import_id', true ) );

		$service = $this->prophesize( Service::class );
		$service->api()->willReturn( true );
		$service->is_over_limit( true )->willReturn( false );
		$service->post_import( \Prophecy\Argument::type( 'array' ) )->willReturn(
			(object) [
				'message_code' => 'success:create-import',
				'data'         => (object) [ 'import_id' => uniqid( 'created-', true ) ],
				'status'       => 'created',
				'message'      => 'Created',
			]
		);
		$service->get_import( \Prophecy\Argument::cetera() )->willReturn(
			(object) [
				'status'       => 'queued',
				'message_code' => 'queued',
				'message'      => 'Queued',
				'data'         => (object) [ 'import_id' => uniqid( 'created-', true ) ],
			]
		);
		$service->get_service_message( \Prophecy\Argument::cetera() )->willReturn( 'Queued' );
		tribe_register( 'events-aggregator.service', $service->reveal() );
		tribe_register( 'events-aggregator.main', $this->make_aggregator_instance() );

		list( , $errors ) = Scheduled::instance()->action_run_import( [ $failing->id, $healthy->id ] );

		$this->assertArrayHasKey( $failing->id, $errors );
		$this->assertSame(
			'success:queued',
			tribe( 'events-aggregator.records' )->get_by_post_id( $healthy->id )->meta['last_import_status'],
			'The record after the failing one must still be run.'
		);
	}
}
