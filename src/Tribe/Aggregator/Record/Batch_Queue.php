<?php
/**
 * Create a new Queue to process Batch imports.
 *
 * @since 5.3.0
 */

namespace Tribe\Events\Aggregator\Record;

use DateTime;
use DateTimeZone;
use Exception;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Aggregator__Record__Abstract;
use Tribe__Events__Aggregator__Record__Activity;
use Tribe__Events__Aggregator__Record__Queue;
use Tribe__Events__Aggregator__Record__Queue_Cleaner;
use Tribe__Events__Aggregator__Record__Queue_Interface;
use Tribe__Events__Aggregator__Records as Records;
use Tribe__Events__Aggregator__Service;
use Tribe__Events__Main as TEC;
use WP_Error;
use WP_Post;

/**
 * Class Tribe__Events__Aggregator__Record__Batch_Queue - New Queue system to process imports crated with the new
 * batch system.
 *
 * @since 5.3.0
 */
class Batch_Queue implements Tribe__Events__Aggregator__Record__Queue_Interface {
	/**
	 * Set a name to identify the activity object.
	 *
	 * @since 5.3.0
	 *
	 * @var string $activity_key Key to identify the activity object.
	 */
	public static $activity_key = 'activity';

	/**
	 * Access to the current record.
	 *
	 * @since 5.3.0
	 *
	 * @var Tribe__Events__Aggregator__Record__Abstract $record The current record.
	 */
	public $record;

	/**
	 * @var Tribe__Events__Aggregator__Record__Activity
	 */
	protected $activity;

	/**
	 * Whether any real processing should happen for the queue or not.
	 *
	 * @since 5.3.0
	 *
	 * @var bool
	 */
	protected $null_process = false;

