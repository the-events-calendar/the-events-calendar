<?php

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Processors\Pre_Processor;
use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Config;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;

class Preprocessing_Action_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * @var Preprocessing_Action
	 */
	private Preprocessing_Action $action;

	/**
	 * @before
	 */
	public function set_up(): void {
		parent::setUp();
		$this->action = new Preprocessing_Action();

		// Mock action scheduler functions
		$this->set_fn_return('as_schedule_single_action', 123);
		$this->set_fn_return('as_unschedule_action', true);
		$this->set_fn_return('as_next_scheduled_action', null);
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();
		$this->action->cancel();
		Status::update_migration_status( Status::$not_started );
		delete_option( Config::$migration_data_option );
		delete_option( Config::$migration_processing_option );
	}

	/**
	 * @test
	 */
	public function should_schedule_preprocessing(): void {
		$result = $this->action->schedule();

		$this->assertIsInt( $result );
		$this->assertTrue( $this->action->is_scheduled() );
		$this->assertEquals( Status::$preprocessing_scheduled, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_schedule_when_preprocessing_in_progress(): void {
		Status::update_migration_status( Status::$preprocessing_in_progress );
		$result = $this->action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'tec_events_category_colors_migration_cannot_schedule', $result->get_error_code() );
	}

	/**
	 * @test
	 */
	public function should_process_preprocessing(): void {
		// Mock the preprocessor
		$this->set_class_fn_return( Pre_Processor::class, 'process', true );

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
		update_option( Config::$migration_data_option, $migration_data );

		$this->action->schedule();
		$result = $this->action->process();

		$this->assertTrue( $result );
		$this->assertEquals( Status::$preprocessing_completed, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_handle_preprocessing_failure(): void {
		// Mock the preprocessor to fail
		$this->set_class_fn_return( Pre_Processor::class, 'process', false );

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
		update_option( Config::$migration_data_option, $migration_data );

		$this->action->schedule();
		$result = $this->action->process();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( Status::$preprocessing_failed, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_schedule_when_no_data(): void {
		// Mock the can_schedule method to return false when there's no data
		$this->set_class_fn_return( Preprocessing_Action::class, 'can_schedule', false );

		$result = $this->action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'tec_events_category_colors_migration_cannot_schedule', $result->get_error_code() );
	}
}
