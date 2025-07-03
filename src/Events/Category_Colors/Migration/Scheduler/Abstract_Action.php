<?php
/**
 * Abstract base class for all migration scheduler actions.
 * Provides common functionality and interfaces for all migration steps.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Config;
use WP_Error;
use Exception;

/**
 * Abstract base class for all migration scheduler actions.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
abstract class Abstract_Action implements Action_Interface {

	/**
	 * The Action Scheduler action ID.
	 *
	 * @since 6.14.0
	 * @var int|null
	 */
	protected ?int $action_id = null;

	/**
	 * The hook name for this action.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	abstract public function get_hook(): string;

	/**
	 * The status to set when this action is scheduled.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	abstract public function get_scheduled_status(): string;

	/**
	 * The status to set when this action is running.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	abstract public function get_in_progress_status(): string;

	/**
	 * The status to set when this action completes successfully.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	abstract public function get_completed_status(): string;

	/**
	 * The status to set when this action fails.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	abstract public function get_failed_status(): string;

	/**
	 * Whether this action can be scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action can be scheduled.
	 */
	abstract public function can_schedule(): bool;

	/**
	 * Register the action hook.
	 *
	 * @since 6.14.0
	 */
	public function hook(): void {
		add_action( $this->get_hook(), [ $this, 'execute' ] );
	}

	/**
	 * Schedule this action to run.
	 *
	 * @since 6.14.0
	 *
	 * @return int|WP_Error|false The action ID on success, WP_Error on failure, false if prevented.
	 */
	public function schedule() {
		if ( ! $this->can_schedule() ) {
			return new WP_Error(
				'tec_events_category_colors_migration_cannot_schedule',
				'Action cannot be scheduled at this time.'
			);
		}

		/**
		 * Fires before scheduling a migration action.
		 *
		 * @since 6.14.0
		 *
		 * @param bool            $pre_schedule Whether to allow scheduling.
		 * @param Abstract_Action $action         The action being scheduled.
		 *
		 * @return bool True to allow scheduling, false to prevent it.
		 */
		$pre_schedule = apply_filters( 'tec_events_category_colors_migration_pre_schedule_action', true, $this );
		if ( false === $pre_schedule ) {
			return false;
		}

		// Unschedule any existing actions to avoid duplicates.
		as_unschedule_action( $this->get_hook(), [], Config::MIGRATION_ACTION_GROUP );

		// Schedule for immediate execution.
		$action_id = as_schedule_single_action(
			time(), // Run immediately.
			$this->get_hook(),
			[],
			Config::MIGRATION_ACTION_GROUP
		);

		if ( is_wp_error( $action_id ) ) {
			return $action_id;
		}

		$this->action_id = $action_id;
		$this->update_migration_status( $this->get_scheduled_status() );

		/**
		 * Fires after scheduling a migration action.
		 *
		 * @since 6.14.0
		 *
		 * @param Abstract_Action $action    The action that was scheduled.
		 * @param int             $action_id The ID of the scheduled action.
		 */
		do_action( 'tec_events_category_colors_migration_post_schedule_action', $this, $action_id );

		return $action_id;
	}

	/**
	 * Execute the action.
	 *
	 * @since 6.14.0
	 *
	 * @throws Exception If execution fails.
	 */
	public function execute() {
		$pre_execute = apply_filters( 'tec_events_category_colors_migration_pre_execute_action', true );
		if ( is_wp_error( $pre_execute ) ) {
			throw new Exception( $pre_execute->get_error_message(), (int) $pre_execute->get_error_code() );
		}

		$pre_execute = apply_filters( 'tec_events_category_colors_migration_' . $this->get_hook() . '_pre_execute', true );
		if ( is_wp_error( $pre_execute ) ) {
			throw new Exception( $pre_execute->get_error_message(), (int) $pre_execute->get_error_code() );
		}

		// Let the concrete action handle its own execution.
		$result = $this->process();

		// If processing was successful, schedule the next action.
		if ( true === $result ) {
			$this->schedule_next_action();
		} elseif ( is_wp_error( $result ) ) {
			throw new Exception( $result->get_error_message(), (int) $result->get_error_code() );
		}
	}

	/**
	 * Cancel this action if it's scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action was cancelled, false otherwise.
	 */
	public function cancel(): bool {
		if ( ! $this->action_id ) {
			return false;
		}

		/**
		 * Fires before cancelling a migration action.
		 *
		 * @since 6.14.0
		 *
		 * @param bool $pre_cancel Whether the action should be canceled or not. Defaults to true,
		 *                         meaning cancellation is allowed unless explicitly prevented.
		 * @param Abstract_Action $action The action being cancelled.
		 *
		 * @return bool True to allow cancellation (default), false to prevent cancellation.
		 */
		$pre_cancel = (bool) apply_filters( 'tec_events_category_colors_migration_pre_cancel_action', true, $this );
		if ( ! $pre_cancel ) {
			return false;
		}

		$cancelled       = as_unschedule_action( $this->get_hook(), [], Config::MIGRATION_ACTION_GROUP );
		$this->action_id = null;

		/**
		 * Fires after cancelling a migration action.
		 *
		 * @since 6.14.0
		 *
		 * @param Abstract_Action $action    The action that was cancelled.
		 * @param bool            $cancelled Whether the action was successfully cancelled.
		 */
		do_action( 'tec_events_category_colors_migration_post_cancel_action', $this, $cancelled );

		return $cancelled;
	}

	/**
	 * Get the current action ID.
	 *
	 * @since 6.14.0
	 *
	 * @return int|null The action ID if scheduled, null otherwise.
	 */
	public function get_action_id(): ?int {
		return $this->action_id;
	}

	/**
	 * Check if this action is currently scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action is scheduled.
	 */
	public function is_scheduled(): bool {
		return as_next_scheduled_action( $this->get_hook(), [], Config::MIGRATION_ACTION_GROUP ) !== false;
	}

	/**
	 * Get the next scheduled time for this action.
	 *
	 * @since 6.14.0
	 *
	 * @return int|false The timestamp of the next scheduled run, or false if not scheduled.
	 */
	public function get_next_scheduled_time() {
		return as_next_scheduled_action( $this->get_hook(), [], Config::MIGRATION_ACTION_GROUP );
	}

	/**
	 * Updates the migration status and triggers an action.
	 *
	 * @since 6.14.0
	 *
	 * @param string $status The new migration status.
	 *
	 * @return void
	 */
	public function update_migration_status( string $status ): void {
		update_option(
			Config::MIGRATION_STATUS_OPTION,
			[
				'status'    => $status,
				'timestamp' => current_time( 'mysql' ),
			]
		);

		$this->log_message( 'info', "Migration status updated to: {$status} at " . current_time( 'mysql' ), [], 'Migration Status Updated' );

		/**
		 * Fires when the migration status is updated.
		 *
		 * @since 6.14.0
		 *
		 * @param string $status The new migration status.
		 */
		do_action( 'tec_events_category_colors_migration_status_updated', $status );
	}

	/**
	 * Whether this action uses batching.
	 *
	 * @since 6.14.0
	 *
	 * @return int|false Number of batches if batching is used, false otherwise.
	 */
	public function get_batching(): ?int {
		return false;
	}

	/**
	 * Logs a message using the Tribe logging system.
	 *
	 * This function standardizes logging by wrapping `do_action( 'tribe_log' )`
	 * and allowing an optional type prefix (e.g., `[Migration]`).
	 * If the log level is 'error' or higher, it returns a `WP_Error` to indicate failure.
	 *
	 * @since 6.14.0
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
		$context         = wp_parse_args( $context, $default_context );

		do_action( 'tribe_log', $level, $message, $context );

		// Return WP_Error if critical.
		if ( $is_critical ) {
			return new WP_Error( 'migration_error', $message, $context );
		}

		return false;
	}

	/**
	 * Process a step.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	abstract public function process();

	/**
	 * Schedule the next action, if needed.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	abstract protected function schedule_next_action(): void;
}
