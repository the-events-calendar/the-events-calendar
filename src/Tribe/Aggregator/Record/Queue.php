<?php

class Tribe__Events__Aggregator__Record__Queue {
	public static $in_progress_key = 'tribe_aggregator_queue_';
	public static $queue_key = '_tribe_aggregator_queue';
	public $record_id;
	public $record;

	protected $total = 0;
	protected $updated = 0;
	protected $created = 0;
	protected $skipped = 0;
	protected $remaining = array();

	public function __construct( $record_id, $items = array() ) {
		$this->record_id = $record_id;
		$this->record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $this->record_id );

		if ( ! empty( $items ) ) {
			$this->remaining = $items;
			$this->total = count( $this->remaining );
			$this->save();
		} else {
			$this->load_queue();
		}
	}

	public function load_queue() {
		$queue = (array) get_post_meta( $this->record->post->ID, self::$queue_key, true );

		$this->total     = empty( $queue['total'] ) ? 0 : $queue['total'];
		$this->updated   = empty( $queue['updated'] ) ? 0 : $queue['updated'];
		$this->created   = empty( $queue['created'] ) ? 0 : $queue['created'];
		$this->skipped   = empty( $queue['skipped'] ) ? 0 : $queue['skipped'];
		$this->remaining = empty( $queue['remaining'] ) ? array() : $queue['remaining'];
	}

	public function defaults() {
		return array(
			'total' => $this->total,
			'updated' => $this->updated,
			'created' => $this->created,
			'skipped' => $this->skipped,
			'remaining' => $this->remaining,
		);
	}

	public function is_empty() {
		return empty( $this->remaining );
	}

	public function count() {
		return count( $this->remaining );
	}

	public function save() {
		if ( empty( $this->remaining ) ) {
			delete_post_meta( $this->record->post->ID, self::$queue_key );
		} else {
			$data = array(
				'total'     => $this->total,
				'updated'   => $this->updated,
				'created'   => $this->created,
				'skipped'   => $this->skipped,
				'remaining' => $this->remaining,
			);

			update_post_meta( $this->record->post->ID, self::$queue_key, $data );
		}
	}

	public function process( $batch_size = null ) {
		$items = array();

		if ( ! $batch_size ) {
			$batch_size = apply_filters( 'tribe_aggregator_batch_size', Tribe__Events__Aggregator__Record__Queue_Processor::$batch_size );
		}

		for ( $i = 0; $i < $batch_size; $i++ ) {
			if ( empty( $this->remaining ) ) {
				break;
			}

			$items[] = array_shift( $this->remaining );
		}

		$results = $this->record->insert_posts( $items );

		$updated = empty( $results['updated'] ) ? 0 : $results['updated'];
		$created = empty( $results['created'] ) ? 0 : $results['created'];
		$skipped = empty( $results['skipped'] ) ? 0 : $results['skipped'];

		$this->updated += $updated;
		$this->created += $created;
		$this->skipped += $skipped;

		$this->save();

		// return the amount of records processed
		return $updated + $created + $skipped;
	}

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @return int
	 */
	public function progress_percentage() {
		if ( 0 === $this->total ) {
			return 0;
		}

		$complete = $this->total - $this->count();
		$percent = ( $complete / $this->total ) * 100;
		return (int) $percent;
	}

	/**
	 * Sets a flag to indicate that update work is in progress for a specific event:
	 * this can be useful to prevent collisions between cron-based updated and realtime
	 * updates.
	 *
	 * The flag naturally expires after an hour to allow for recovery if for instance
	 * execution hangs half way through the processing of a batch.
	 */
	public function set_in_progress_flag() {
		set_transient( self::$in_progress_key . $this->record_id, true, HOUR_IN_SECONDS );
	}

	/**
	 * Clears the in progress flag.
	 */
	public function clear_in_progress_flag() {
		delete_transient( self::$in_progress_key . $this->record_id );
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		return (bool) get_transient( self::$in_progress_key . $this->record_id );
	}

}
