<?php
/**
 * Centralizes migration status values.
 * Provides a single source of truth for migration status management.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

/**
 * Centralizes migration status values.
 * Provides a single source of truth for migration status management.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Status {

	/**
	 * Status when migration has not started.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $not_started = 'not_started';

	/**
	 * Status when migration is in progress.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $in_progress = 'in_progress';

	/**
	 * Status when preprocessing is skipped.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $preprocess_skipped = 'preprocess_skipped';


	/**
	 * Status when preprocessing has completed.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $preprocess_completed = 'preprocess_completed';

	/**
	 * Status when validation has completed successfully.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $validation_completed = 'validation_completed';

	/**
	 * Status when execution has been completed.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $execution_completed = 'execution_completed';

	/**
	 * Status when post-processing has been completed successfully.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $postprocess_completed = 'migration_completed';

	/**
	 * Status when execution has failed.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $execution_skipped = 'execution_skipped';

	/**
	 * Status when execution has failed.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $execution_failed = 'execution_failed';

	/**
	 * Status when validation is in progress.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $validation_in_progress = 'validation_in_progress';

	/**
	 * Status when validation has failed.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $validation_failed = 'validation_failed';

	/**
	 * Status when post-processing has failed.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $postprocess_failed = 'postprocessing_failed';

	/**
	 * Status when execution is in progress.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $execution_in_progress = 'execution_in_progress';
}
