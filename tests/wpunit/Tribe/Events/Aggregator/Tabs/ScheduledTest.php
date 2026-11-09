<?php

namespace Tribe\Events\Aggregator\Tabs;

use Tribe\Events\Test\Testcases\Aggregator\V1\Aggregator_TestCase;
use Tribe\Events\Test\Traits\Aggregator\AggregatorMaker;
use Tribe\Events\Test\Traits\Aggregator\RecordMaker;
use ReflectionMethod;
use RuntimeException;
use Tribe__Events__Aggregator__Record__gCal as Record;
use Tribe__Events__Aggregator__Service as Service;
use Tribe__Events__Aggregator__Tabs__Scheduled as Scheduled;
use WP_Post;

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

	/**
	 * Bulk action values the Scheduled Imports tab does not handle.
	 *
	 * @return array<string, array{0: string}> The bulk action values, keyed by case name.
	 */
	public function unsupported_bulk_action_provider(): array {
		return [
			'no action chosen' => [ '' ],
			'unknown action'   => [ 'not-a-bulk-action' ],
			'reactivate'       => [ 'reactivate' ],
		];
	}

	/**
	 * @test
	 * @dataProvider unsupported_bulk_action_provider
	 */
	public function should_do_nothing_when_the_bulk_action_is_not_supported( string $action ) {
		$records = $this->make_two_schedule_records();
		$this->intercept_redirects();

		$result = $this->submit_bulk_action( $action, $records );

		$this->assertFalse( $result );
		foreach ( $records as $record ) {
			$this->assertInstanceOf( WP_Post::class, get_post( $record->id ) );
		}
	}

	/**
	 * @test
	 */
	public function should_delete_the_selected_records_and_redirect_back() {
		$records = $this->make_two_schedule_records();
		$this->intercept_redirects();

		try {
			$this->submit_bulk_action( 'delete', $records );
			$this->fail( 'The delete bulk action should redirect back to the tab.' );
		} catch ( RuntimeException $redirect ) {
			parse_str( wp_parse_url( $redirect->getMessage(), PHP_URL_QUERY ), $query );
			$this->assertSame( 'delete', $query['action'] );
			$this->assertSame( implode( ',', wp_list_pluck( $records, 'id' ) ), $query['ids'] );
		}

		foreach ( $records as $record ) {
			$this->assertNull( get_post( $record->id ) );
		}
	}

	/**
	 * Creates two scheduled import records, so the bulk action has more than one record to handle.
	 *
	 * @return array<Record> The created records.
	 */
	private function make_two_schedule_records(): array {
		return [
			$this->make_schedule_record( 'first-scheduled-import' ),
			$this->make_schedule_record( 'second-scheduled-import' ),
		];
	}

	/**
	 * Throws on redirect, so the handler's redirect and `die` can be observed instead of ending the test.
	 */
	private function intercept_redirects(): void {
		add_filter(
			'wp_redirect',
			static function ( $location ) {
				throw new RuntimeException( $location );
			}
		);
	}

	/**
	 * Submits the Scheduled Imports bulk action form as an administrator.
	 *
	 * @param string        $action  The value of the bulk action field.
	 * @param array<Record> $records The records ticked in the list.
	 *
	 * @return mixed The value returned by the form handler.
	 */
	private function submit_bulk_action( string $action, array $records ) {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$tab         = Scheduled::instance();
		$handle_post = new ReflectionMethod( $tab, 'handle_post' );
		$handle_post->setAccessible( true );

		return $handle_post->invoke(
			$tab,
			[
				'action'  => $action,
				'nonce'   => wp_create_nonce( 'aggregator_' . $tab->get_slug() . '_request' ),
				'records' => wp_list_pluck( $records, 'id' ),
			]
		);
	}
}
