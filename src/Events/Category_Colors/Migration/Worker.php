<?php
/**
 * Executes the full category color migration process.
 * Orchestrates preprocessing, validation, execution, and post-processing steps.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Migration\Scheduler\Action_Interface;
use TEC\Events\Category_Colors\Migration\Scheduler\Execution_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Postprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Preprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Validation_Action;
use Tribe__Events__Main;
use WP_Error;

/**
 * Class Worker
 * Controls the entire migration lifecycle for category colors.
 * Runs preprocessing, validation, execution, and post-processing in sequence.
 * Ensures data integrity and logs potential issues during migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Worker extends Abstract_Migration_Step {

	/**
	 * List of meta keys that should be skipped (not inserted as term meta).
	 *
	 * @since TBD
	 * @var array<string>
	 */
	protected array $skip_meta_keys = [
		'taxonomy_id',
	];

	/**
	 * Whether to use scheduled execution.
	 *
	 * @since TBD
	 * @var bool
	 */
	protected bool $use_scheduler = false;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param bool $use_scheduler Whether to use scheduled execution.
	 */
	public function __construct( bool $use_scheduler = false ) {
		$this->use_scheduler = $use_scheduler;
	}

	/**
	 * Start the migration process.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function start_migration() {
		if ( $this->use_scheduler ) {
			return $this->start_scheduled_migration();
		}

		return $this->start_immediate_migration();
	}

	/**
	 * Start the migration process using scheduled actions.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	protected function start_scheduled_migration() {
		$preprocessing = tribe( Preprocessing_Action::class );
		return $preprocessing->schedule();
	}

	/**
	 * Start the migration process using immediate execution.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	protected function start_immediate_migration() {
		$start_time = microtime( true );

		// Run preprocessing
		$pre_processor = tribe( Pre_Processor::class );
		$result = $pre_processor->process();
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Run validation
		$validator = tribe( Validator::class );
		$result = $validator->process();
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Run execution
		$result = $this->process();
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Run post-processing
		$post_processor = tribe( Post_Processor::class );
		$result = $post_processor->process();
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$this->log_elapsed_time( 'Migration', $start_time );
		return true;
	}

	/**
	 * Cancel the migration process.
	 *
	 * @since TBD
	 *
	 * @return bool True if cancelled successfully.
	 */
	public function cancel_migration(): bool {
		if ( ! $this->use_scheduler ) {
			return false;
		}

		$actions = [
			tribe( Preprocessing_Action::class ),
			tribe( Validation_Action::class ),
			tribe( Execution_Action::class ),
			tribe( Postprocessing_Action::class ),
		];

		$cancelled = true;
		foreach ( $actions as $action ) {
			if ( ! $action->cancel() ) {
				$cancelled = false;
			}
		}

		return $cancelled;
	}

	/**
	 * Process a batch of categories.
	 *
	 * @since TBD
	 *
	 * @param array<int, array<string, mixed>> $categories The categories to process.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process_batch( array $categories ) {
		$migrated_category_meta_count = 0;
		$migrated_category_count      = count( $categories );

		foreach ( $categories as $category_id => $meta_data ) {
			try {
				$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );
				
				foreach ( $meta_data as $meta_key => $meta_value ) {
					if ( in_array( $meta_key, $this->skip_meta_keys, true ) ) {
						continue;
					}

					$existing_value = $category_meta->get( $meta_key );

					if ( ! empty( $existing_value ) ) {
						continue; // Skip if already exists.
					}
					++$migrated_category_meta_count;

					if ( $this->dry_run ) {
						$this->log_dry_run( $category_id, $meta_key, $meta_value );
					} else {
						$result = $category_meta->set( $meta_key, $meta_value );
						if ( is_wp_error( $result ) ) {
							return $this->log_message( 'error', "Failed to insert meta '{$meta_key}' for category {$category_id}.", [], 'Worker' );
						}
					}
				}

				if ( ! $this->dry_run ) {
					$category_meta->save();
				}
			} catch ( \InvalidArgumentException $e ) {
				return $this->log_message( 'error', "Failed to process category {$category_id}: {$e->getMessage()}", [], 'Worker' );
			}
		}

		$this->log_message( 'info', "Migrated {$migrated_category_meta_count} category meta values across {$migrated_category_count} categories.", [], 'Worker' );

		return true;
	}

	/**
	 * Process the execution step.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process() {
		$start_time = microtime( true );
		if ( Status::$validation_completed !== static::get_migration_status()['status'] ) {
			$this->log_message( 'info', 'Validation not completed. Running validation before execution.', [], 'Worker' );

			$error = tribe( Validator::class )->process();
			if ( is_wp_error( $error ) ) {
				$this->log_message( 'error', 'Validation failed. Migration execution stopped.', [], 'Worker' );
				$this->update_migration_status( Status::$execution_failed );

				do_action( 'tec_events_category_colors_migration_runner_end', false );
				$this->log_elapsed_time( 'Execution', $start_time );

				return false;
			}

			$this->log_message( 'info', 'Validation completed successfully. Proceeding to execution.', [], 'Worker' );
		} else {
			$this->log_message( 'info', 'Skipping validation step as it was already completed.', [], 'Worker' );
		}

		$this->update_migration_status( Status::$execution_in_progress );

		/**
		 * Fires before the migration execution begins.
		 *
		 * @since TBD
		 *
		 * @param bool $dry_run Whether the migration is running in dry-run mode.
		 */
		do_action( 'tec_events_category_colors_migration_runner_start', $this->dry_run );

		$this->dry_run_statistics(); // Log dry-run details before execution starts.

		$migration_data = $this->get_migration_data();

		if ( empty( $migration_data['categories'] ) || ! is_array( $migration_data['categories'] ) ) {
			$this->log_message( 'error', 'No categories found for migration.', [], 'Worker' );
			$this->update_migration_status( Status::$execution_skipped );

			do_action( 'tec_events_category_colors_migration_runner_end', false );
			$this->log_elapsed_time( 'Execution', $start_time );

			return false;
		}

		$this->log_existing_meta( $migration_data['categories'] );

		$error = $this->insert_category_meta( $migration_data['categories'] );
		if ( is_wp_error( $error ) ) {
			$this->log_message( 'error', 'Failed to insert category meta data.', [], 'Worker' );
			$this->update_migration_status( Status::$execution_failed );
			do_action( 'tec_events_category_colors_migration_runner_end', false );

			return false;
		}

		if ( ! empty( $migration_data['settings'] ) ) {
			$error = $this->insert_settings( $migration_data['settings'] );
			if ( is_wp_error( $error ) ) {
				$this->log_message( 'error', 'Failed to insert settings.', [], 'Worker' );
				$this->update_migration_status( Status::$execution_failed );
				do_action( 'tec_events_category_colors_migration_runner_end', false );

				return false;
			}
		}

		$this->update_migration_status( Status::$execution_completed );

		do_action( 'tec_events_category_colors_migration_runner_end', true );
		$this->log_elapsed_time( 'Execution', $start_time );

		return true;
	}

	/**
	 * Get the migration data.
	 *
	 * @since TBD
	 *
	 * @return array<string, mixed> The migration data.
	 */
	public function get_migration_data(): array {
		return get_option( Config::$migration_data_option, [] );
	}

	/**
	 * Logs what would be inserted or updated in dry-run mode.
	 *
	 * @since TBD
	 *
	 * @param int    $category_id The category ID.
	 * @param string $meta_key    The meta key.
	 * @param mixed  $value       The value to be inserted.
	 *
	 * @return void
	 */
	protected function log_dry_run( int $category_id, string $meta_key, $value ): void {
		$this->log_message( 'info', "[DRY RUN] Would insert meta key '{$meta_key}' for category {$category_id} with value: " . wp_json_encode( $value, JSON_PRETTY_PRINT ), [], 'Worker' );
	}

	/**
	 * Logs dry-run statistics, summarizing the execution plan.
	 *
	 * @since TBD
	 * @return void
	 */
	protected function dry_run_statistics(): void {
		if ( ! $this->dry_run ) {
			return;
		}

		$migration_data = $this->get_migration_data();

		$category_count = isset( $migration_data['categories'] ) ? count( $migration_data['categories'] ) : 0;
		$this->log_message( 'info', 'Dry Run Mode Active: No actual database modifications will be made.', [], 'Worker' );
		$this->log_message( 'info', "Total Categories to Process: {$category_count}", [], 'Worker' );
		$this->log_message( 'info', 'Skipped Meta Keys: ' . wp_json_encode( $this->skip_meta_keys ), [], 'Worker' );
	}

	/**
	 * Logs categories that already have the specified meta keys.
	 *
	 * @since TBD
	 *
	 * @param array<int, array<string, mixed>> $categories The categories and their meta data.
	 *
	 * @return void
	 */
	protected function log_existing_meta( array $categories ): void {
		foreach ( $categories as $category_id => $meta_data ) {
			if ( ! is_array( $meta_data ) ) {
				continue;
			}

			foreach ( $meta_data as $meta_key => $meta_value ) {
				$existing_value = get_term_meta( $category_id, $meta_key, true );

				if ( '' !== $existing_value ) {
					$this->log_message( 'warning', "Category {$category_id} already has meta key '{$meta_key}' with value: " . wp_json_encode( $existing_value, JSON_PRETTY_PRINT ), [], 'Worker' );
				}
			}
		}
	}

	/**
	 * Inserts meta values for the given categories.
	 *
	 * @since TBD
	 *
	 * @param array<int, array<string, mixed>> $categories An associative array where
	 *                                                     the key is the category ID and the value is an array of meta keys and values.
	 *
	 * @return true|WP_Error Returns WP_Error on failure.
	 */
	protected function insert_category_meta( array $categories ) {
		$migrated_category_meta_count = 0;
		$migrated_category_count      = count( $categories );

		foreach ( $categories as $category_id => $meta_data ) {
			$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );

			foreach ( $meta_data as $meta_key => $meta_value ) {
				if ( in_array( $meta_key, $this->skip_meta_keys, true ) ) {
					continue;
				}

				$existing_value = $category_meta->get( $meta_key );

				if ( ! empty( $existing_value ) ) {
					continue; // Skip if already exists.
				}
				++$migrated_category_meta_count;

				if ( $this->dry_run ) {
					$this->log_dry_run( $category_id, $meta_key, $meta_value );
				} else {
					$result = $category_meta->set( $meta_key, $meta_value );
					if ( is_wp_error( $result ) ) {
						return $this->log_message( 'error', "Failed to insert meta '{$meta_key}' for category {$category_id}.", [], 'Worker' );
					}
				}
			}

			if ( ! $this->dry_run ) {
				$category_meta->save();
			}
		}

		$this->log_message( 'info', "Migrated {$migrated_category_meta_count} category meta values across {$migrated_category_count} categories.", [], 'Worker' );

		return true;
	}

	/**
	 * Inserts the migrated settings into the `tribe_events_calendar_options` option.
	 * Ensures existing settings are not overwritten and logs changes.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $settings The settings to insert.
	 *
	 * @return true|WP_Error Returns WP_Error on failure.
	 */
	protected function insert_settings( array $settings ) {
		if ( empty( $settings ) ) {
			$this->log_message( 'warning', 'No general settings found to migrate. Skipping settings update.', [], 'Worker' );

			return true;
		}

		$existing_settings = get_option( Tribe__Events__Main::OPTIONNAME, [] );

		if ( ! is_array( $existing_settings ) ) {
			return $this->log_message( 'error', 'Existing settings are not an array. Skipping migration to prevent corruption.', [], 'Worker' );
		}

		$new_settings     = [];
		$skipped_settings = [];

		foreach ( $settings as $key => $value ) {
			if ( array_key_exists( $key, $existing_settings ) ) {
				$skipped_settings[ $key ] = $existing_settings[ $key ];
				continue;
			}

			$new_settings[ $key ] = $value;
		}

		if ( empty( $new_settings ) ) {
			$this->log_message( 'info', 'No new settings needed migration. All settings already exist.', [], 'Worker' );

			return true;
		}

		if ( $this->dry_run ) {
			foreach ( $new_settings as $key => $value ) {
				$this->log_message( 'info', "[DRY RUN] Would update `tribe_events_calendar_options`: Setting '{$key}' => " . wp_json_encode( $value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		} else {
			$result = update_option( Tribe__Events__Main::OPTIONNAME, array_merge( $existing_settings, $new_settings ) );

			if ( ! $result ) {
				return $this->log_message( 'error', 'Failed to update `tribe_events_calendar_options`.', [], 'Worker' );
			}

			foreach ( $new_settings as $key => $value ) {
				$this->log_message( 'info', "Updated `tribe_events_calendar_options`: Setting '{$key}' => " . wp_json_encode( $value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		}

		if ( ! empty( $skipped_settings ) ) {
			foreach ( $skipped_settings as $key => $existing_value ) {
				$this->log_message( 'info', "Skipped updating setting '{$key}' (already exists) => " . wp_json_encode( $existing_value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		}

		return true;
	}

	/**
	 * Determines if the migration step should run.
	 *
	 * @since TBD
	 *
	 * @return bool True if the step is ready to run, false otherwise.
	 */
	public function is_runnable(): bool {
		return true;
	}
}
