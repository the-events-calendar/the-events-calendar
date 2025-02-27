<?php
/**
 * Provides a base class for migration steps in the category color migration process.
 *
 * This abstract class implements the `Migration_Step_Interface` and provides shared functionality
 * for all migration steps, ensuring consistency across the migration workflow.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use WP_Error;

/**
 * Class Abstract_Migration_Step
 *
 * Serves as a base class for migration steps, enforcing a consistent structure.
 * Implements `Migration_Step_Interface` and provides common methods that can be used
 * by concrete migration steps such as `Pre_Processor`, `Validator`, `Worker`, and `Post_Processor`.
 *
 * Subclasses should implement the `process()` and `is_runnable()` methods according to their specific requirements.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
abstract class Abstract_Migration_Step implements Migration_Step_Interface {

	/**
	 * Whether this step should run in dry-run mode (no actual DB modifications).
	 *
	 * @var bool
	 */
	protected bool $dry_run = false;

	/**
	 * Retrieves the original settings from the database.
	 *
	 * @since TBD
	 * @return array<string, mixed> The original settings.
	 */
	public function get_original_settings(): array {
		return (array) get_option( Config::$original_settings_option, [] );
	}

	/**
	 * Retrieves the migration data from the database.
	 *
	 * @since TBD
	 * @return array<string, mixed> The migration data.
	 */
	public function get_migration_data(): array {
		return (array) get_option( Config::$migration_data_option, [] );
	}

	/**
	 * Stores the migration data in the database.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $data The processed migration data to store.
	 *
	 * @return void
	 */
	public function update_migration_data( array $data ): void {
		update_option( Config::$migration_data_option, $data, false );
	}

	/**
	 * Retrieves the mapped meta key, or null if it is not recognized.
	 *
	 * @since TBD
	 *
	 * @param string $key The original meta key.
	 *
	 * @return string|null The mapped meta key, or null if it should be ignored.
	 */
	protected function get_mapped_meta_key( string $key ): ?string {
		return Config::$meta_key_map[ $key ] ?? null;
	}

	/**
	 * Gets the current migration status.
	 *
	 * Uses `wp_parse_args()` to merge the retrieved status with the default structure,
	 * ensuring required keys (`status` and `timestamp`) are always present.
	 *
	 * @since TBD
	 *
	 * @return array<string, mixed> The current migration status with a timestamp.
	 */
	public static function get_migration_status(): array {
		$default_status = [
			'status'    => Status::$not_started,
			'timestamp' => current_time( 'mysql' ),
		];

		$stored_status = get_option( Config::$migration_status_option, [] );

		return wp_parse_args( $stored_status, $default_status );
	}

	/**
	 * Updates the migration status and triggers an action.
	 *
	 * @since TBD
	 *
	 * @param string $status The new migration status.
	 *
	 * @return void
	 */
	protected function update_migration_status( string $status ): void {
		update_option(
			Config::$migration_status_option,
			[
				'status'    => $status,
				'timestamp' => current_time( 'mysql' ),
			]
		);

		$this->log_message( 'info', "Migration status updated to: {$status} at " . current_time( 'mysql' ), [], 'Migration Status Updated' );

		/**
		 * Fires when the migration status is updated.
		 *
		 * @since TBD
		 *
		 * @param string $status The new migration status.
		 */
		do_action( 'tec_events_category_colors_migration_status_updated', $status );
	}

	/**
	 * Resets the migration process, clearing stored migration data and resetting the status.
	 *
	 * @since TBD
	 * @return void
	 */
	public function reset_migration(): void {
		// Delete migration data and reset status.
		delete_option( Config::$migration_data_option );
		$this->update_migration_status( Status::$not_started );

		$this->log_message( 'info', 'Migration has been reset to the initial state.', [], 'Migration Status Updated' );

		/**
		 * Fires when the migration is reset.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_category_colors_migration_reset' );
	}

	/**
	 * Logs the elapsed time for a migration step.
	 *
	 * @since TBD
	 *
	 * @param string $step       The name of the step (e.g., "Preprocessing", "Validation").
	 * @param float  $start_time The timestamp when the step started.
	 */
	protected function log_elapsed_time( string $step, float $start_time ): void {
		$elapsed_time = number_format( microtime( true ) - $start_time, 3 );
		$this->log_message( 'info', sprintf( '%s: duration: %s seconds.', $step, $elapsed_time ), [], 'Statistics' );
	}

	/**
	 * Logs a message using the Tribe logging system.
	 *
	 * This function standardizes logging by wrapping `do_action( 'tribe_log' )`
	 * and allowing an optional type prefix (e.g., `[Migration]`).
	 * If the log level is 'error' or higher, it returns a `WP_Error` to indicate failure.
	 *
	 * @since TBD
	 *
	 * @param string      $level   The log level (e.g., 'debug', 'info', 'warning', 'error').
	 * @param string      $message The log message.
	 * @param array       $context Additional context data (default: empty array).
	 * @param string|null $type    Optional. A label to prepend to the message (e.g., 'Migration').
	 *
	 * @return bool|WP_Error Returns `WP_Error` if the log level is 'error' or higher.
	 */
	protected function log_message( string $level, string $message, array $context = [], ?string $type = null ) {
		if ( ! empty( $type ) ) {
			$message = sprintf( '[%s] %s', $type, $message );
		}

		// Define critical levels that should trigger WP_Error.
		$critical_levels = [ 'error', 'critical', 'alert', 'emergency' ];
		$is_critical     = in_array( strtolower( $level ), $critical_levels, true );

		// Prepare logging context.
		$default_context = [
			'type'    => $type,
			'process' => 'Category Colors Migration',
		];
		$context         = wp_parse_args( $default_context, $context );

		do_action( 'tribe_log', $level, $message, $context );

		// Return WP_Error if critical.
		if ( $is_critical ) {
			return new WP_Error( 'migration_error', $message, $context );
		}

		return false;
	}

	/**
	 * Enables or disables Dry Run mode for this migration step.
	 *
	 * @param bool $dry_run Whether this step should be in dry-run mode.
	 *
	 * @return self
	 */
	public function set_dry_run( bool $dry_run = false ): self {
		$this->dry_run = $dry_run;

		return $this;
	}
}
