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
	 * Tribe__Events__Aggregator__Record__Async_Queue constructor.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @param $items
	 *
	 * @return Tribe__Process__Queue
	 */
	protected function init_queue( $items ) {
		$items_initially_not_available = empty( $items ) || ! is_array( $items );

		if ( $items_initially_not_available ) {
			$items = $this->record->prep_import_data();
		}

		$items_still_not_available = empty( $items ) || ! is_array( $items );

		if ( $items_still_not_available ) {
			return;
		}

		$transitional_id = substr( md5( uniqid( '', true ) ), 0, 8 );

		/** @var Tribe__Events__Aggregator__Record__Items $record_items */
		$record_items = tribe( 'events-aggregator.record-items' );
		$record_items->set_items( $items );
		$items = $record_items->mark_dependencies()->get_items();

		/** @var Tribe__Process__Queue $import_queue */
		$import_queue = tribe( 'events-aggregator.processes.import-events' );

		foreach ( $items as $item ) {
			$item_data = array(
				'user_id'         => get_current_user_id(),
				'record_id'       => $this->record->id,
				'data'            => $item,
				'transitional_id' => $transitional_id,
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return Tribe__Events__Aggregator__Record__Activity
	 */
	public function activity() {
		return $this->record->activity();
	}

	/**
	 * Shortcut to check if this queue is empty.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return Tribe__Events__Aggregator__Record__Async_Queue
	 */
	public function process( $batch_size = null ) {
		$this->maybe_init_queue();

		if ( ! $this->is_in_progress() ) {
			$this->record->update_meta( 'in_progress', true );
			$this->queue_process->dispatch();
		}
	}

	/**
	 * Initializes the async queue process if required.
	 *
	 * @since TBD
	 */
	protected function maybe_init_queue() {
		if ( null === $this->queue_process ) {
			$this->queue_process = new Tribe__Events__Aggregator__Processes__Import_Events();
			$this->queue_process->set_id( $this->record->meta['queue_id'] );
			$this->queue_process->set_record_id( $this->record->id );
		}
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @since TBD
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
	 * @since TBD
	 */
	public function set_in_progress_flag() {
		// no-op
	}

	/**
	 * Clears the in progress flag.
	 *
	 * No-op as the async queue has its own lock system.
	 *
	 * @since TBD
	 */
	public function clear_in_progress_flag() {
		// no-op
	}

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @since TBD
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
	 * @since TBD
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
}