<?php
/**
 * Defines the contract for migration steps in the category color migration process.
 *
 * This interface ensures that all migration steps follow a standardized structure,
 * allowing them to be executed consistently within the migration workflow.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use WP_Error;

/**
 * Interface Migration_Step_Interface
 *
 * Represents a single step in the category color migration process.
 *
 * @since TBD
 */
interface Migration_Step_Interface {

	/**
	 * Executes the migration step.
	 *
	 * Each step should return `true` if it completes successfully, or `false` if it encounters an issue.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process();

	/**
	 * Determines if the migration step should run.
	 *
	 * This method checks if the step is in a valid state to execute,
	 * ensuring that prerequisite conditions are met.
	 *
	 * @since TBD
	 *
	 * @return bool True if the step is ready to run, false otherwise.
	 */
	public function is_runnable(): bool;
}
