<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Batch_Queue implements Tribe__Events__Aggregator__Record__Queue_Interface {
	public static $activity_key = 'activity';

	/**
	 * @var Tribe__Events__Aggregator__Record__Abstract
	 */
	public $record;

	protected $importer;

	/**
	 * @var Tribe__Events__Aggregator__Record__Activity
	 */
	protected $activity;

	/**
	 * Holds the Items that will be processed
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * Holds the Items that will be processed next
	 *
	 * @var array
	 */
	public $next = [];

	/**
	 * How many items are going to be processed
	 *
	 * @var int
	 */
	public $total = 0;

	/**
	 * @var Tribe__Events__Aggregator__Record__Queue_Cleaner
	 */
	protected $cleaner;

	/**
	 * Whether any real processing should happen for the queue or not.
	 *
	 * @var bool
	 */
	protected $null_process = false;

	/**
	 * @var bool Whether this queue instance has acquired the lock or not.
	 */
	protected $has_lock = false;

	/**
	 * Tribe__Events__Aggregator__Record__Queue constructor.
	 *
	 * @param int|Tribe__Events__Aggregator__Record__Abstract       $record
	 * @param Tribe__Events__Aggregator__Record__Queue_Cleaner|null $cleaner
	 */
	public function __construct( $record, Tribe__Events__Aggregator__Record__Queue_Cleaner $cleaner = null ) {
		if ( is_numeric( $record ) ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $record );
		}

		if ( ! is_object( $record ) || ! $record instanceof \Tribe__Events__Aggregator__Record__Abstract ) {
			$this->null_process = true;

			return;
		}

		$cleaner = $cleaner ? $cleaner : new Tribe__Events__Aggregator__Record__Queue_Cleaner();
		$cleaner->remove_duplicate_pending_records_for( $record );

		if ( $cleaner->maybe_fail_stalled_record( $record ) ) {
			$this->null_process = true;

			return;
		}

		$this->record = $record;
		$this->activity();
	}

	public function __get( $key ) {
		switch ( $key ) {
			case 'activity':
				return $this->activity();
				break;
		}
	}

	public function activity() {
		if ( empty( $this->activity ) ) {
			if (
				empty( $this->record->meta[ self::$activity_key ] )
				|| ! $this->record->meta[ self::$activity_key ] instanceof Tribe__Events__Aggregator__Record__Activity
			) {
				$this->activity = new Tribe__Events__Aggregator__Record__Activity;
			} else {
				$this->activity = $this->record->meta[ self::$activity_key ];
			}
		}

		return $this->activity;
	}

	/**
	 * Allows us to check if the Events Data has still pending
	 *
	 * @return boolean
	 */
	public function is_fetching() {
		return $this->is_in_progress();
	}

	/**
	 * Shortcut to check how many items are going to be processed next
	 *
	 * @return int
	 */
	public function count() {
		return 0;
	}

	/**
	 * Shortcut to check if this queue is empty or it has a null process.
	 *
	 * @return boolean `true` if this queue instance has acquired the lock and
	 *                 the count is 0, `false` otherwise.
	 */
	public function is_empty() {
		if ( $this->null_process ) {
			return true;
		}

		return ! $this->is_in_progress();
	}

	protected function complete() {
		// Updates the Modified time for the Record Log
		$args = [
			'ID'            => $this->record->post->ID,
			'post_modified' => date( Tribe__Date_Utils::DBDATETIMEFORMAT, current_time( 'timestamp' ) ),
			'post_status'   => Tribe__Events__Aggregator__Records::$status->success,
		];

		wp_update_post( $args );

		return $this;
	}

	/**
	 * Processes a batch for the queue
	 *
	 * @return self|Tribe__Events__Aggregator__Record__Activity
	 */
	public function process( $batch_size = null ) {
		if ( empty( $this->record->meta['batch_started'] ) ) {
			$this->record->update_meta(
				'batch_started',
				( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )->format( Tribe__Date_Utils::DBDATETIMEFORMAT )
			);
			$this->record->set_status_as_pending();
			$this->start();

			return $this;
		}

		if ( $this->record->post instanceof WP_Post && ! $this->is_in_progress() ) {
			return $this;
		}

		return $this->activity();
	}

	public function start() {
		if (
			empty( $this->record->meta['allow_batch_push'] )
			|| empty( $this->record->meta['import_id'] )
			|| empty( $this->record->meta['next_batch_hash'] )
		) {
			$error = new WP_Error(
				'core:aggregator:invalid-batch-record',
				esc_html__( 'The batch registered does not have all the required fields.', 'the-events-calendar' )
			);
			$this->record->set_status_as_failed( $error );

			return;
		}

		/** @var Tribe__Events__Aggregator__Service $service */
		$service = tribe( 'events-aggregator.service' );

		if ( $service->is_over_limit( true ) ) {
			$this->record->update_meta( 'last_import_status', 'error:usage-limit-exceeded' );
			$this->record->set_status_as_failed();

			return;
		}

		$version                 = $service->api['version'];
		$service->api['version'] = 'v2.0.0';

		// TODO: Filter the body before delivering to EA.
		$body = [
			'batch_size'       => 10,
			'batch_interval'   => 10,
			'tec_version'      => Tribe__Events__Main::VERSION,
			'next_import_hash' => $this->record->meta['next_batch_hash'],
			'api'              => get_rest_url( get_current_blog_id(), 'tribe/event-aggregator/v1' ),
		];

		if ( isset( $this->record->meta['ids_to_import'] ) && is_array( $this->record->meta['ids_to_import'] ) ) {
			$body['selected_events'] = $this->record->meta['ids_to_import'];
		}

		$response = $service->post( "import/{$this->record->meta['import_id']}}/deliver/", [ 'body' => $body ] );

		if ( is_wp_error( $response ) ) {
			$this->record->set_status_as_failed( $response );
		}

		$service->api['version'] = $version;
	}

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @return int
	 */
	public function progress_percentage() {
		if ( empty( $this->record ) || empty( $this->record->meta['percentage_complete'] ) ) {
			return 0;
		}

		return (int) $this->record->meta['percentage_complete'];
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
	}

	/**
	 * Clears the in progress flag.
	 */
	public function clear_in_progress_flag() {
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		if ( empty( $this->record->id ) ) {
			return false;
		}

		if ( ! $this->record->post instanceof WP_Post ) {
			return false;
		}

		return $this->record->post->post_status === Tribe__Events__Aggregator__Records::$status->pending;
	}

	/**
	 * Returns the primary post type the queue is processing
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
	 * Whether the current queue process is stuck or not.
	 *
	 * @since 4.6.21
	 *
	 * @return mixed
	 */
	public function is_stuck() {
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
		return false;
	}

	/**
	 * Returns the queue error message.
	 *
	 * @since 4.6.21
	 *
	 * @return string
	 */
	public function get_error_message() {
		return '';
	}
}
