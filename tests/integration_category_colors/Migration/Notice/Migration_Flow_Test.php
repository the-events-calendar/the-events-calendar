<?php

namespace TEC\Events\Category_Colors\Migration\Notice;

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Scheduler\Abstract_Action;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use WP_Error;

use function Codeception\Extension\codecept_log;

class Migration_Flow_Test extends WPTestCase {
	use With_Uopz;
	use MatchesSnapshots;

	/**
	 * @var Migration_Flow
	 */
	private Migration_Flow $flow;

	/**
	 * @before
	 */
	public function set_up(): void {
		parent::setUp();

		$this->flow = tribe( Migration_Flow::class );

		// Reset migration status before each test
		Status::update_migration_status( Status::$not_started );

		// Create a mock option for the old plugin
		update_option( 'teccc_options', [ 'some' => 'value' ] );

		// Mock is_plugin_active to return true by default
		$this->set_fn_return( 'is_plugin_active', true );
	}

	/**
	 * @after
	 */
	public function tear_down(): void {
		parent::tearDown();
		delete_option( 'teccc_options' );
		Status::update_migration_status( Status::$not_started );
	}

	/**
	 * @test
	 */
	public function should_initialize_migration_successfully(): void {
		// Mock successful scheduling
		$this->set_class_fn_return( Abstract_Action::class, 'schedule', true );

		$result = $this->flow->initialize();

		$this->assertTrue( $result );
		$status = Status::get_migration_status();
		$this->assertEquals( Status::$preprocessing_scheduled, $status['status'] );
	}

	/**
	 * @test
	 */
	public function should_handle_scheduling_error(): void {
		// Mock scheduling error
		$this->set_class_fn_return(
			Abstract_Action::class,
			'schedule',
			new WP_Error( 'test_error', 'Test error message' )
		);

		$result = $this->flow->initialize();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'migration_error', $result->get_error_code() );
		$this->assertEquals( 'Test error message', $result->get_error_message() );

		$status = Status::get_migration_status();
		$this->assertEquals( Status::$preprocessing_failed, $status['status'] );
	}

	/**
	 * @test
	 */
	public function should_handle_scheduling_failure(): void {
		// Mock scheduling failure
		$this->set_class_fn_return( Abstract_Action::class, 'schedule', false );

		$result = $this->flow->initialize();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'migration_error', $result->get_error_code() );
		$this->assertEquals( 'Failed to schedule preprocessing action', $result->get_error_message() );

		$status = Status::get_migration_status();
		$this->assertEquals( Status::$preprocessing_failed, $status['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_show_migration_when_completed(): void {
		Status::update_migration_status( Status::$postprocessing_completed );

		$this->assertFalse( $this->flow->should_show_migration() );
	}

	/**
	 * @test
	 */
	public function should_not_show_migration_when_no_old_options(): void {
		delete_option( 'teccc_options' );

		$this->assertFalse( $this->flow->should_show_migration() );
	}

	/**
	 * @test
	 */
	public function should_not_show_migration_when_old_plugin_inactive(): void {
		$this->set_fn_return( 'is_plugin_active', false );

		$this->assertFalse( $this->flow->should_show_migration() );
	}

	/**
	 * @test
	 */
	public function should_show_migration_when_all_conditions_met(): void {
		$this->assertTrue( $this->flow->should_show_migration() );
	}

	/**
	 * @test
	 */
	public function should_handle_unexpected_exception(): void {
		$this->set_class_fn_return(
			Abstract_Action::class,
			'schedule',
			function () {
				throw new \Exception( 'Unexpected error' );
			},
			true
		);
		$result = $this->flow->initialize();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertEquals( 'migration_error', $result->get_error_code() );
		$this->assertEquals( 'Unexpected error', $result->get_error_message() );

		$status = Status::get_migration_status();
		$this->assertEquals( Status::$preprocessing_failed, $status['status'] );
	}
}
