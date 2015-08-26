<?php
class Tribe__Events__Pro__Recurrence__Queue_Processor {
	const SCHEDULED_TASK = 'tribe_events_pro_process_recurring_events';

	/**
	 * Number of event instances to be processed in a single batch.
	 *
	 * @var int
	 */
	protected $batch_size = 100;

	/**
	 * Number of events in the current batch processed so far.
	 *
	 * @var int
	 */
	protected $processed = 0;

	/**
	 * @var int
	 */
	protected $current_event_id = 0;

	/**
	 * @var Tribe__Events__Pro__Recurrence__Queue
	 */
	protected $current_queue;


	public function __construct() {
		$this->manage_scheduled_task();
	}

	/**
	 * Configures a scheduled task to handle "background processing" of recurring events.
	 */
	public function manage_scheduled_task() {
		add_action( 'tribe_events_pro_blog_deactivate', array( $this, 'clear_scheduled_task' ) );
		add_action( self::SCHEDULED_TASK, array( $this, 'process_queue' ), 20, 0 );
		$this->register_scheduled_task();
	}


	/**
	 * Runs upon plugin activation, registering our scheduled task used to process
	 * batches of pending recurring event instances.
	 */
	public function register_scheduled_task() {
		if ( ! wp_next_scheduled( self::SCHEDULED_TASK ) ) {
			/**
			 * Filter the interval at which to process recurring event queues.
			 *
			 * By default a custom interval of ever 30mins is specified, however
			 * other intervals such as "hourly", "twicedaily" and "daily" can
			 * normally be substituted.
			 *
			 * @see wp_schedule_event()
			 * @see 'cron_schedules'
			 */
			$interval = apply_filters( 'tribe_events_pro_recurrence_processor_interval', 'every_30mins' );
			wp_schedule_event( time(), $interval, self::SCHEDULED_TASK );
		}
	}

	/**
	 * Expected to fire upon plugin deactivation.
	 */
	public function clear_scheduled_task() {
		wp_clear_scheduled_hook( self::SCHEDULED_TASK );
	}

	/**
	 * Process a batch of queued instances for a specific event.
	 *
	 * This is typically used when processing a small number of instances immediately upon
	 * a recurring event pattern being updated for a particular event, or to facilitate
	 * batches being updated via an ajax update loop.
	 *
	 * The default number of instances processed in a single batch is 10, which can be
	 * overridden using the tribe_events_pro_recurrence_small_batch_size filter hook:
	 * however, the value optional $batch_size parameter takes precedence when provided.
	 *
	 * @param int $event_id
	 * @param int $batch_size
	 */
	public function process_batch( $event_id, $batch_size = null ) {
		/**
		 * Sets the default number of instances to be immediately processed when a recurring event pattern
		 * is updated.
		 *
		 * @param int $small_batch_size
		 */
		$default_batch_size = apply_filters( 'tribe_events_pro_recurrence_small_batch_size', 10 );
		$this->batch_size = ( null === $batch_size ) ? $default_batch_size : (int) $batch_size;

		$this->current_event_id = (int) $event_id;
		$this->do_processing();
	}

	/**
	 * Processes the next waiting batch of recurring event instances, if there are any.
	 *
	 * @param int $batch_size
	 */
	public function process_queue( $batch_size = null ) {
		if ( null === $batch_size ) {
			/**
			 * Controls the size of each batch processed by default (ie, during cron updates of recurring
			 * event instances).
			 *
			 * @param int $default_batch_size
			 */
			$this->batch_size = (int) apply_filters( 'tribe_events_pro_recurrence_batch_size', 100 );
		} else {
			$this->batch_size = (int) $batch_size;
		}

		while ( $this->next_waiting_event() ) {
			if ( ! $this->do_processing() ) break;
		}
	}

