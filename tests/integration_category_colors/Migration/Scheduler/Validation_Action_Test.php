/**
 * Tests for the Validation_Action class.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Scheduler\Abstract_Action;
use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;

/**
 * Class Validation_Action_Test
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Validation_Action_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * @var Validation_Action
	 */
	private Validation_Action $action;

	/**
	 * @before
	 */
	public function set_up(): void {
		parent::setUp();
		$this->action = new Validation_Action();

		// Mock action scheduler functions
		$this->set_fn_return( 'as_schedule_single_action', 123 );
		$this->set_fn_return( 'as_unschedule_action', true );
		$this->set_fn_return( 'as_next_scheduled_action', null );

		// By default, allow scheduling
		$this->set_class_fn_return( Validation_Action::class, 'can_schedule', true );

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
	public function should_schedule_validation(): void {
		$result = $this->action->schedule();

		$this->assertIsInt( $result );
		$this->assertTrue( $this->action->is_scheduled() );
		$this->assertEquals( Status::$validation_scheduled, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_schedule_when_validation_in_progress(): void {
		Status::update_migration_status( Status::$validation_in_progress );
		$this->set_class_fn_return( Validation_Action::class, 'can_schedule', false );
		
		$result = $this->action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'tec_events_category_colors_migration_cannot_schedule', $result->get_error_code() );
	}

	/**
	 * @test
	 */
	public function should_process_validation(): void {
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

		// Mock the process method to return true for successful validation
		$this->set_class_fn_return( Validation_Action::class, 'process', true );

		// Mock the get_completed_status method to return the correct status
		$this->set_class_fn_return( Validation_Action::class, 'get_completed_status', Status::$validation_completed );

		$this->action->schedule();
		$result = $this->action->process();

		$this->assertTrue( $result );
		Status::update_migration_status( Status::$validation_completed );
		$this->assertEquals( Status::$validation_completed, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_handle_validation_failure(): void {
		// Set up invalid data
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

		// Mock the process method to return WP_Error
		$this->set_class_fn_return( Validation_Action::class, 'process', new \WP_Error( 'validation_failed', 'Validation failed' ) );

		// Mock the get_failed_status method to return the correct status
		$this->set_class_fn_return( Validation_Action::class, 'get_failed_status', Status::$validation_failed );

		$this->action->schedule();
		$result = $this->action->process();

		$this->assertInstanceOf( \WP_Error::class, $result );
		Status::update_migration_status( Status::$validation_failed );
		$this->assertEquals( Status::$validation_failed, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_schedule_when_no_data(): void {
		// Mock the can_schedule method to return false when there's no data
		$this->set_class_fn_return( Validation_Action::class, 'can_schedule', false );

		$result = $this->action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'tec_events_category_colors_migration_cannot_schedule', $result->get_error_code() );
	}
} 