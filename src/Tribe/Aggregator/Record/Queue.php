<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Queue {
	public static $in_progress_key = 'tribe_aggregator_queue_';
	public static $queue_key = 'queue';
	public static $activity_key = 'activity';

	public $record;

	protected $fetching = false;
	protected $importer;

	/**
	 * Holds a Log of what has been done on This Queue
	 * @var Tribe__Events__Aggregator__Record__Activity
	 */
	public $activity = null;

	/**
	 * Holds the Items that will be processed
	 * @var array
	 */
	public $items = array();

	/**
	 * How many items are going to be processed
	 * @var int
	 */
	public $total = 0;

	public function __construct( $record, $items = array() ) {
		if ( is_numeric( $record ) ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $record );
		}

		if ( ! in_array( 'Tribe__Events__Aggregator__Record__Abstract', class_parents( $record ) ) ){
			return false;
		}

		// Prevent it going any further
		if ( is_wp_error( $record ) ) {
			return $record;
		}

		$this->record = $record;

		if (
			empty( $this->record->meta[ self::$activity_key ] ) ||
			! $this->record->meta[ self::$activity_key ] instanceof Tribe__Events__Aggregator__Record__Activity
		) {
			$this->activity = new Tribe__Events__Aggregator__Record__Activity();
		} else {
			$this->activity = $this->record->meta[ self::$activity_key ];
		}

		if ( ! $this->record->has_queue() ) {
			$items = $this->record->prep_import_data( $items );
		} else {
			$items = $this->record->meta[ self::$queue_key ];
		}

		// Prevent it going any further
		if ( is_wp_error( $items ) ) {
			return $items;
		}

		$this->items = $items;

		// Count the Total of items now and stores as the total
		$this->total = $this->count();
	}

	public function count() {
		return count( $this->items );
	}

	/**
	 * Saves queue data to relevant meta keys on the post
	 */
	public function save() {
		$this->record->update_meta( self::$activity_key, $this->activity );

		if ( empty( $this->items ) ) {
			$this->record->delete_meta( self::$queue_key );
		} else {
			$this->record->update_meta( self::$queue_key, $this->items );
		}

		// If we have a parent also update that
		if ( ! empty( $this->post->post_parent ) ) {
			$parent = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $this->post->post_parent );
			$activity = $parent->meta[ self::$activity_key ];

			if ( $activity instanceof Tribe__Events__Aggregator__Record__Activity ) {
				$this->activity = $activity->merge( $this->activity );
				$this->record->update_meta( self::$activity_key, $this->activity );
			}
		}

		// Updates the Modified time for the Record Log
		$args = array(
			'ID' => $this->record->post->ID,
			'post_modified' => date( Tribe__Date_Utils::DBDATETIMEFORMAT, current_time( 'timestamp' ) ),
		);

		if ( empty( $this->items ) ) {
			$args['post_status'] = Tribe__Events__Aggregator__Records::$status->success;
		}

		wp_update_post( $args );

		return $this;
	}

	/**
	 * Processes a batch for the queue
	 *
	 * @return array|WP_Error
	 */
	public function process( $batch_size = null ) {
		if ( ! $batch_size ) {
			$batch_size = apply_filters( 'tribe_aggregator_batch_size', Tribe__Events__Aggregator__Record__Queue_Processor::$batch_size );
		}
		$items = array();

		for ( $i = 0; $i < $batch_size; $i++ ) {
			if ( 0 === count( $this->items ) ) {
				break;
			}

			// Remove the Event from the Items remaining
			$items[] = array_shift( $this->items );
		}

		if ( 'csv' === $this->record->origin ) {
			$this->record->continue_import();
			$activity = get_option( 'tribe_events_import_log' );
		} else {
			$activity = $this->record->insert_posts( $items );
		}

		$this->activity = $this->activity->merge( $activity );

		return $this->save();
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
		Tribe__Post_Transient::instance()->set( $this->record->ID, self::$in_progress_key, true, HOUR_IN_SECONDS );
	}

	/**
	 * Clears the in progress flag.
	 */
	public function clear_in_progress_flag() {
		Tribe__Post_Transient::instance()->delete( $this->record->ID, self::$in_progress_key );
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		Tribe__Post_Transient::instance()->get( $this->record->ID, self::$in_progress_key );
	}

}
