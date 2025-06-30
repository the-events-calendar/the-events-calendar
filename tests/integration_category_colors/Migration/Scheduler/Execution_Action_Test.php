<?php
/**
 * Tests for the Execution_Action class.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Processors\Worker;
use TEC\Events\Category_Colors\Migration\Scheduler\Postprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Abstract_Action;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;

/**
 * Class Execution_Action_Test
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Execution_Action_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * @var Execution_Action
	 */
	private Execution_Action $action;

	/**
	 * @before
	 */
	public function set_up(): void {
		parent::setUp();
		$this->action = new Execution_Action();

		// Mock action scheduler functions
		$this->set_fn_return( 'as_schedule_single_action', 123 );
		$this->set_fn_return( 'as_unschedule_action', true );
		$this->set_fn_return( 'as_next_scheduled_action', null );
		$this->set_fn_return( 'as_enqueue_async_action', 123 );

		// By default, allow scheduling
		$this->set_class_fn_return( Execution_Action::class, 'can_schedule', true );

		// Mock the update_migration_status method on the parent class to actually update the status
		$this->set_class_fn_return( Abstract_Action::class, 'update_migration_status', function( $status ) {
			Status::update_migration_status( $status );
			return true;
		}, true );

		// Mock the schedule method on the parent class to update status
		$action = $this->action;
		$this->set_class_fn_return( Abstract_Action::class, 'schedule', function() use ( $action ) {
			if ( ! $action->can_schedule() ) {
				return new \WP_Error(
					'tec_events_category_colors_migration_cannot_schedule',
					'Cannot schedule the action.'
				);
			}
			$status = $action->get_scheduled_status();
			Status::update_migration_status( $status );
			return 123;
		}, true );
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();
		$this->action->cancel();
		Status::update_migration_status( Status::$not_started );
		delete_option( Config::MIGRATION_DATA_OPTION );
		delete_option( Config::MIGRATION_PROCESSING_OPTION );
	}

	/**
	 * @test
	 */
	public function should_schedule_execution(): void {
		$result = $this->action->schedule();

		$this->assertIsInt( $result );
		$this->assertTrue( $this->action->is_scheduled() );
		$this->assertEquals( Status::$execution_scheduled, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_schedule_when_execution_in_progress(): void {
		Status::update_migration_status( Status::$execution_in_progress );
		$this->set_class_fn_return( Execution_Action::class, 'can_schedule', false );
		
		$result = $this->action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'tec_events_category_colors_migration_cannot_schedule', $result->get_error_code() );
	}

	/**
	 * @test
	 */
	public function should_process_execution(): void {
		// Set initial status to validation_completed
		Status::update_migration_status( Status::$validation_completed );

		// Set up test data
		$migration_data = [
			'categories' => [
				'1' => [
					'taxonomy_id' => 1,
					'tec-events-cat-colors-primary' => '#ff0000',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
			],
			'settings' => [],
		];
		update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		update_option( Config::MIGRATION_PROCESSING_OPTION, $migration_data );

		// Mock the worker to return true for successful processing
		$this->set_class_fn_return( Worker::class, 'process', true );
		$this->set_class_fn_return( Worker::class, 'get_remaining_categories', 0 );

		// Mock the postprocessing action schedule method on the parent class
		$this->set_class_fn_return( Abstract_Action::class, 'schedule', 123 );

		$this->action->schedule();
		$result = $this->action->execute();

		$this->assertTrue( $result );
		$this->assertEquals( Status::$execution_completed, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_handle_execution_failure(): void {
		// Set initial status to validation_completed
		Status::update_migration_status( Status::$validation_completed );

		// Set up test data with invalid category
		$migration_data = [
			'categories' => [
				'999999' => [ // Invalid category ID
					'taxonomy_id' => 999999,
					'tec-events-cat-colors-primary' => '#ff0000',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
			],
			'settings' => [],
		];
		update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		update_option( Config::MIGRATION_PROCESSING_OPTION, $migration_data );

		// Mock the worker to return WP_Error for failed processing
		$this->set_class_fn_return( Worker::class, 'process', new \WP_Error('execution_failed', 'Execution failed') );

		$this->action->schedule();
		$result = $this->action->execute();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( Status::$execution_scheduled, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_process_categories_in_batches(): void {
		// Set initial status to validation_completed
		Status::update_migration_status( Status::$validation_completed );

		// Set up test data with multiple categories
		$categories = [];
		for ( $i = 1; $i <= 5; $i++ ) {
			$categories[ (string) $i ] = [
				'taxonomy_id' => $i,
				'tec-events-cat-colors-primary' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
				'tec-events-cat-colors-secondary' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
				'tec-events-cat-colors-text' => sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) ),
			];
		}

		$migration_data = [
			'categories' => $categories,
			'settings' => [],
		];
		update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		update_option( Config::MIGRATION_PROCESSING_OPTION, $migration_data );

		// Mock the worker to return true for successful processing and indicate more categories to process
		$this->set_class_fn_return( Worker::class, 'process', true );
		$this->set_class_fn_return( Worker::class, 'get_remaining_categories', 3 );
		$this->set_class_fn_return( Worker::class, 'get_total_categories', 5 );

		$this->action->schedule();
		$result = $this->action->execute();

		$this->assertTrue( $result );
		$this->assertEquals( Status::$execution_scheduled, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_schedule_when_no_data(): void {
		// Mock the can_schedule method to return false when there's no data
		$this->set_class_fn_return( Execution_Action::class, 'can_schedule', false );

		$result = $this->action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'tec_events_category_colors_migration_cannot_schedule', $result->get_error_code() );
	}
} 