<?php
/**
 * Provides shared functionality for category color migration.
 * This trait includes reusable methods for retrieving and managing migration data,
 * ensuring consistency across all migration-related classes.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

/**
 * Trait Migration_Trait
 * Contains shared methods for handling migration data, taxonomy lookups,
 * and utility functions used across multiple migration classes.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
trait Utilities {


	/**
	 * Retrieves the original settings from the database.
	 *
	 * @since TBD
	 * @return array<string, mixed> The original settings.
	 */
	public function get_original_settings(): array {
		return get_option( Config::$original_settings_option, [] );
	}

	/**
	 * Retrieves the migration data from the database.
	 *
	 * @since TBD
	 * @return array<string, mixed> The migration data.
	 */
	public function get_migration_data(): array {
		return get_option( Config::$migration_data_option, [] );
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
	 * Extracts the category ID from a category-related setting key.
	 *
	 * @since TBD
	 *
	 * @param string $key The category setting key.
	 *
	 * @return int|null The extracted category ID or null if not found.
	 */
	protected function extract_category_id( string $key ): ?int {
		$original_settings = $this->get_original_settings();

		// Check if the key exists in the terms or all_terms mapping.
		foreach ( [ 'terms', 'all_terms' ] as $term_group ) {
			if ( isset( $original_settings[ $term_group ] ) ) {
				foreach ( $original_settings[ $term_group ] as $term_id => [$slug, $name] ) {
					if ( strpos( $key, $slug ) === 0 ) {
						return (int) $term_id;
					}
				}
			}
		}

		return null;
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
	 * @since TBD
	 * @return array<string, mixed> The current migration status with timestamp.
	 */
	protected function get_migration_status(): array {
		return get_option(
			Config::$migration_status_option,
			[
				'status'    => Status::$not_started,
				'timestamp' => current_time( 'mysql' ),
			]
		);
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
		Errors::clear_errors();
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
	 * If the log level is 'error' or higher, it is also recorded in the `$errors` array.
	 *
	 * @since TBD
	 *
	 * @param string      $level   The log level (e.g., 'debug', 'info', 'warning', 'error').
	 * @param string      $message The log message.
	 * @param array       $context Additional context data (default: empty array).
	 * @param string|null $type    Optional. A label to prepend to the message (e.g., 'Migration').
	 *
	 * @return void
	 */
	protected function log_message( string $level, string $message, array $context = [], ?string $type = null ): void {
		if ( ! empty( $type ) ) {
			$message = sprintf( '[%s] %s', $type, $message );
		}

		// Store error messages separately for status checking.
		if ( in_array( strtolower( $level ), [ 'error', 'critical', 'alert', 'emergency' ], true ) ) {
			Errors::add_error( $message );
		}

		$default_context = [
			'type'    => $type,
			'process' => 'Category Colors Migration',
		];
		$context         = wp_parse_args( $default_context, $context );


		do_action( 'tribe_log', $level, $message, $context );
	}
}
