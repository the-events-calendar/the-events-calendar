<?php
/**
 * Interface for all migration scheduler actions.
 * Defines the contract that all scheduler actions must follow.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use WP_Error;

/**
 * Interface for all migration scheduler actions.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
interface Action_Interface {

	/**
	 * Get the hook name for this action.
	 *
	 * @since 6.14.0
	 *
	 * @return string The hook name.
	 */
	public function get_hook(): string;

	/**
	 * Get the status to set when this action is scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return string The scheduled status.
	 */
	public function get_scheduled_status(): string;

	/**
	 * Get the status to set when this action is running.
	 *
	 * @since 6.14.0
	 *
	 * @return string The in-progress status.
	 */
	public function get_in_progress_status(): string;

	/**
	 * Get the status to set when this action completes successfully.
	 *
	 * @since 6.14.0
	 *
	 * @return string The completed status.
	 */
	public function get_completed_status(): string;

	/**
	 * Get the status to set when this action fails.
	 *
	 * @since 6.14.0
	 *
	 * @return string The failed status.
	 */
	public function get_failed_status(): string;

	/**
	 * Whether this action can be scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action can be scheduled.
	 */
	public function can_schedule(): bool;

	/**
	 * Schedule this action to run.
	 *
	 * @since 6.14.0
	 *
	 * @return int|WP_Error The action ID on success, WP_Error on failure.
	 */
	public function schedule();

	/**
	 * Execute the action.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function execute();

	/**
	 * Cancel this action if it's scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action was cancelled, false otherwise.
	 */
	public function cancel(): bool;

	/**
	 * Get the current action ID.
	 *
	 * @since 6.14.0
	 *
	 * @return int|null The action ID if scheduled, null otherwise.
	 */
	public function get_action_id(): ?int;

	/**
	 * Check if this action is currently scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action is scheduled.
	 */
	public function is_scheduled(): bool;

	/**
	 * Get the next scheduled time for this action.
	 *
	 * @since 6.14.0
	 *
	 * @return int|false The timestamp of the next scheduled run, or false if not scheduled.
	 */
	public function get_next_scheduled_time();
}
