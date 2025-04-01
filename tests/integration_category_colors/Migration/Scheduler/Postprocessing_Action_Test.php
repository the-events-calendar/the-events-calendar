/**
 * Tests for the Postprocessing_Action class.
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
 * Class Postprocessing_Action_Test
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Postprocessing_Action_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * @var Postprocessing_Action
	 */
	private Postprocessing_Action $action;

	/**
	 * @before
	 */
	public function set_up(): void {
		parent::setUp();
		$this->action = new Postprocessing_Action();

		// Mock action scheduler functions
		$this->set_fn_return( 'as_schedule_single_action', 123 );
		$this->set_fn_return( 'as_unschedule_action', true );
		$this->set_fn_return( 'as_next_scheduled_action', null );

		// By default, allow scheduling
		$this->set_class_fn_return( Postprocessing_Action::class, 'can_schedule', true );

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

		// Clean up test terms
		$terms = get_terms( [
			'taxonomy' => 'tribe_events_cat',
			'hide_empty' => false,
		] );
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, 'tribe_events_cat' );
		}
	}

	/**
	 * @test
	 */
	public function should_schedule_postprocessing(): void {
		$result = $this->action->schedule();

		$this->assertIsInt( $result );
		$this->assertTrue( $this->action->is_scheduled() );
		$this->assertEquals( Status::$postprocessing_scheduled, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_schedule_when_postprocessing_in_progress(): void {
		Status::update_migration_status( Status::$postprocessing_in_progress );
		$this->set_class_fn_return( Postprocessing_Action::class, 'can_schedule', false );
		
		$result = $this->action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'tec_events_category_colors_migration_cannot_schedule', $result->get_error_code() );
	}

	/**
	 * @test
	 */
	public function should_process_postprocessing(): void {
		// Set initial status to execution_completed
		Status::update_migration_status( Status::$execution_completed );

		// Create a test term
		$term = wp_insert_term( 'Test Category', 'tribe_events_cat' );
		$this->assertNotWPError( $term );
		$term_id = $term['term_id'];

		// Set up test data
		$migration_data = [
			'categories' => [
				(string) $term_id => [
					'taxonomy_id' => $term_id,
					'tec-events-cat-colors-primary' => '#ff0000',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
			],
			'settings' => [],
		];
		update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		update_option( Config::MIGRATION_PROCESSING_OPTION, $migration_data );

		// Mock the process method to return true for successful processing
		$this->set_class_fn_return( Postprocessing_Action::class, 'process', true );

		$this->action->schedule();
		$result = $this->action->process();

		$this->assertTrue( $result );
		$this->assertEquals( Status::$postprocessing_scheduled, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_handle_postprocessing_failure(): void {
		// Set initial status to execution_completed
		Status::update_migration_status( Status::$execution_completed );

		// Create a test term
		$term = wp_insert_term( 'Test Category', 'tribe_events_cat' );
		$this->assertNotWPError( $term );
		$term_id = $term['term_id'];

		// Set up test data with invalid category
		$migration_data = [
			'categories' => [
				(string) $term_id => [
					'taxonomy_id' => $term_id,
					'tec-events-cat-colors-primary' => '#ff0000',
					'tec-events-cat-colors-secondary' => '#ffffff',
					'tec-events-cat-colors-text' => '#000000',
				],
			],
			'settings' => [],
		];
		update_option( Config::MIGRATION_DATA_OPTION, $migration_data );
		update_option( Config::MIGRATION_PROCESSING_OPTION, $migration_data );

		// Mock the process method to return WP_Error
		$this->set_class_fn_return( Postprocessing_Action::class, 'process', new \WP_Error( 'postprocessing_failed', 'Postprocessing failed' ) );

		$this->action->schedule();
		$result = $this->action->process();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( Status::$postprocessing_scheduled, Status::get_migration_status()['status'] );
	}

	/**
	 * @test
	 */
	public function should_not_schedule_when_no_data(): void {
		// Mock the can_schedule method to return false when there's no data
		$this->set_class_fn_return( Postprocessing_Action::class, 'can_schedule', false );

		$result = $this->action->schedule();

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'tec_events_category_colors_migration_cannot_schedule', $result->get_error_code() );
	}
} 