	/**
	 * Tribe__Events__Aggregator__Record__Queue constructor.
	 *
	 * @since 5.3.0
	 *
	 * @param int|Tribe__Events__Aggregator__Record__Abstract       $record The current record or record ID.
	 * @param array|string|null                                     $items The items to build the Queue.
	 * @param Tribe__Events__Aggregator__Record__Queue_Cleaner|null $cleaner The cleaner to remove duplicates.
	 */
	public function __construct( $record, $items = null, Tribe__Events__Aggregator__Record__Queue_Cleaner $cleaner = null ) {
		if ( is_numeric( $record ) ) {
			$record = Records::instance()->get_by_post_id( $record );
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

	/**
	 * Get the activity if a call to a dynamic attribute is taking place in this case `$this->>activity`
	 *
	 * @since 5.3.0
	 *
	 * @param string $key The dynamic key to be returned.
	 *
	 * @return mixed|Tribe__Events__Aggregator__Record__Activity
	 */
	public function __get( $key ) {
		if ( $key === 'activity' ) {
			return $this->activity();
		}

		return null;
	}

	/**
	 * Returns the activity object for the processing of this Queue.
	 *
	 * @since 5.3.0
	 *
	 * @return mixed|Tribe__Events__Aggregator__Record__Activity
	 */
	public function activity() {
		if ( empty( $this->activity ) ) {
			if (
				empty( $this->record->meta[ self::$activity_key ] )
				|| ! $this->record->meta[ self::$activity_key ] instanceof Tribe__Events__Aggregator__Record__Activity
			) {
				$this->activity = new Tribe__Events__Aggregator__Record__Activity();
			} else {
				$this->activity = $this->record->meta[ self::$activity_key ];
			}
		}

		return $this->activity;
	}

	/**
	 * Allows us to check if the Events Data has still pending
	 *
	 * @since 5.3.0
	 *
	 * @return boolean
	 */
	public function is_fetching() {
		return $this->is_in_progress();
	}

	/**
	 * Shortcut to check how many items are going to be processed next.
	 *
	 * @since 5.3.0
	 *
	 * @return int
	 */
	public function count() {
		return 0;
	}

	/**
	 * Shortcut to check if this queue is empty or it has a null process.
	 *
	 * @since 5.3.0
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

	/**
	 * After the process has been completed make sure the `post_modified` and `post_status` are updated accordingly.
	 *
	 * @since 5.3.0
	 *
	 * @return $this
	 */
	protected function complete() {
		// Updates the Modified time for the Record Log.
		$args = [
			'ID'            => $this->record->post->ID,
			'post_modified' => $this->now(),
			'post_status'   => Records::$status->success,
		];

		wp_update_post( $args );

		return $this;
	}

	/**
	 * Processes a batch for the queue
	 *
	 * @since 5.3.0
	 *
	 * @throws Exception
	 *
	 * @param null $batch_size The batch size is ignored on batch import as is controlled via the initial filtered value.
	 *
	 * @return self|Tribe__Events__Aggregator__Record__Activity
	 */
	public function process( $batch_size = null ) {
		// This batch has not started yet, make sure to initiate this import.
		if ( empty( $this->record->meta['batch_started'] ) ) {
			$now = $this->now();

			if ( ! $now instanceof DateTime ) {
				return $this;
			}

			$this->record->update_meta(
				'batch_started',
				$now->format( Dates::DBDATETIMEFORMAT )
			);
			$this->record->update_meta( Tribe__Events__Aggregator__Record__Queue::$queue_key, 'fetch' );
			$this->record->set_status_as_pending();
			$this->start();

			return $this;
		}

		if ( $this->record->post instanceof WP_Post && ! $this->is_in_progress() ) {
			return $this;
		}

		return $this->activity();
	}

	/**
	 * Get the current date time using UTC as the time zone.
	 *
	 * @since 5.3.0
	 *
	 * @return DateTime|false|\Tribe\Utils\Date_I18n
	 */
	private function now() {
		return Dates::build_date_object( 'now', new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Create the initial request to the EA server requesting that the client is ready to start getting batches of events.
	 *
	 * @since 5.3.0
	 */
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

		$body = [
			'batch_size'       => $this->batch_size(),
			'batch_interval'   => $this->batch_interval(),
			'tec_version'      => TEC::VERSION,
			'next_import_hash' => $this->record->meta['next_batch_hash'],
			'api'              => get_rest_url( get_current_blog_id(), 'tribe/event-aggregator/v1' ),
		];

		if ( isset( $this->record->meta['ids_to_import'] ) && is_array( $this->record->meta['ids_to_import'] ) ) {
			$body['selected_events'] = $this->record->meta['ids_to_import'];
		}

		$response = $service->post( "import/{$this->record->meta['import_id']}/deliver/", [ 'body' => $body ] );

		if ( is_wp_error( $response ) ) {
			$this->record->set_status_as_failed( $response );
		}

		$service->api['version'] = $version;
	}

	/**
	 * Return the number of events delivered per batch.
	 *
	 * @since 5.3.0
	 *
	 * @return int
	 */
	private function batch_size() {
		return (int) apply_filters( 'event_aggregator_event_batch_size', 10 );
	}

	/**
	 * Return the interval in seconds of the delivery of each batch.
	 *
	 * @since 5.3.0
	 *
	 * @return int
	 */
	private function batch_interval() {
		return (int) apply_filters( 'event_aggregator_event_batch_interval', 10 );
	}

	/**
	 * Returns the total progress made on processing the queue so far as a percentage.
	 *
	 * @since 5.3.0
	 *
	 * @return int
	 */
	public function progress_percentage() {
		if ( empty( $this->record ) ) {
			return 0;
		}

		if ( empty( $this->record->meta['total_events'] ) ) {
			// Backwards compatible if the total_events meta key is still not present.
			if ( empty( $this->record->meta['percentage_complete'] ) ) {
				return 0;
			}
			return (int) $this->record->meta['percentage_complete'];
		}

		$total = (int) $this->record->meta['total_events'];
		$done = (int) $this->record->activity()->count( TEC::POSTTYPE );

		if ( 0 === $total ) {
			return 100;
		}

		return min( 100, max( 1, (int) ( 100 * ( $done / $total ) ) ) );
	}

	/**
	 * Sets a flag to indicate that update work is in progress for a specific event:
	 * this can be useful to prevent collisions between cron-based updated and realtime
	 * updates.
	 *
	 * The flag naturally expires after an hour to allow for recovery if for instance
	 * execution hangs half way through the processing of a batch.
	 *
	 * @since 5.3.0
	 */
	public function set_in_progress_flag() {
		// No operation.
	}

	/**
	 * Clears the in progress flag.
	 *
	 * @since 5.3.0
	 */
	public function clear_in_progress_flag() {
		// No operation.
	}

	/**
	 * Indicates if the queue for the current event is actively being processed.
	 *
	 * @since 5.3.0
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

		return $this->record->post->post_status === Records::$status->pending;
	}

	/**
	 * Returns the primary post type the queue is processing
	 *
	 * @since 5.3.0
	 *
	 * @return string
	 */
	public function get_queue_type() {
		$item_type = TEC::POSTTYPE;

		if ( ! empty( $this->record->origin ) && 'csv' === $this->record->origin ) {
			$item_type = $this->record->meta['content_type'];
		}

		return $item_type;
	}

	/**
	 * Whether the current queue process is stuck or not.
	 *
	 * @since 5.3.0
	 *
	 * @return bool
	 */
	public function is_stuck() {
		return false;
	}

	/**
	 * Orderly closes the queue process.
	 *
	 * @since 5.3.0
	 *
	 * @return bool
	 */
	public function kill_queue() {
		return true;
	}

	/**
	 * Whether the current queue process failed or not.
	 *
	 * @since 5.3.0
	 *
	 * @return bool
	 */
	public function has_errors() {
		return false;
	}

	/**
	 * Returns the queue error message.
	 *
	 * @since 5.3.0
	 *
	 * @return string
	 */
	public function get_error_message() {
		return '';
	}
}
