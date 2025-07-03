<?php
/**
 * Status constants for the category colors migration process.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

/**
 * Class Status
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Status {
	/**
	 * Status when migration has not started.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $not_started = 'not_started';

	/**
	 * Status when preprocessing is scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $preprocessing_scheduled = 'preprocessing_scheduled';

	/**
	 * Status when validation is scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $validation_scheduled = 'validation_scheduled';

	/**
	 * Status when execution is scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $execution_scheduled = 'execution_scheduled';

	/**
	 * Status when postprocessing is scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $postprocessing_scheduled = 'postprocessing_scheduled';

	/**
	 * Generic in-progress state for the overall migration.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $in_progress = 'in_progress';

	/**
	 * Status when preprocessing is skipped.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $preprocessing_skipped = 'preprocessing_skipped';

	/**
	 * Status when preprocessing is in progress.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $preprocessing_in_progress = 'preprocessing_in_progress';

	/**
	 * Status when preprocessing has completed successfully.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $preprocessing_completed = 'preprocessing_completed';

	/**
	 * Status when preprocessing has failed.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $preprocessing_failed = 'preprocessing_failed';

	/**
	 * Status when validation is in progress.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $validation_in_progress = 'validation_in_progress';

	/**
	 * Status when validation has completed successfully.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $validation_completed = 'validation_completed';

	/**
	 * Status when validation has failed.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $validation_failed = 'validation_failed';

	/**
	 * Status when execution is in progress.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $execution_in_progress = 'execution_in_progress';

	/**
	 * Status when execution has completed successfully.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $execution_completed = 'execution_completed';

	/**
	 * Status when execution has failed.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $execution_failed = 'execution_failed';

	/**
	 * Status when execution is skipped due to no data.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $execution_skipped = 'execution_skipped';

	/**
	 * Status when postprocessing is in progress.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $postprocessing_in_progress = 'postprocessing_in_progress';

	/**
	 * Status when postprocessing has completed successfully.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $postprocessing_completed = 'postprocessing_completed';

	/**
	 * Status when postprocessing has failed.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	public static string $postprocessing_failed = 'postprocessing_failed';

	/**
	 * Get the current migration status.
	 *
	 * @since 6.14.0
	 *
	 * @return array<string, mixed> The current migration status.
	 */
	public static function get_migration_status(): array {
		$status = get_option( Config::MIGRATION_STATUS_OPTION, [] );

		return array_merge(
			[
				'status'    => self::$not_started,
				'timestamp' => '',
				'error'     => '',
			],
			$status
		);
	}

	/**
	 * Update the migration status.
	 *
	 * @since 6.14.0
	 *
	 * @param string $status    The new status.
	 * @param string $error     Optional. Error message if any.
	 * @param string $timestamp Optional. Timestamp of the status change.
	 *
	 * @return bool Whether the status was updated successfully.
	 */
	public static function update_migration_status( string $status, string $error = '', string $timestamp = '' ): bool {
		$current_status = self::get_migration_status();
		$new_status     = array_merge(
			$current_status,
			[
				'status'    => $status,
				'error'     => $error,
				'timestamp' => $timestamp ?: current_time( 'mysql' ),
			]
		);

		return update_option( Config::MIGRATION_STATUS_OPTION, $new_status );
	}

	/**
	 * Reset the migration status.
	 *
	 * @since 6.14.0
	 *
	 * @return bool Whether the status was reset successfully.
	 */
	public static function reset_migration_status(): bool {
		return delete_option( Config::MIGRATION_STATUS_OPTION );
	}
}
