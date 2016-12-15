<?php
class Tribe__Events__Aggregator__Record__Queue_Processor {
	public static $scheduled_key = 'tribe_aggregator_process_insert_records';

	/**
	 *Number of items to be processed in a single batch.
	 *
	 * @var int
	 */
	public static $batch_size = 5;

	/**
	 *Number of items to be processed in a single small batch.
	 *
	 * @var int
	 */
	public static $small_batch_size = 5;

	/**
	 * Number of items in the current batch processed so far.
	 *
	 * @var int
	 */
	protected $processed = 0;

	/**
	 * @var int
	 */
	protected $current_record_id = 0;

	/**
	 * @var Tribe__Events__Aggregator__Record__Queue
	 */
	public $current_queue;


	public function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	public function action_init() {
		$this->manage_scheduled_task();
	}

	/**
	 * Configures a scheduled task to handle "background processing" of import record insertions/updates.
	 */
	public function manage_scheduled_task() {
		add_action( 'tribe_events_blog_deactivate', array( $this, 'clear_scheduled_task' ) );
		add_action( self::$scheduled_key, array( $this, 'process_queue' ), 20, 0 );
		$this->register_scheduled_task();
	}

	/**
	 * Runs upon plugin activation, registering our scheduled task used to process
	 * batches of pending import record inserts/updates.
	 */
	public function register_scheduled_task() {
		if ( wp_next_scheduled( self::$scheduled_key ) ) {
			return;
		}

		/**
		 * Filter the interval at which to process import records.
		 *
		 * By default a custom interval of ever 30mins is specified, however
		 * other intervals such as "hourly", "twicedaily" and "daily" can
		 * normally be substituted.
		 *
		 * @see wp_schedule_event()
		 * @see 'cron_schedules'
		 */
		$interval = apply_filters( 'tribe_aggregator_record_processor_interval', 'tribe-every15mins' );
		wp_schedule_event( time(), $interval, self::$scheduled_key );
	}

	/**
	 * Expected to fire upon plugin deactivation.
	 */
	public function clear_scheduled_task() {
		wp_clear_scheduled_hook( self::$scheduled_key );
	}

	/**
	 * Process a batch of queued items for a specific import record.
	 *
	 * This is typically used when processing a small number of instances immediately upon
	 * an import record queue being updated for a particular import record, or to facilitate
	 * batches being updated via an ajax update loop.
	 *
	 * The default number of items processed in a single batch is 10, which can be
	 * overridden using the tribe_events_aggregator_small_batch_size filter hook
	 *
	 * @param int $record_id
	 * @param int $batch_size
	 */
	public function process_batch( $record_id, $batch_size = null ) {
		/**
		 * Sets the default number of instances to be immediately processed when a record has items to insert
		 *
		 * @param int $small_batch_size
		 */
		$default_batch_size = apply_filters( 'tribe_aggregator_small_batch_size', self::$small_batch_size );
		self::$batch_size = ( null === $batch_size ) ? $default_batch_size : (int) $batch_size;

		$this->current_record_id = (int) $record_id;
		$this->do_processing();
	}

	/**
	 * Processes the next waiting batch of Import Record posts, if there are any.
	 *
	 * @param int $batch_size
	 */
	public function process_queue( $batch_size = null ) {
		if ( null === $batch_size ) {
			/**
			 * Controls the size of each batch processed by default (ie, during cron updates of record
			 * inserts/updates).
			 *
			 * @param int $default_batch_size
			 */
			self::$batch_size = (int) apply_filters( 'tribe_aggregator_batch_size', self::$batch_size );
		} else {
			self::$batch_size = (int) $batch_size;
		}

		while ( $this->next_waiting_record() ) {
			if ( ! $this->do_processing() ) {
				break;
			}
		}
	}

	public function set_current_queue( Tribe__Events__Aggregator__Record__Queue $queue ) {
		$this->current_queue = $queue;
	}

	/**
	 * Obtains the post ID of the next record which has a queue of items in need
	 * of processing.
	 *
	 * If no records in need of further processing can be found it will return bool false.
	 *
	 * @param boolean $interactive_only Whether or not we should look for imports that were kicked off interactively
	 *
	 * @return boolean
	 */
	public function next_waiting_record( $interactive_only = false ) {
		$args = array(
			'post_type'      => Tribe__Events__Aggregator__Records::$post_type,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key' => Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . Tribe__Events__Aggregator__Record__Queue::$queue_key,
					'compare' => 'EXISTS',
				),
			),
		);

		if ( $interactive_only ) {
			$args['meta_query'][] = array(
				'key' => Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'interactive',
				'compare' => 'EXISTS',
			);
		}

		$waiting_records = get_posts( $args );

		if ( empty( $waiting_records ) ) {
			return $this->current_record_id = 0;
		} else {
			$next_record = array_shift( $waiting_records );
			return $this->current_record_id = $next_record->ID;
		}
	}

	/**
	 * Processes the current import record queue. May return boolean false if it is unable to continue.
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
		$processed = $this->current_queue->process( self::$batch_size );
		// in the 'fetch' phase this will not be a Queue object
		if ( $processed instanceof Tribe__Events__Aggregator__Record__Queue ) {
			$this->processed += $processed->activity->count( $this->current_queue->get_queue_type() );
		}
		$this->current_queue->clear_in_progress_flag();

		return true;
	}

	/**
	 * Returns true if a non-empty queue exists for the current record, else returns false.
	 *
	 * @return bool
	 */
	protected function get_current_queue() {
		try {
			$this->current_queue = new Tribe__Events__Aggregator__Record__Queue( $this->current_record_id );
		} catch ( InvalidArgumentException $e ) {
			do_action( 'log', sprintf( __( 'Could not process queue for Import Record %1$d: %2$s', 'the-events-calendar' ), $this->current_record_id, $e->getMessage() ) );
			return false;
		}

		return $this->current_queue->is_empty() ? false : true;
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
		return ( $this->processed >= self::$batch_size );
	}
}
