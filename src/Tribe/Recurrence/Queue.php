<?php
/**
 * Maintains a queue of recurring event instances still to be processed.
 *
 * Each queue is made up of two lists - event instances to be created and event
 * instances to be deleted.
 */
class Tribe__Events__Pro__Recurrence__Queue {
	const EVENT_QUEUE = '_TribeEventsPRO_RecurrenceQueue';
	const IN_PROGRESS = 'tribe_events_pro_processing_batch_';

	const CREATE    = 'create';
	const UPDATE    = 'update';
	const DELETE    = 'delete';
	const EXCLUDE   = 'exclude';
	const JOB_TOTAL = 'original';

	/** @var WP_Post */
	protected $event;
	protected $to_create  = array();
	protected $to_update  = array();
	protected $to_delete  = array();
	protected $to_exclude = array();
	protected $job_total  = 0;


	/**
	 * @param  int $event_id
	 * @throws Exception
	 */
	public function __construct( $event_id ) {
		if ( $this->get_event( $event_id ) ) {
			$this->load_queue();
		}
	}

	/**
	 * Indicates if the queue is empty.
	 *
	 * @return bool
	 */
	public function is_empty() {
		return ( empty( $this->to_create ) && empty( $this->to_update ) && empty( $this->to_delete ) );
	}

	/**
	 * @param  int  $event_id
	 * @return bool true on successful load
	 */
	protected function get_event( $event_id ) {
		$this->event = get_post( $event_id );

		if ( null === $this->event ) {
			return false;
		}

		if ( $this->event->post_parent > 0 ) {
			$this->event = get_post( $this->event->post_parent );
		}

		return ( null !== $this->event );
	}

	/**
	 * Loads the queue for the current event.
	 */
	protected function load_queue() {
		$queue = (array) get_post_meta( $this->event->ID, self::EVENT_QUEUE, true );

		$this->to_create  = isset( $queue[ self::CREATE ] ) ? (array) $queue[ self::CREATE ] : array();
		$this->to_update  = isset( $queue[ self::UPDATE ] ) ? (array) $queue[ self::UPDATE ] : array();
		$this->to_delete  = isset( $queue[ self::DELETE ] ) ? (array) $queue[ self::DELETE ] : array();
		$this->to_exclude = isset( $queue[ self::EXCLUDE ] ) ? (array) $queue[ self::EXCLUDE ] : array();
		$this->job_total  = isset( $queue[ self::JOB_TOTAL ] ) ? (int) $queue[ self::JOB_TOTAL ] : -1;
	}

	/**
	 * Total number of instances to be created, updated or deleted.
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->to_create ) + count( $this->to_update ) + count( $this->to_delete );
	}

	/**
	 * Update the current queue.
	 *
	 * @param  array $to_create
	 * @param  array $to_update
	 * @param  array $to_delete
	 * @param  array $to_exclude
	 *
	 * @return bool
	 */
	public function update( array $to_create, array $to_update, array $to_delete, array $to_exclude ) {
		$this->instances_to_create( $to_create );
		$this->instances_to_update( $to_update );
		$this->instances_to_delete( $to_delete );
		$this->instances_to_exclude( $to_exclude );
		$this->job_total = $this->count();

		$this->save();
	}

	/**
	 * Gets or sets an array of recurring instance dates that need to be created.
	 *
	 * Note that when setting this list any pre-enqueued instance dates will be
	 * overwritten, therefore for use cases where the dates need to be appended this
	 * must be managed manually by first obtaining the existing list.
	 *
	 * @param  array $dates
	 * @return mixed array|null
	 */
	public function instances_to_create( array $dates = null ) {
		if ( null === $dates ) {
			return $this->to_create;
		}

		$this->to_create = $dates;
	}

	/**
	 * Gets or sets an array of recurring instance dates that need to be updated.
	 *
	 * Note that when setting this list any pre-enqueued instance dates will be
	 * overwritten, therefore for use cases where the dates need to be appended this
	 * must be managed manually by first obtaining the existing list.
	 *
	 * @param  array $dates
	 * @return mixed array|null
	 */
	public function instances_to_update( array $dates = null ) {
		if ( null === $dates ) {
			return $this->to_update;
		}

		$this->to_update = $dates;
	}

	/**
	 * Gets or sets an array of recurring instance dates that need to be deleted.
	 *
	 * Note that when setting this list any pre-enqueued instance dates will be
	 * overwritten, therefore for use cases where the dates need to be appended this
	 * must be managed manually by first obtaining the existing list.
	 *
	 * @param  array $dates
	 * @return mixed array|null
	 */
	public function instances_to_delete( array $dates = null ) {
		if ( null === $dates ) {
			return $this->to_delete;
		}

		$this->to_delete = $dates;
	}

	/**
	 * Gets or sets an array of recurring instance dates that should be excluded from
	 * processing.
	 *
	 * Note that when setting this list any pre-enqueued instance dates will be
	 * overwritten, therefore for use cases where the dates need to be appended this
	 * must be managed manually by first obtaining the existing list.
	 *
	 * @param  array $dates
	 * @return mixed array|null
	 */
	public function instances_to_exclude( array $dates = null ) {
		if ( null === $dates ) {
			return $this->to_exclude;
		}

		$this->to_exclude = $dates;
	}

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @return int
	 */
	public function progress_percentage() {
		// If for some reason the job total is zero, return zero to avoid errors
		if ( 0 == $this->job_total ) {
			return 0;
		}

		$complete = $this->job_total - $this->count();
		$percent  = ( $complete / $this->job_total ) * 100;
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
		set_transient( self::IN_PROGRESS . $this->event->ID, true, HOUR_IN_SECONDS );
	}

	/**
	 * Clears the in progress flag.
	 */
	public function clear_in_progress_flag() {
		delete_transient( self::IN_PROGRESS . $this->event->ID );
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		return (bool) get_transient( self::IN_PROGRESS );
	}

	/**
	 * Saves the queue of recurring instance dates to be created or deleted.
	 *
	 * If the lists (of instances to be created/deleted/updated) are empty then the entire
	 * queue is deleted.
	 */
	public function save() {
		if ( empty( $this->to_create ) && empty( $this->to_delete ) && empty( $this->to_update ) ) {
			delete_post_meta( $this->event->ID, self::EVENT_QUEUE );
		}
		else {
			update_post_meta( $this->event->ID, self::EVENT_QUEUE, array(
				self::CREATE    => $this->to_create,
				self::UPDATE    => $this->to_update,
				self::DELETE    => $this->to_delete,
				self::EXCLUDE   => $this->to_exclude,
				self::JOB_TOTAL => ( -1 === $this->job_total ) ? $this->count() : $this->job_total,
			) );
		}
	}
}
