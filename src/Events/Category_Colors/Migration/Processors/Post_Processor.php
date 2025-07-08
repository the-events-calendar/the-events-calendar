<?php
/**
 * Handles post-processing validation for category color migration.
 * Ensures that all expected meta values exist and logs any inconsistencies.
 * This step is crucial to verify the success of the migration process.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */

namespace TEC\Events\Category_Colors\Migration\Processors;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Plugin_Manager;
use TEC\Events\Category_Colors\CSS\Generator;

/**
 * Class Post_Processor
 * Validates migration execution results by checking if expected metadata
 * was correctly stored in the database. Logs missing or mismatched data.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Post_Processor extends Abstract_Migration_Step {

	/**
	 * List of meta keys that should be skipped (not inserted as term meta).
	 *
	 * @since 6.14.0
	 * @var array<string>
	 */
	protected const SKIP_META_KEYS = [
		'taxonomy_id',
	];

	/**
	 * Determines whether the migration step is in a valid state to run.
	 *
	 * This method checks the current migration status and ensures the step
	 * should only execute if the migration has not already started.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the migration step can run, false otherwise.
	 */
	public function is_runnable(): bool {
		$status          = Status::get_migration_status()['status'];
		$processing_data = get_option( Config::MIGRATION_PROCESSING_OPTION, [] );

		// First check if we're in a valid state to run post-processing.
		if ( ! in_array( $status, [ Status::$execution_completed, Status::$postprocessing_failed ], true ) ) {
			$this->log_message(
				'warning',
				"Cannot run post-processing. Current status: {$status}",
				[],
				'Post Processor'
			);

			return false;
		}

		// Check if we have any processing data at all.
		if ( empty( $processing_data ) ) {
			$this->log_message(
				'error',
				'No processing data found. Migration may be in an inconsistent state.',
				[],
				'Post Processor'
			);

			return false;
		}

		// Check if we have any categories still to process.
		if ( ! empty( $processing_data['categories'] ) ) {
			$this->log_message(
				'warning',
				'Categories still exist in processing data. Execution may not be complete.',
				[ 'remaining_categories' => count( $processing_data['categories'] ) ],
				'Post Processor'
			);

			return false;
		}

		// Verify we have migration data to validate against.
		$migration_data = $this->get_migration_data();
		if ( empty( $migration_data['categories'] ) ) {
			$this->log_message(
				'error',
				'No migration data found to validate against.',
				[],
				'Post Processor'
			);

			return false;
		}

		return true;
	}

	/**
	 * Runs validation checks on migrated category meta data.
	 *
	 * @since 6.14.0
	 * @return bool
	 */
	public function process(): bool {
		$start_time = microtime( true );
		if ( $this->dry_run ) {
			$this->log_message( 'info', 'Dry run mode active. Skipping post-processing validation.', [], 'Post Processor' );
			$this->update_migration_status( Status::$postprocessing_completed );
			$this->log_elapsed_time( 'Post Processor', $start_time );

			return true;
		}

		$migration_data = $this->get_migration_data();

		if ( empty( $migration_data['categories'] ) ) {
			$this->log_message( 'warning', 'No migration data found. Cannot validate migration results.', [], 'Post Processor' );
			$this->update_migration_status( Status::$postprocessing_completed );
			$this->log_elapsed_time( 'Post Processor', $start_time );

			return false;
		}

		$validation_result = $this->validate_categories( $migration_data['categories'] );
		$this->update_migration_status( $validation_result ? Status::$postprocessing_completed : Status::$postprocessing_failed );

		// If validation passed, regenerate the CSS stylesheet.
		if ( $validation_result ) {
			$generator = tribe( Generator::class );
			$generator->generate_and_save_css();
			$this->log_message( 'info', 'Category Colors CSS stylesheet regenerated after successful migration.', [], 'Post Processor' );
		}

		// If validation passed and the plugin was previously active, deactivate it.
		if ( $validation_result && isset( $migration_data['was_plugin_active'] ) && $migration_data['was_plugin_active'] ) {
			$plugin_manager = tribe( Plugin_Manager::class );
			if ( $plugin_manager->is_old_plugin_active() ) {
				$plugin_manager->deactivate_plugin();
				$this->log_message( 'info', 'Category Colors plugin deactivated after successful migration.', [], 'Post Processor' );
			}
		}

		$this->log_elapsed_time( 'Post Processor', $start_time );

		return $validation_result;
	}

	/**
	 * Validates all categories against their expected meta data.
	 *
	 * @since 6.14.0
	 * @param array<int, array<string, mixed>> $categories The categories to validate.
	 * @return bool True if all validations pass, false otherwise.
	 */
	protected function validate_categories( array $categories ): bool {
		foreach ( $categories as $category_id => $meta_data ) {
			if ( ! $this->validate_category( $category_id, $meta_data ) ) {
				return false;
			}
		}

		$this->log_message( 'info', 'Migration verification successful. Marking migration as completed.', [], 'Post Processor' );
		return true;
	}

	/**
	 * Validates a single category's meta data.
	 *
	 * @since 6.14.0
	 * @param int   $category_id The category ID to validate.
	 * @param array $meta_data   The expected meta data for the category.
	 * @return bool True if validation passes, false otherwise.
	 */
	protected function validate_category( int $category_id, array $meta_data ): bool {
		$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );
		$actual_meta   = array_map(
			static fn( $value ) => is_array( $value ) && count( $value ) === 1 ? $value[0] : $value,
			$category_meta->get()
		);

		$meta_keys_to_validate = array_filter(
			array_keys( $meta_data ),
			fn( $key ) => ! in_array( $key, self::SKIP_META_KEYS, true )
		);

		foreach ( $meta_keys_to_validate as $meta_key ) {
			$actual_value   = $actual_meta[ $meta_key ] ?? null;
			$expected_value = $meta_data[ $meta_key ];

			if ( null === $actual_value ) {
				$this->log_message( 'error', "Missing meta key '{$meta_key}' for category ID {$category_id}.", [], 'Post Processor' );
				return false;
			}

			if ( $actual_value !== $expected_value ) {
				$this->log_mismatched_value( $category_id, $meta_key, $expected_value, $actual_value );
			}
		}

		return true;
	}

	/**
	 * Logs information about mismatched meta values.
	 *
	 * @since 6.14.0
	 * @param int    $category_id    The category ID.
	 * @param string $meta_key       The meta key that has mismatched values.
	 * @param mixed  $expected_value The expected value.
	 * @param mixed  $actual_value   The actual value.
	 * @return void
	 */
	protected function log_mismatched_value( int $category_id, string $meta_key, $expected_value, $actual_value ): void {
		$this->log_message(
			'info',
			"Found different value for '{$meta_key}' on category {$category_id}. " .
			"This is expected as we don't overwrite existing values. " .
			'Migration value: ' . wp_json_encode( $expected_value, JSON_PRETTY_PRINT ) .
			' | Existing value: ' . wp_json_encode( $actual_value, JSON_PRETTY_PRINT ),
			[],
			'Post Processor'
		);
	}
}
