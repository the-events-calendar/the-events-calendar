<?php
/**
 * Handles the migration process initialization and flow control.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration\Notice;

use Exception;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Scheduler\Execution_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Postprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Preprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Validation_Action;
use TEC\Events\Category_Colors\Migration\Status;
use WP_Error;

/**
 * Class Migration_Flow
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Migration_Flow {
	/**
	 * Initialize the migration process.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function initialize() {
		try {
			// Schedule the preprocessing action.
			$preprocessing_action = tribe( Preprocessing_Action::class );
			$scheduled            = $preprocessing_action->schedule();

			if ( is_wp_error( $scheduled ) ) {
				return $this->handle_error( $scheduled->get_error_message() );
			}

			if ( false === $scheduled ) {
				return $this->handle_error( 'Failed to schedule preprocessing action' );
			}

			// Set migration status only after successful scheduling.
			Status::update_migration_status( Status::$preprocessing_scheduled );

			return true;
		} catch ( \Exception $e ) {
			return $this->handle_error( $e->getMessage() );
		}
	}

	/**
	 * Get the current migration progress.
	 *
	 * @since 6.14.0
	 *
	 * @return array{
	 *     total_categories: int,
	 *     processed_categories: int,
	 *     current_batch: int,
	 *     total_batches: int,
	 *     started_at: int,
	 *     status: string,
	 *     error_message?: string
	 * }
	 */
	public function get_progress(): array {
		$migration_data = (array) get_option( Config::MIGRATION_DATA_OPTION, [] );
		$status         = Status::get_migration_status();

		return array_merge(
			$migration_data,
			[
				'status'        => (string) $status['status'],
				'error_message' => (string) $status['error_message'] ?? null,
			]
		);
	}

	/**
	 * Cancel the migration process.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function cancel() {
		try {
			// Cancel any scheduled actions.
			$actions = [
				Preprocessing_Action::class,
				Validation_Action::class,
				Execution_Action::class,
				Postprocessing_Action::class,
			];

			foreach ( $actions as $action_class ) {
				$action = tribe( $action_class );
				$action->cancel();
			}

			// Reset migration status.
			Status::update_migration_status( Status::$not_started );

			// Clear migration data.
			delete_option( Config::MIGRATION_DATA_OPTION );

			return true;
		} catch ( Exception $e ) {
			return $this->handle_error( $e->getMessage() );
		}
	}

	/**
	 * Check if migration should be shown.
	 *
	 * @since 6.14.0
	 *
	 * @return bool Whether the migration notice should be shown.
	 */
	public function should_show_migration(): bool {
		// First check if original settings exist - if not, no migration needed.
		$old_options = get_option( Config::ORIGINAL_SETTINGS_OPTION );
		if ( empty( $old_options ) ) {
			return false;
		}

		$status = Status::get_migration_status();

		// Don't show if migration is already completed.
		if ( Status::$postprocessing_completed === $status['status'] ) {
			return false;
		}

		// If migration has started (status is past not_started), show the notice.
		if ( Status::$not_started !== $status['status'] ) {
			return true;
		}

		// For not started migrations, only show if old plugin is active.
		return is_plugin_active( 'the-events-calendar-category-colors/the-events-calendar-category-colors.php' );
	}

	/**
	 * Handle migration errors.
	 *
	 * @since 6.14.0
	 *
	 * @param string $message The error message.
	 *
	 * @return WP_Error
	 */
	protected function handle_error( string $message ): WP_Error {
		Status::update_migration_status( Status::$preprocessing_failed, $message );

		return new WP_Error( 'migration_error', $message );
	}
}
