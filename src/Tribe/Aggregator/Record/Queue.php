<?php

class Tribe__Events__Aggregator__Record__Queue {
	public static $in_progress_key = 'tribe_aggregator_queue_';
	public static $queue_key = 'queue';
	public static $activity_key = 'activity_log';
	public $record_id;
	public $record;

	protected $fetching = false;
	protected $importer;
	protected $total = 0;
	protected $updated = 0;
	protected $created = 0;
	protected $skipped = 0;
	protected $remaining = array();

	public function __construct( $record_id, $items = array() ) {
		$this->record_id = $record_id;
		$this->record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $this->record_id );

		if ( ! empty( $items ) ) {
			if ( 'fetch' === $items ) {
				$this->fetching = true;
				$this->remaining = 'fetch';
			} else {
				$this->init_queue( $items );
			}

			$this->save();
		} else {
			$this->load_queue();
		}
	}

	public function init_queue( $items ) {
		if ( 'csv' === $this->record->origin ) {
			$this->record->reset_tracking_options();
			$this->importer = $items;
			$this->total = $this->importer->get_line_count();
			$this->remaining = array_fill( 0, $this->total, true );
		} else {
			$this->remaining = $items;
			$this->total = count( $this->remaining );
		}
	}

	public function load_queue() {
		$activity = empty( $this->record->meta[ self::$activity_key ] ) ? array() : $this->record->meta[ self::$activity_key ];
		$queue = empty( $this->record->meta[ self::$queue_key ] ) ? array() : $this->record->meta[ self::$queue_key ];
		$queue = (array) $queue;

		$this->total     = empty( $activity['total'] ) ? 0 : $activity['total'];
		$this->updated   = empty( $activity['updated'] ) ? 0 : $activity['updated'];
		$this->created   = empty( $activity['created'] ) ? 0 : $activity['created'];
		$this->skipped   = empty( $activity['skipped'] ) ? 0 : $activity['skipped'];
		$this->remaining = empty( $queue ) ? array() : $queue;
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

	public function total() {
		return $this->total;
	}

	public function updated() {
		return $this->updated;
	}

	public function created() {
		return $this->created;
	}

	public function skipped() {
		return $this->skipped;
	}

	public function activity() {
		return array(
			'total'     => $this->total,
			'updated'   => $this->updated,
			'created'   => $this->created,
			'skipped'   => $this->skipped,
			'remaining' => count( $this->remaining ),
		);
	}

	public function save() {
		$activity = $this->activity();

		$this->record->update_meta( self::$activity_key, $activity );

		if ( empty( $this->remaining ) ) {
			$this->record->delete_meta( self::$queue_key );
		} else {
			$this->record->update_meta( self::$queue_key, $this->remaining );
		}
	}

	public function process( $batch_size = null ) {
		if ( $this->fetching ) {
			$data = $this->record->prep_import_data();

			if ( is_wp_error( $data ) ) {
				$activity = $this->activity();
				$activity['batch_process'] = 0;
				return $activity;
			}

			$this->init_queue( $data );
			$this->save();
		}

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

		if ( 'csv' === $this->record->origin ) {
			$this->record->continue_import();
			$results = get_option( 'tribe_events_import_log' );
		} else {
			$results = $this->record->insert_posts( $items );
		}

		// grab the results from THIS batch
		$updated = empty( $results['updated'] ) ? 0 : $results['updated'];
		$created = empty( $results['created'] ) ? 0 : $results['created'];
		$skipped = empty( $results['skipped'] ) ? 0 : $results['skipped'];

		if ( 'csv' === $this->record->origin ) {
			// update the running total across all batches
			$this->updated = $updated;
			$this->created = $created;
			$this->skipped = $skipped;
		} else {
			// update the running total across all batches
			$this->updated += $updated;
			$this->created += $created;
			$this->skipped += $skipped;
		}

		$this->save();

		$activity = $this->activity();

		$activity['batch_process'] = $activity['updated'] + $activity['created'] + $activity['skipped'];

		if ( empty( $this->remaining ) ) {
			$this->record->complete_import( $activity );
		}

		return $activity;
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
