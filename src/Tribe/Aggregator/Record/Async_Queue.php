<?php

class Tribe__Events__Aggregator__Record__Async_Queue
	implements Tribe__Events__Aggregator__Record__Queue_Interface {

	/**
	 * @var Tribe__Events__Aggregator__Record__Abstract
	 */
	public $record;

	/**
	 * @var Tribe__Events__Aggregator__Record__Activity
	 */
	protected $activity;

	/**
	 * @var Tribe__Process__Queue
	 */
	protected $queue_process;

	/**
	 * @var string
	 */
	protected $error;

	/**
	 * Tribe__Events__Aggregator__Record__Async_Queue constructor.
	 *
	 * @since 4.6.16
	 *
	 * @param Tribe__Events__Aggregator__Record__Abstract $record
	 * @param array $items
	 */
	public function __construct( Tribe__Events__Aggregator__Record__Abstract $record, $items = array() ) {
		$this->record = $record;

		if ( empty( $this->record->meta['queue_id'] ) ) {
			$this->queue_process = $this->init_queue( $items );
		}
	}

	/**
	 * Initializes the async queue.
	 *
	 * @since 4.6.16
	 *
	 * @param $items
	 *
	 * @return Tribe__Process__Queue|null Either a built and ready queue process or `null`
	 *                                    if the queue process was not built as not needed;
	 *                                    the latter will happen when there are no items to
	 *                                    process.
	 */ protected function init_queue( $items ) {
		$items_initially_not_available = empty( $items ) || ! is_array( $items );

		if ( $items_initially_not_available ) {
			$items = $this->record->prep_import_data();
		}

		$items_still_not_available = empty( $items ) || ! is_array( $items );

		if ( $items_still_not_available  ) {
			if ( is_array( $items ) ) {
				/**
				 * It means that there are actually no items to process.
				 * No need to go further.
				 */
				$this->record->delete_meta( 'in_progress' );
				$this->record->delete_meta( 'queue' );
				$this->record->delete_meta( 'queue_id' );
				$this->record->set_status_as_success();
			}

			return null;
		}

		$transitional_id = $this->generate_transitional_id();

		/** @var Tribe__Events__Aggregator__Record__Items $record_items */
		$record_items = tribe( 'events-aggregator.record-items' );
		$record_items->set_items( $items );
		$items = $record_items->mark_dependencies()->get_items();

		/** @var Tribe__Process__Queue $import_queue */
		$import_queue = tribe( 'events-aggregator.processes.import-events' );

		// Fetch and store the current blog ID to make sure each task knows the blog context it should happen into.
		$current_blog_id = is_multisite() ? get_current_blog_id() : 1;

		foreach ( $items as $item ) {
			$item_data           = array(
				'user_id'         => get_current_user_id(),
				'record_id'       => $this->record->id,
				'data'            => $item,
				'transitional_id' => $transitional_id,
				'blog_id'         => $current_blog_id,
			);
			$import_queue->push_to_queue( $item_data );
		}

		$import_queue->save();
		$queue_id = $import_queue->get_id();
		$this->record->update_meta( 'queue_id', $queue_id );
		$this->record->update_meta( 'queue', '1' );

		return $import_queue;
	}

	/**
	 * Magic method override.
	 *
	 * @since 4.6.16
	 *
	 * @param string $key
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	public function __get( $key ) {
		switch ( $key ) {
			case 'activity':
				return $this->activity();
				break;
		}
	}

	/**
	 * Returns the queue activity.
	 *
	 * In this implementation really stored on the record.
	 *
	 * @since 4.6.16
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	public function activity() {
		return $this->record->activity();
	}

	/**
	 * Shortcut to check if this queue is empty.
	 *
	 * @since 4.6.16
	 *
	 * @return boolean `true` if this queue instance has acquired the lock and
	 *                 the count is 0, `false` otherwise.
	 */
	public function is_empty() {
		return $this->count() === 0;
	}

	/**
	 * Shortcut to check how many items are going to be processed next
	 *
	 * @since 4.6.16
	 *
	 * @return int
	 */
	public function count() {
		$queue_status = $this->get_queue_process_status();
		$total        = (int) Tribe__Utils__Array::get( $queue_status, 'total', 0 );
		$done         = (int) Tribe__Utils__Array::get( $queue_status, 'done', 0 );

		return max( 0, $total - $done );
	}

	/**
	 * Returns the process status of the queue, read from the queue meta.
	 *
	 * @since 4.6.16
	 *
	 * @return array
	 */
	protected function get_queue_process_status() {
		$queue_status = array();

		if ( ! empty( $this->record->meta['queue_id'] ) ) {
			$queue_id     = $this->record->meta['queue_id'];
			$queue_status = Tribe__Process__Queue::get_status_of( $queue_id )->to_array();
		}

		return $queue_status;
	}

	/**
	 * Processes a batch for the queue
	 *
	 * The `batch_size` is ignored in async mode.
	 *
	 * @since 4.6.16
	 *
	 * @return Tribe__Events__Aggregator__Record__Async_Queue
	 */
	public function process( $batch_size = null ) {
		$initialized = $this->maybe_init_queue();

		if ( $initialized && ! $this->is_in_progress() ) {
			$this->record->update_meta( 'in_progress', true );
			$this->queue_process->dispatch();
		}

		return $this;
	}

	/**
	 * Initializes the async queue process if required.
	 *
	 * @since 4.6.16
	 *
	 * @return bool Whether the queue needed and was correctly initialized or not.
	 */
	protected function maybe_init_queue() {
		if ( null === $this->queue_process ) {
			$queue_id = Tribe__Utils__Array::get( $this->record->meta, 'queue_id', false );

			if ( false === $queue_id ) {
				/**
				 * If there are no items to process then no queue process will have
				 * been built.
				 * But in this case it's fine: we're done and the process should be marked
				 * as successfully completed.
				 */
				$this->record->delete_meta( 'queue' );
				$this->record->delete_meta( 'in_progress' );
				$this->record->set_status_as_success();

				return false;
			}

			$this->queue_process = new Tribe__Events__Aggregator__Processes__Import_Events();
			$this->queue_process->set_id( $queue_id );
			$this->queue_process->set_record_id( $this->record->id );

			return true;
		}

		return true;
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @since 4.6.16
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		return isset( $this->record->meta['in_progress'] );
	}

	/**
	 * Sets a flag to indicate that update work is in progress for a specific event.
	 *
	 * No-op as the async queue has its own lock system.
	 *
	 * @since 4.6.16
	 */
	public function set_in_progress_flag() {
		// no-op
	}

	/**
	 * Clears the in progress flag.
	 *
	 * No-op as the async queue has its own lock system.
	 *
	 * @since 4.6.16
	 */
	public function clear_in_progress_flag() {
		// no-op
	}

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @since 4.6.16
	 *
	 * @return int
	 */
	public function progress_percentage() {
		$queue_status = $this->get_queue_process_status();
		$total = (int) Tribe__Utils__Array::get( $queue_status, 'total', 0 );
		$done = (int) $this->record->activity()->count( Tribe__Events__Main::POSTTYPE );

		if ( 0 === $total ) {
			return 100;
		}

		return min( 100, max( 1, (int) ( 100 * ( $done / $total ) ) ) );
	}

	/**
	 * Returns the primary post type the queue is processing
	 *
	 * @since 4.6.16
	 *
	 * @return string
	 */
	public function get_queue_type() {
		$item_type = Tribe__Events__Main::POSTTYPE;

		if ( ! empty( $this->record->origin ) && 'csv' === $this->record->origin ) {
			$item_type = $this->record->meta['content_type'];
		}

		return $item_type;
	}

	/**
	 * Generates a transitional id that will be used to uniquely identify dependencies in the
	 * context of an import.
	 *
	 * @since 4.6.16
	 *
	 * @return string An 8 char long unique ID.
	 */
	protected function generate_transitional_id() {
		return substr( md5( uniqid( '', true ) ), 0, 8 );
	}

	/**
	 * Whether the current queue process is stuck or not.
	 *
	 * @since 4.6.21
	 *
	 * @return bool
	 */
	public function is_stuck() {
		if ( ! empty( $this->record->meta['queue_id'] ) ) {
			$queue_id = $this->record->meta['queue_id'];

			return Tribe__Process__Queue::is_stuck( $queue_id );
		}

		return false;
	}

	/**
	 * Orderly closes the queue process.
	 *
	 * @since 4.6.21
	 *
	 * @return bool
	 */
	public function kill_queue() {
		if ( ! $this->record ) {
			return false;
		}

		if ( ! empty( $this->record->meta['queue_id'] ) ) {
			Tribe__Process__Queue::delete_queue( $this->record->meta['queue_id'] );
		}
		$this->error = __( 'Unable to process this import - a breakage or conflict may have resulted in the import halting.', 'the-events-calendar' );

		$this->record->delete_meta( 'in_progress' );
		$this->record->delete_meta( 'queue' );
		$this->record->delete_meta( 'queue_id' );
		$this->record->set_status_as_failed( new WP_Error( 'stuck-queue', $this->error ) );

		return true;
	}

	/**
	 * Whether the current queue process failed or not.
	 *
	 * @since 4.6.21
	 *
	 * @return bool
	 */
	public function has_errors() {
		return ! empty( $this->error );
	}

	/**
	 * Returns the queue error message.
	 *
	 * @since 4.6.21
	 *
	 * @return string
	 */
	public function get_error_message() {
		return $this->error;
	}
}