	/**
	 * Obtains the post ID of the next event which has a queue of event instances in need
	 * of processing.
	 *
	 * If no events in need of further processing can be found it will return bool false.
	 *
	 * @return boolean
	 */
	protected function next_waiting_event() {
		$waiting_events = get_posts( array(
			'post_type'      => Tribe__Events__Main::POSTTYPE,
			'post_parent'    => 0,
			'meta_key'       => Tribe__Events__Pro__Recurrence__Queue::EVENT_QUEUE,
			'posts_per_page' => 1,
		) );

		if ( empty( $waiting_events ) ) {
			$this->current_event_id = 0;
			return false;
		} else {
			$next_event = array_shift( $waiting_events );
			$this->current_event_id = $next_event->ID;
			return true;
		}
	}

	/**
	 * Processes the current event queue. May return boolean false if it is unable to continue.
	 *
	 * @return bool
	 */
	protected function do_processing() {
		// Bail out if the batch limit has been exceeded, if nothing is waiting in the queue
		// or the queue is actively being processed by a concurrent request/scheduled task
		if ( $this->batch_complete() || ! $this->get_current_queue() || $this->current_queue->is_in_progress() ) {
			return false;
		}

		$this->current_queue->set_in_progress_flag();
		$this->do_deletions();
		$this->do_creations();
		$this->do_updates();

		$this->current_queue->save();
		$this->current_queue->clear_in_progress_flag();

		Tribe__Events__Main::instance()->rebuild_known_range();

		return true;
	}

	/**
	 * Returns true if a non-empty queue exists for the current event, else returns false.
	 *
	 * @return bool
	 */
	protected function get_current_queue() {
		try {
			$this->current_queue = new Tribe__Events__Pro__Recurrence__Queue( $this->current_event_id );
		}
		catch ( Exception $e ) {
			do_action( 'log', sprintf( __( 'Could not process queue for event %1$d: %2$s', 'tribe-events-pro' ), $this->current_event_id, $e->getMessage() ) );
			return false;
		}

		return $this->current_queue->is_empty() ? false : true;
	}

	protected function do_deletions() {
		$instances_to_delete = $this->current_queue->instances_to_delete();

		foreach ( $instances_to_delete as $instance_id => $start_date ) {
			// Don't process more than the current batch size allows
			if ( $this->batch_complete() ) {
				break;
			}

			Tribe__Events__Pro__Recurrence_Meta::delete_unexcluded_event( $instance_id, $start_date );

			unset( $instances_to_delete[ $instance_id ] );
			$this->processed++;
		}

		// Update the "to delete" list
		$this->current_queue->instances_to_delete( $instances_to_delete );
	}

	protected function do_updates() {
		$instances_to_update = $this->current_queue->instances_to_update();

		foreach ( $instances_to_update as $instance_id => $start_date ) {
			// Don't process more than the current batch size allows
			if ( $this->batch_complete() ) {
				break;
			}

			$instance = new Tribe__Events__Pro__Recurrence_Instance( $this->current_event_id, $start_date, $instance_id );
			$instance->save();

			unset( $instances_to_update[ $instance_id ] );
			$this->processed++;
		}

		$this->current_queue->instances_to_update( $instances_to_update );
	}

	protected function do_creations() {
		$exclusions = $this->current_queue->instances_to_exclude();
		$instances_to_create = $this->current_queue->instances_to_create();

		foreach ( $instances_to_create as $key => $start_date ) {
			// Don't process more than the current batch size allows
			if ( $this->batch_complete() ) {
				break;
			}

			// Some instances may deliberately have been removed - let's remove
			// them from the list of events to create and move on
			if ( in_array( $start_date, $exclusions ) ) {
				unset( $instances_to_create[ $key ] );
				$this->processed++;
				continue;
			}

			$instance = new Tribe__Events__Pro__Recurrence_Instance( $this->current_event_id, $start_date );
			$instance->save();

			unset( $instances_to_create[ $key ] );
			$this->processed++;
		}

		$this->current_queue->instances_to_create( $instances_to_create );
	}

	/**
	 * Determines if the batch job is complete.
	 *
	 * Currently this is simply a measure of the number of instances processed against
	 * the batch size limit - however it could potentially be expanded to include an
	 * additional time based check.
	 *
	 * @return bool
	 */
	protected function batch_complete() {
		return ( $this->processed >= $this->batch_size );
	}
}
