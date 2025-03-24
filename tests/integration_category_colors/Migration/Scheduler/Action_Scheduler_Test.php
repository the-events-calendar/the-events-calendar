<?php
/**
 * Tests for the Action Scheduler implementation in the Category Colors migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Handler;
use TEC\Events\Category_Colors\Migration\Status;
use Codeception\TestCase\WPTestCase;
use ActionScheduler_Store;

/**
 * Tests for the Action Scheduler implementation.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Action_Scheduler_Test extends WPTestCase {

	/**
	 * @var Preprocessing_Action
	 */
	protected $preprocessing_action;

	/**
	 * @var Validation_Action
	 */
	protected $validation_action;

	/**
	 * @var Execution_Action
	 */
	protected $execution_action;

	/**
	 * @var Postprocessing_Action
	 */
	protected $postprocessing_action;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->preprocessing_action = tribe( Preprocessing_Action::class );
		$this->validation_action = tribe( Validation_Action::class );
		$this->execution_action = tribe( Execution_Action::class );
		$this->postprocessing_action = tribe( Postprocessing_Action::class );

		// Reset migration status
		delete_option( Config::$migration_status_option );
		delete_option( Config::$migration_data_option );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		// Cancel any scheduled actions
		$this->preprocessing_action->cancel();
		$this->validation_action->cancel();
		$this->execution_action->cancel();
		$this->postprocessing_action->cancel();

		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function it_schedules_preprocessing_first(): void {
		$result = $this->preprocessing_action->schedule();

		$this->assertNotInstanceOf( \WP_Error::class, $result, 'Preprocessing action should be scheduled successfully.' );
		$this->assertTrue( $this->preprocessing_action->is_scheduled(), 'Preprocessing action should be scheduled.' );
		$this->assertSame( Status::$preprocessing_scheduled, $this->preprocessing_action->get_migration_status()['status'], 'Migration status should be set to preprocessing_scheduled.' );
	}

	/**
	 * @test
	 */
	public function it_schedules_validation_after_preprocessing(): void {
		// Set up preprocessing as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$preprocessing_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		$result = $this->validation_action->schedule();

		$this->assertNotInstanceOf( \WP_Error::class, $result, 'Validation action should be scheduled successfully.' );
		$this->assertTrue( $this->validation_action->is_scheduled(), 'Validation action should be scheduled.' );
		$this->assertSame( Status::$validation_scheduled, $this->validation_action->get_migration_status()['status'], 'Migration status should be set to validation_scheduled.' );
	}

	/**
	 * @test
	 */
	public function it_schedules_execution_after_validation(): void {
		// Set up validation as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$validation_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		// Schedule execution
		$result = $this->execution_action->schedule();
		$this->assertNotInstanceOf( \WP_Error::class, $result, 'Execution should be scheduled successfully.' );
		
		// Verify the action is scheduled with Action Scheduler
		$this->assertTrue( as_has_scheduled_action( $this->execution_action->get_hook() ), 'Action should be scheduled with Action Scheduler.' );
		
		// Verify the migration status
		$this->assertSame( Status::$execution_scheduled, $this->execution_action->get_migration_status()['status'], 'Migration status should be set to execution_scheduled.' );
	}

	/**
	 * @test
	 */
	public function it_schedules_postprocessing_after_execution(): void {
		// Set up execution as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$execution_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		$result = $this->postprocessing_action->schedule();

		$this->assertNotInstanceOf( \WP_Error::class, $result, 'Postprocessing action should be scheduled successfully.' );
		$this->assertTrue( $this->postprocessing_action->is_scheduled(), 'Postprocessing action should be scheduled.' );
		$this->assertSame( Status::$postprocessing_scheduled, $this->postprocessing_action->get_migration_status()['status'], 'Migration status should be set to postprocessing_scheduled.' );
	}

	/**
	 * @test
	 */
	public function it_cancels_all_scheduled_actions(): void {
		// Schedule preprocessing action
		$preprocessing_result = $this->preprocessing_action->schedule();
		$this->assertNotInstanceOf( \WP_Error::class, $preprocessing_result, 'Preprocessing action should be scheduled successfully.' );

		// Set up preprocessing as completed and schedule validation
		update_option( Config::$migration_status_option, [
			'status' => Status::$preprocessing_completed,
			'timestamp' => current_time( 'mysql' ),
		] );
		$validation_result = $this->validation_action->schedule();
		$this->assertNotInstanceOf( \WP_Error::class, $validation_result, 'Validation action should be scheduled successfully.' );

		// Set up validation as completed and schedule execution
		update_option( Config::$migration_status_option, [
			'status' => Status::$validation_completed,
			'timestamp' => current_time( 'mysql' ),
		] );
		$execution_result = $this->execution_action->schedule();
		$this->assertNotInstanceOf( \WP_Error::class, $execution_result, 'Execution action should be scheduled successfully.' );

		// Set up execution as completed and schedule postprocessing
		update_option( Config::$migration_status_option, [
			'status' => Status::$execution_completed,
			'timestamp' => current_time( 'mysql' ),
		] );
		$postprocessing_result = $this->postprocessing_action->schedule();
		$this->assertNotInstanceOf( \WP_Error::class, $postprocessing_result, 'Postprocessing action should be scheduled successfully.' );

		// Verify all actions are scheduled before cancellation
		$this->assertTrue( $this->preprocessing_action->is_scheduled(), 'Preprocessing action should be scheduled.' );
		$this->assertTrue( $this->validation_action->is_scheduled(), 'Validation action should be scheduled.' );
		$this->assertTrue( $this->execution_action->is_scheduled(), 'Execution action should be scheduled.' );
		$this->assertTrue( $this->postprocessing_action->is_scheduled(), 'Postprocessing action should be scheduled.' );

		// Cancel all actions
		$preprocessing_cancelled = $this->preprocessing_action->cancel();
		$validation_cancelled = $this->validation_action->cancel();
		$execution_cancelled = $this->execution_action->cancel();
		$postprocessing_cancelled = $this->postprocessing_action->cancel();

		$this->assertTrue( $preprocessing_cancelled, 'Preprocessing action should be cancelled.' );
		$this->assertTrue( $validation_cancelled, 'Validation action should be cancelled.' );
		$this->assertTrue( $execution_cancelled, 'Execution action should be cancelled.' );
		$this->assertTrue( $postprocessing_cancelled, 'Postprocessing action should be cancelled.' );

		$this->assertFalse( $this->preprocessing_action->is_scheduled(), 'Preprocessing action should not be scheduled.' );
		$this->assertFalse( $this->validation_action->is_scheduled(), 'Validation action should not be scheduled.' );
		$this->assertFalse( $this->execution_action->is_scheduled(), 'Execution action should not be scheduled.' );
		$this->assertFalse( $this->postprocessing_action->is_scheduled(), 'Postprocessing action should not be scheduled.' );
	}

	/**
	 * @test
	 */
	public function it_handles_preprocessing_skipped(): void {
		// Mock preprocessing to have no settings
		add_filter( 'tec_events_category_colors_migration_pre_execute_action', function() {
			return true; // Allow execution
		} );

		$result = $this->preprocessing_action->execute();

		$this->assertTrue( $result, 'Preprocessing should return false when no settings exist.' );
		$this->assertSame( Status::$validation_scheduled, $this->preprocessing_action->get_migration_status()['status'], 'Migration status should be set to validation_scheduled.' );
	}

	/**
	 * @test
	 */
	public function it_handles_preprocessing_failure(): void {
		// Mock preprocessing to fail with an error
		add_filter( 'tec_events_category_colors_migration_pre_execute_action', function() {
			return new \WP_Error( 'test_error', 'Test error message' );
		} );

		$result = $this->preprocessing_action->execute();

		$this->assertInstanceOf( \WP_Error::class, $result, 'Preprocessing should fail with WP_Error.' );
		$this->assertSame( Status::$preprocessing_failed, $this->preprocessing_action->get_migration_status()['status'], 'Migration status should be set to preprocessing_failed.' );
	}

	/**
	 * @test
	 */
	public function it_handles_validation_failure(): void {
		// Set up preprocessing as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$preprocessing_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		// Set up invalid migration data (missing required fields)
		update_option( Config::$migration_data_option, [
			'categories' => [], // Missing required category data
			'settings' => [],   // Missing required settings
		] );

		$result = $this->validation_action->execute();

		$this->assertInstanceOf( \WP_Error::class, $result, 'Validation should fail.' );
		$this->assertSame( Status::$validation_failed, $this->validation_action->get_migration_status()['status'], 'Migration status should be set to validation_failed.' );
	}

	/**
	 * @test
	 */
	public function it_handles_execution_failure(): void {
		// Set up validation as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$validation_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		// Mock execution to fail
		add_filter( 'tec_events_category_colors_migration_pre_execute_action', function() {
			return new \WP_Error( 'test_error', 'Test error message' );
		} );

		$result = $this->execution_action->execute();

		$this->assertInstanceOf( \WP_Error::class, $result, 'Execution should fail.' );
		$this->assertSame( Status::$execution_failed, $this->execution_action->get_migration_status()['status'], 'Migration status should be set to execution_failed.' );
	}

	/**
	 * @test
	 */
	public function it_handles_postprocessing_failure(): void {
		// Set up execution as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$execution_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		// Mock postprocessing to fail
		add_filter( 'tec_events_category_colors_migration_pre_execute_action', function() {
			return new \WP_Error( 'test_error', 'Test error message' );
		} );

		$result = $this->postprocessing_action->execute();

		$this->assertInstanceOf( \WP_Error::class, $result, 'Postprocessing should fail.' );
		$this->assertSame( Status::$postprocessing_failed, $this->postprocessing_action->get_migration_status()['status'], 'Migration status should be set to postprocessing_failed.' );
	}

	/**
	 * @test
	 */
	public function it_allows_retry_after_failure(): void {
		// Set up preprocessing as failed
		update_option( Config::$migration_status_option, [
			'status' => Status::$preprocessing_failed,
			'timestamp' => current_time( 'mysql' ),
		] );

		$result = $this->preprocessing_action->schedule();

		$this->assertNotInstanceOf( \WP_Error::class, $result, 'Preprocessing action should be rescheduled successfully.' );
		$this->assertTrue( $this->preprocessing_action->is_scheduled(), 'Preprocessing action should be scheduled.' );
		$this->assertSame( Status::$preprocessing_scheduled, $this->preprocessing_action->get_migration_status()['status'], 'Migration status should be set to preprocessing_scheduled.' );
	}

	/**
	 * @test
	 */
	public function it_prevents_scheduling_when_in_progress(): void {
		// Set up preprocessing as in progress
		update_option( Config::$migration_status_option, [
			'status' => Status::$preprocessing_in_progress,
			'timestamp' => current_time( 'mysql' ),
		] );

		$result = $this->preprocessing_action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result, 'Preprocessing action should not be scheduled when in progress.' );
		$this->assertFalse( $this->preprocessing_action->is_scheduled(), 'Preprocessing action should not be scheduled.' );
		$this->assertSame( Status::$preprocessing_in_progress, $this->preprocessing_action->get_migration_status()['status'], 'Migration status should remain as preprocessing_in_progress.' );
	}

	/**
	 * @test
	 */
	public function it_handles_batch_processing_failure(): void {
		// Set up validation as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$validation_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		// Create actual categories in the database
		$categories = [];
		for ( $i = 1; $i <= 15; $i++ ) {
			$term_id = wp_insert_term( "Test Category {$i}", 'tribe_events_cat' )['term_id'];
			$categories[ $term_id ] = [
				'taxonomy_id' => $term_id,
				'tec_category_color' => '#ff0000',
				'tec_category_text_color' => '#ffffff',
			];
		}

		update_option( Config::$migration_data_option, [
			'categories' => $categories,
			'settings' => [],
		] );

		// Execute first batch
		$result = $this->execution_action->execute();
		$this->assertTrue( $result, 'First batch should execute successfully.' );

		// Mock failure for second batch
		add_filter( 'tec_events_category_colors_migration_pre_execute_action', function() {
			return new \WP_Error( 'test_error', 'Test error message' );
		} );

		// Execute second batch (should fail)
		$result = $this->execution_action->execute();
		$this->assertInstanceOf( \WP_Error::class, $result, 'Second batch should fail.' );
		$this->assertSame( Status::$execution_failed, $this->execution_action->get_migration_status()['status'], 'Status should be set to execution_failed.' );

		// Verify batch progress is maintained
		$current_batch = get_option( Config::$migration_batch_option, 0 );
		$this->assertEquals( 0, $current_batch, 'Batch counter should remain at 0 after failure.' );

		// Clean up test categories
		foreach ( $categories as $term_id => $data ) {
			wp_delete_term( $term_id, 'tribe_events_cat' );
		}
	}

	/**
	 * @test
	 */
	public function it_handles_batch_processing(): void {
		// Set up validation as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$validation_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		// Set up test data with multiple categories
		$categories = [];
		for ( $i = 1; $i <= 250; $i++ ) { // Create 250 categories to ensure multiple batches
			$term = wp_insert_term( "Test Category {$i}", 'tribe_events_cat' );
			$this->assertNotInstanceOf( \WP_Error::class, $term, "Category {$i} should be created successfully." );
			$categories[ $term['term_id'] ] = [
				'taxonomy_id' => $term['term_id'],
				'tec_category_color' => '#ff0000',
				'tec_category_text_color' => '#ffffff',
			];
		}

		update_option( Config::$migration_data_option, [
			'categories' => $categories,
			'settings' => [],
		] );

		// Schedule first batch
		$result = $this->execution_action->schedule();
		$this->assertNotInstanceOf( \WP_Error::class, $result, 'First batch should be scheduled successfully.' );
		
		// Verify the action is scheduled
		$this->assertTrue( as_has_scheduled_action( $this->execution_action->get_hook() ), 'First batch action should be scheduled.' );
		
		// Get and execute the scheduled action
		$actions = as_get_scheduled_actions( [
			'hook' => $this->execution_action->get_hook(),
			'status' => ActionScheduler_Store::STATUS_PENDING,
		] );
		$action = reset( $actions );
		$action->execute();
		
		// Verify first batch results and next batch scheduling
		$this->assertSame( Status::$execution_scheduled, $this->execution_action->get_migration_status()['status'], 'Status should be set to execution_scheduled.' );
		$current_batch = get_option( Config::$migration_batch_option, 0 );
		$this->assertEquals( 2, $current_batch, 'Batch counter should be decremented.' );
		$this->assertTrue( as_has_scheduled_action( $this->execution_action->get_hook() ), 'Next batch should be scheduled.' );

		// Clean up test categories
		foreach ( $categories as $term_id => $data ) {
			wp_delete_term( $term_id, 'tribe_events_cat' );
		}
	}

	/**
	 * @test
	 */
	public function it_registers_action_hook(): void {
		// Verify the hook is registered
		$this->assertTrue( has_action( $this->execution_action->get_hook(), [ $this->execution_action, 'execute' ] ), 'Action hook should be registered.' );
	}

	/**
	 * @test
	 */
	public function it_respects_batch_size(): void {
		// Set up validation as completed
		update_option( Config::$migration_status_option, [
			'status' => Status::$validation_completed,
			'timestamp' => current_time( 'mysql' ),
		] );

		// Create exactly BATCH_SIZE + 1 categories
		$categories = [];
		for ( $i = 1; $i <= Execution_Action::BATCH_SIZE + 1; $i++ ) {
			$term = wp_insert_term( "Test Category {$i}", 'tribe_events_cat' );
			$this->assertNotInstanceOf( \WP_Error::class, $term, "Category {$i} should be created successfully." );
			$categories[ $term['term_id'] ] = [
				'taxonomy_id' => $term['term_id'],
				'tec_category_color' => '#ff0000',
				'tec_category_text_color' => '#ffffff',
			];
		}

		update_option( Config::$migration_data_option, [
			'categories' => $categories,
			'settings' => [],
		] );

		// Schedule first batch
		$result = $this->execution_action->schedule();
		$this->assertNotInstanceOf( \WP_Error::class, $result, 'First batch should be scheduled successfully.' );
		
		// Get and execute the scheduled action
		$actions = as_get_scheduled_actions( [
			'hook' => $this->execution_action->get_hook(),
			'status' => ActionScheduler_Store::STATUS_PENDING,
		] );
		$action = reset( $actions );
		$action->execute();
		
		// Verify we have exactly 2 batches
		$total_batches = ceil( count( $categories ) / Execution_Action::BATCH_SIZE );
		$this->assertEquals( 2, $total_batches, 'Should have exactly 2 batches.' );
		$current_batch = get_option( Config::$migration_batch_option, 0 );
		$this->assertEquals( 1, $current_batch, 'Should have 1 batch remaining.' );

		// Clean up test categories
		foreach ( $categories as $term_id => $data ) {
			wp_delete_term( $term_id, 'tribe_events_cat' );
		}
	}

	/**
	 * Determines if the migration step should run.
	 *
	 * @since TBD
	 *
	 * @return bool True if the step is ready to run, false otherwise.
	 */
	public function is_runnable(): bool {
		return true; // For testing purposes, we'll always allow the step to run
	}
} 