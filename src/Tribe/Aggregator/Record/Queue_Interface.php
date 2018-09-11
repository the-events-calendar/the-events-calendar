<?php

interface Tribe__Events__Aggregator__Record__Queue_Interface {

	public function activity();

	/**
	 * Shortcut to check how many items are going to be processed next
	 *
	 * @return int
	 */
	public function count();

	/**
	 * Shortcut to check if this queue is empty.
	 *
	 * @return boolean `true` if this queue instance has acquired the lock and
	 *                 the count is 0, `false` otherwise.
	 */
	public function is_empty();

	/**
	 * Processes a batch for the queue
	 *
	 * @return \Tribe__Events__Aggregator__Record__Queue
	 */
	public function process( $batch_size = null );

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @return int
	 */
	public function progress_percentage();

	/**
	 * Sets a flag to indicate that update work is in progress for a specific event:
	 * this can be useful to prevent collisions between cron-based updated and realtime
	 * updates.
	 *
	 * The flag naturally expires after an hour to allow for recovery if for instance
	 * execution hangs half way through the processing of a batch.
	 */
	public function set_in_progress_flag();

	/**
	 * Clears the in progress flag.
	 */
	public function clear_in_progress_flag();

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @return bool
	 */
	public function is_in_progress();

	/**
	 * Returns the primary post type the queue is processing
	 *
	 * @return string
	 */
	public function get_queue_type();

	/**
	 * Whether the current queue process is stuck or not.
	 *
	 * @since 4.6.21
	 *
	 * @return bool
	 */
	public function is_stuck();

	/**
	 * Orderly closes the queue process.
	 *
	 * @since 4.6.21
	 *
	 * @return bool
	 */
	public function kill_queue();

	/**
	 * Whether the current queue process failed or not.
	 *
	 * @since 4.6.21
	 *
	 * @return bool
	 */
	public function has_errors();

	/**
	 * Returns the queue error message.
	 *
	 * @since 4.6.21
	 *
	 * @return string
	 */
	public function get_error_message();
}
