<?php
// Don't load directly
defined( 'WPINC' ) or die;

use Tribe__Events__Aggregator__Records as Records;

// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared

class Tribe__Events__Aggregator__Cron {
	/**
	 * Action where the cron will run, on schedule
	 * @var string
	 */
	public static $action = 'tribe_aggregator_cron';

	/**
	 * Action where the cron will run, if enqueued manually
	 * @var string
	 */
	public static $single_action = 'tribe_aggregator_single_cron';

	/**
	 * Limit of Requests to our servers
	 * @var int
	 */
	private $limit = 25;

	/**
	 * A Boolean holding if this Cron is Running
	 * @var boolean
	 */
	private $is_running = false;

	/**
	 * Static Singleton Holder
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setup all the hooks and filters
	 *
	 * @return void
	 */
	private function __construct() {
		// Register the base cron schedule
		add_action( 'init', [ $this, 'action_register_cron' ] );

		// Register the Required Cron Schedules
		add_filter( 'cron_schedules', [ $this, 'filter_add_cron_schedules' ] );

		// Check for imports on cron action
		add_action( self::$action, [ $this, 'run' ] );
		add_action( self::$single_action, [ $this, 'run' ] );

		// Decreases limit after each Request, runs late for security
		add_filter( 'pre_http_request', [ $this, 'filter_check_http_limit' ], 25, 3 );

		// Add the Actual Process to run on the Action
		add_action( 'tribe_aggregator_cron_run', [ $this, 'verify_child_record_creation' ], 5 );
		add_action( 'tribe_aggregator_cron_run', [ $this, 'verify_fetching_from_service' ], 15 );
		add_action( 'tribe_aggregator_cron_run', [ $this, 'start_batch_pushing_records' ], 20 );
		add_action( 'tribe_aggregator_cron_run', [ $this, 'purge_expired_records' ], 25 );
	}

	/**
	 * Frequencies in which a Scheduled import can Happen
	 *
	 * @param  array  $search  Search on existing schedules with `array_intersect_assoc`
	 *
	 * @return array|stdClass
	 */
	public function get_frequency( $search = [] ) {
		$search = wp_parse_args( $search, [] );

		/**
		 * Allow developers to filter to add or remove schedules
		 * @param array $schedules
		 */
		$found = $schedules = apply_filters(
			'tribe_aggregator_record_frequency',
			[
				(object) [
					'id'       => 'on_demand',
					'interval' => false,
					'text'     => esc_html_x( 'On Demand', 'aggregator schedule frequency', 'the-events-calendar' ),
				],
				(object) [
					'id'       => 'every30mins',
					'interval' => MINUTE_IN_SECONDS * 30,
					'text'     => esc_html_x(
						'Every 30 Minutes',
						'aggregator schedule frequency',
						'the-events-calendar'
					),
				],
				(object) [
					'id'       => 'hourly',
					'interval' => HOUR_IN_SECONDS,
					'text'     => esc_html_x( 'Hourly', 'aggregator schedule frequency', 'the-events-calendar' ),
				],
				(object) [
					'id'       => 'daily',
					'interval' => DAY_IN_SECONDS,
					'text'     => esc_html_x( 'Daily', 'aggregator schedule frequency', 'the-events-calendar' ),
				],
				(object) [
					'id'       => 'weekly',
					'interval' => WEEK_IN_SECONDS,
					'text'     => esc_html_x( 'Weekly', 'aggregator schedule frequency', 'the-events-calendar' ),
				],
				(object) [
					'id'       => 'monthly',
					'interval' => DAY_IN_SECONDS * 30,
					'text'     => esc_html_x( 'Monthly', 'aggregator schedule frequency', 'the-events-calendar' ),
				],
			]
		);

		if ( ! empty( $search ) ) {
			$found = [];

			foreach ( $schedules as $i => $schedule ) {
				// Check if the search matches this schedule
				$intersect = array_intersect_assoc( $search, (array) $schedule );

				// Modify the found array if something was discovered
				if ( ! empty( $intersect ) ) {
					$found[] = $schedule;
				}
			}
		}

		// If there is only return the only one
		return count( $found ) === 1 ? reset( $found ) : $found;
	}

	/**
	 * Register the base frequency on WP cron system
	 *
	 * @return void
	 */
	public function action_register_cron() {
		// if the service isn't active, don't do anything
		if ( ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return;
		}

		// If we have an cron scheduled we bail
		if ( wp_next_scheduled( self::$action ) ) {
			return;
		}

		// Fetch the initial Date and Hour
		$date = date( 'Y-m-d H' );

		// Based on the Minutes construct a wp_cron
		$minutes = (int) date( 'i' );
		// Get minutes / 15 with no remainder.
		$minutes = intdiv( $minutes, 15 ) * 15;
		// Format it & insert into date string. Add 0 seconds.
		$date .= sprintf( ':%02d', $minutes ) . ':00';

		// Fetch the last half hour as a timestamp
		$start_timestamp = strtotime( $date );

		// randomize the time by plus/minus 0-5 minutes
		$random_minutes = ( mt_rand( -5, 5 ) * 60 );
		$start_timestamp += $random_minutes;

		$current_time = time();

		// if the start timestamp is older than RIGHT NOW, set it for 5 minutes from now.
		if ( $current_time > $start_timestamp ) {
			$start_timestamp = $current_time + absint( $random_minutes );
		}

		// Now add an action once every fifteen minutes.
		wp_schedule_event( $start_timestamp, 'tribe-every15mins', self::$action );
	}

	/**
	 * Adds the Frequency to WP cron schedules
	 * Instead of having cron be scheduled to specific times, we will check every 30 minutes
	 * to make sure we can insert without having to expire cache.
	 *
	 * @param array $schedules Current list of schedules.
	 *
	 * @return array            Modified list of schedules.
	 */
	public function filter_add_cron_schedules( $schedules = [] ) {
		// Ensure schedules is an array.
		$schedules = is_array( $schedules ) ? $schedules : [];

		// Adds the Min frequency to WordPress cron schedules
		$schedules['tribe-every15mins'] = [
			'interval' => MINUTE_IN_SECONDS * 15,
			'display'  => esc_html_x( 'Every 15 minutes', 'aggregator schedule frequency', 'the-events-calendar' ),
		];

		return $schedules;
	}

	/**
	 * Allows us to Prevent too many of our Requests to be fired at on single Cron Job
	 *
	 * @param  boolean  $run     Shouldn't trigger the call
	 * @param  array    $request The Request that was made
	 * @param  string   $url     To which URL
	 *
	 * @return boolean|array|object
	 */
	public function filter_check_http_limit( $run = false, $request = null, $url = null ) {
		// We bail if it's not a CRON job
		if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
			return $run;
		}

		// If someone changed this already we bail, it's not going to be fired
		if ( false !== $run ) {
			return $run;
		}

		// Bail if it wasn't done inside of the Actual Cron task
		if ( true !== $this->is_running ) {
			return $run;
		}

		$service = tribe( 'events-aggregator.service' );

		// If the Domain is not we just keep the same answer
		if ( 0 !== strpos( $url, $service->api()->domain ) ) {
			return $run;
		}

		// If we already reached 0 we throw an error
		if ( $this->limit <= 0 ) {
			// Schedule a Cron Event to happen ASAP, and flag it for searching and we need to make it unique
			// By default WordPress won't allow more than one Action to happen twice in 10 minutes
			wp_schedule_single_event( time(), self::$single_action );

			return tribe_error( 'core:aggregator:http_request-limit', [ 'request' => $request, 'url' => $url ] );
		}

		// Lower the Limit
		$this->limit--;

		// Return false to make the Actual Request Run
		return $run;
	}

	/**
	 * A Wrapper method to run the Cron Tasks here
	 *
	 * @return void
	 */
	public function run() {
		// if the service isn't active, don't do anything
		if ( ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return;
		}

		// if the service has been disabled by the user don't do anything
		if ( true === tribe_get_option( 'tribe_aggregator_disable', false ) ) {
			return;
		}

		// Flag that we are running the Task
		$this->is_running = true;

		/**
		 * Have a hook be Fired, to allow Priorities to be changed and other methods to be hooked
		 */
		do_action( 'tribe_aggregator_cron_run' );

		// Flag that we stopped running the Cron Task
		$this->is_running = false;
	}

	/**
	 * Checks if any Child Record needs to be created, this will run on the Cron every 15m
	 *
	 * @since  4.3
	 * @return void
	 */
	public function verify_child_record_creation() {
		// if the service isn't active, don't do anything
		if ( ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return;
		}
		$records = Records::instance();
		$service = tribe( 'events-aggregator.service' );

		$query = $records->query( [
			'post_status'    => Records::$status->schedule,
			'posts_per_page' => -1,
		] );

		if ( ! $query->have_posts() ) {
			tribe( 'logger' )->log_debug( 'No Records Scheduled, skipped creating children', 'EA Cron' );

			return;
		}

		foreach ( $query->posts as $post ) {
			$record = Records::instance()->get_by_post_id( $post );

			if ( tribe_is_error( $record ) ) {
				continue;
			}

			if ( $record instanceof Tribe__Events__Aggregator__Record__Unsupported ) {
				/**
				 * This means the record post exists but the origin is not currently supported.
				 * To avoid re-looping on this let's trash this post and continue.
				 */
				$record->delete();
				continue;
			}

			if ( ! $record->is_schedule_time() ) {
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) skipped, not scheduled time', $record->id ), 'EA Cron' );
				continue;
			}

			if ( $record->get_child_record_by_status( 'pending', -1, [ 'after' => time() - 4 * HOUR_IN_SECONDS ] ) ) {
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) skipped, has pending child(ren)', $record->id ), 'EA Cron' );
				continue;
			}

			// if there are no remaining imports for today, log that and skip
			if ( $service->is_over_limit( true ) ) {
				$import_limit     = $service->get_limit( 'import' );
				$service_template = $service->get_service_message( 'error:usage-limit-exceeded', [ $import_limit ] );
				tribe( 'logger' )->log_debug( sprintf( $service_template . ' (%1$d)', $record->id ), 'EA Cron' );
				$record->update_meta( 'last_import_status', 'error:usage-limit-exceeded' );
				continue;
			}

			// Creating the child records based on this Parent
			$child = $record->create_child_record();
			tribe( 'logger' )->log_debug( sprintf( 'Creating child record %d for %d', $child->id, $record->id ), 'EA Cron' );

			if ( ! is_wp_error( $child ) ) {
				tribe( 'logger' )->log_debug( sprintf( 'Record %d was created as a child of %d', $child->id, $record->id ), 'EA Cron' );

				// Creates on the Service a Queue to Fetch the events
				$response = $child->queue_import();

				tribe( 'logger' )->log_debug( sprintf( 'Queueing import on EA Service for %d (child of %d)', $child->id, $record->id ), 'EA Cron' );

				if ( ! empty( $response->status ) ) {
					tribe( 'logger' )->log_debug( sprintf( '%s — %s (%s)', $response->status, $response->message, $response->data->import_id ),
						'EA Cron' );

					$record->update_meta( 'last_import_status', 'success:queued' );

					$this->maybe_process_immediately( $child );
				} elseif ( is_numeric( $response ) ) {
					// it's the post ID of a rescheduled record
					tribe( 'logger' )->log_debug( sprintf( 'rescheduled — %s', $response ), 'EA Cron' );

					$record->update_meta( 'last_import_status', 'queued' );
				} else {
					$message = '';

					if ( is_string( $response ) ) {
						$message = $response;
					} elseif ( is_object( $response ) || is_array( $response ) ) {
						$message = json_encode( $response );
					}

					tribe( 'logger' )->log_debug( 'Could not create Queue on Service, message is ' . $message, 'EA Cron' );

					$record->update_meta( 'last_import_status', 'error:import-failed' );
				}
			} else {
				tribe( 'logger' )->log_debug( $child->get_error_message(), 'EA Cron' );
				$record->update_meta( 'last_import_status', 'error:import-failed' );
			}
		}
	}

	/**
	 * Start the processing of the scheduled imports created with batch pushing the cron job would select and start
	 * the beginning of the batch delivery.
	 *
	 * @since 5.3.0
	 */
	public function start_batch_pushing_records() {
		if ( ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return;
		}

		$records = Records::instance();
		$query = $this->get_batch_pushing_records();

		if ( ! $query->have_posts() ) {
			tribe( 'logger' )->log_debug( 'No Pending Batch to be started', 'EA Cron' );

			return;
		}

		tribe( 'logger' )->log_debug( "Found {$query->found_posts} records", 'EA Cron' );

		$cleaner = new Tribe__Events__Aggregator__Record__Queue_Cleaner();
		foreach ( $query->posts as $post ) {
			$record = $records->get_by_post_id( $post );

			if ( null === $record || tribe_is_error( $record ) ) {
				continue;
			}

			// Just double Check for CSV.
			if ( 'csv' === $record->origin ) {
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) skipped, has CSV origin', $record->id ), 'EA Cron' );
				continue;
			}

			$cleaner->set_stall_limit( HOUR_IN_SECONDS * 22 )->set_time_to_live( HOUR_IN_SECONDS * 23 );

			$cleaner->remove_duplicate_pending_records_for( $record );
			$failed = $cleaner->maybe_fail_stalled_record( $record );

			if ( $failed ) {
				tribe( 'logger' )->log_debug( sprintf( 'Stalled record (%d) was skipped', $record->id ), 'EA Cron' );
				continue;
			}

			$record->process_posts( [], true );
		}
	}

	/**
	 * Get the first set of pending schedule records to be processed for batch pushing.
	 *
	 * @since 5.3.0
	 *
	 * @return WP_Query The result of the Query.
	 */
	private function get_batch_pushing_records() {
		return Records::instance()->query(
			[
				'post_status'    => Records::$status->pending,
				'posts_per_page' => 250,
				'order'          => 'ASC',
				'meta_query'     => [
					'origin-not-csv'               => [
						'key'     => '_tribe_aggregator_origin',
						'value'   => 'csv',
						'compare' => '!=',
					],
					'batch-push-support-specified' => [
						'key'     => '_tribe_aggregator_allow_batch_push',
						'value'   => true,
						'compare' => '=',
					],
					'batch-not-queued'             => [
						'key'     => '_tribe_aggregator_batch_started',
						'value'   => 'bug #23268',
						'compare' => 'NOT EXISTS',
					],
				],
				'after'          => '-4 hours',
			]
		);
	}


	/**
	 * Checks if any record data needs to be fetched from the service, this will run on the Cron every 15m
	 *
	 * @since  4.3
	 * @return void
	 */
	public function verify_fetching_from_service() {
		// if the service isn't active, don't do anything
		if ( ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return;
		}

		$records = Records::instance();

		$query = $records->query( [
			'post_status'    => Records::$status->pending,
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'meta_query'     => [
				'origin-not-csv' => [
					'key'     => '_tribe_aggregator_origin',
					'value'   => 'csv',
					'compare' => '!=',
				],
				[
					'relation'                        => 'OR',
					// If not specified then assume batch push is not supported.
					'no-batch-push-support-specified' => [
						'key'     => '_tribe_aggregator_allow_batch_push',
						'value'   => 'bug #23268',
						'compare' => 'NOT EXISTS',
					],
					// If specified, and not `1`, then batch push is not supported.
					'explicit-no-batch-push-support'  => [
						'key'     => '_tribe_aggregator_allow_batch_push',
						'value'   => '1',
						'compare' => '!=',
					],
				],
			],
			'after'          => '-4 hours',
		] );

		if ( ! $query->have_posts() ) {
			tribe( 'logger' )->log_debug( 'No Records Pending, skipped Fetching from service', 'EA Cron' );

			return;
		}

		$count = count( $query->posts );
		tribe( 'logger' )->log_debug( "Found {$count} records", 'EA Cron' );

		$cleaner = new Tribe__Events__Aggregator__Record__Queue_Cleaner();
		foreach ( $query->posts as $post ) {
			$record = $records->get_by_post_id( $post );

			if ( $record === null || tribe_is_error( $record ) ) {
				continue;
			}

			// Just double Check for CSV
			if ( 'csv' === $record->origin ) {
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) skipped, has CSV origin', $record->id ), 'EA Cron' );
				continue;
			}

			$cleaner->remove_duplicate_pending_records_for( $record );
			$failed = $cleaner->maybe_fail_stalled_record( $record );

			if ( $failed ) {
				tribe( 'logger' )->log_debug( sprintf( 'Stalled record (%d) was skipped', $record->id ), 'EA Cron' );
				continue;
			}

			// Open a Queue to try to process the posts
			$queue = $record->process_posts();

			if ( ! is_wp_error( $queue ) ) {
				/** @var Tribe__Events__Aggregator__Record__Queue_Interface $queue */
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) has processed queue ', $record->id ), 'EA Cron' );

				if ( $queue instanceof Tribe__Events__Aggregator__Record__Queue_Interface ) {
					$activity = $queue->activity()->get();
				} else {
					// if fetching or on error
					$activity = $queue->get();
				}

				foreach ( $activity as $key => $actions ) {
					foreach ( $actions as $action => $ids ) {
						if ( empty( $ids ) ) {
							continue;
						}
						tribe( 'logger' )->log_debug( sprintf( "\t" . '%s — %s: %s', $key, $action, implode( ', ', $ids ) ), 'EA Cron' );
					}
				}
			} else {
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) — %s', $record->id, $queue->get_error_message() ), 'EA Cron' );
			}
		}
	}

	/**
	 * @since  4.3.2
	 * @return void
	 */
	public function purge_expired_records() {
		global $wpdb;
		$records        = Records::instance();
		$statuses       = Records::$status;
		$deletable_statuses = [
			$statuses->pending,
			$statuses->success,
			$statuses->failed,
			$statuses->draft,
		];
		$date_threshold = date( 'Y-m-d H:i:s', time() - $records->get_retention() );

		// Check the constant to see if we should purge records using direct queries or not.
		$direct_deletion = defined( 'TEC_EVENT_AGGREGATOR_RECORDS_PURGE_DIRECT_DELETION' )
		                   && TEC_EVENT_AGGREGATOR_RECORDS_PURGE_DIRECT_DELETION;

		/**
		 * Filters whether to use direct deletion of Event Aggregator records during cleanup or not.
		 *
		 * Note the filtered value will override the value defined by the
		 * `TEC_EVENT_AGGREGATOR_RECORDS_PURGE_DIRECT_DELETION` constant.
		 *
		 * @since 6.0.9
		 *
		 * @param bool $direct_deletion Whether to use direct deletion of Event Aggregator records during cleanup or not.
		 */
		$direct_deletion = apply_filters( 'tec_event_aggregator_direct_record_deletion', $direct_deletion );

		if ( $direct_deletion ) {
			$this->purge_expired_records_directly( $deletable_statuses, $date_threshold );

			return;
		}

		$sql = "
			SELECT
				meta_value
			FROM
				{$wpdb->postmeta}
				JOIN {$wpdb->posts}
				ON ID = post_id
				AND post_status = %s
			WHERE
				meta_key = %s
		";

		// let's make sure we don't purge the most recent record for each import
		$records_to_retain = $wpdb->get_col(
			$wpdb->prepare(
				$sql,
				$statuses->schedule,
				Tribe__Events__Aggregator__Record__Abstract::$meta_key_prefix . 'recent_child'
			)
		);

		$args = [
			'post_status'    => $deletable_statuses,
			'date_query'     => [
				[
					'before' => $date_threshold,
					'column' => 'post_date_gmt',
				],
			],
			'order'          => 'ASC',
			'posts_per_page' => 100,
		];

		if ( $records_to_retain ) {
			$args['post__not_in'] = $records_to_retain;
		}

		$query = $records->query( $args );

		if ( ! $query->have_posts() ) {
			tribe( 'logger' )->log_debug( 'No Records over retention limit, skipped pruning expired', 'EA Cron' );

			return;
		}

		foreach ( $query->posts as $post ) {
			$record = Records::instance()->get_by_post_id( $post );

			if ( tribe_is_error( $record ) ) {
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) skipped, original post non-existent', $post->ID ), 'EA Cron' );
				continue;
			}

			if ( ! $record->has_passed_retention_time() ) {
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) skipped, not past retention time', $record->id ), 'EA Cron' );
				continue;
			}

			$has_post = false;
			$deleted  = false;

			// Creating the child records based on this Parent
			if ( ! empty( $record->id ) ) {
				$has_post = true;
				$deleted  = wp_delete_post( $record->id, true );
			}

			if ( $has_post ) {
				if ( $deleted ) {
					tribe( 'logger' )->log_debug( sprintf( 'Record (%d) was pruned', $deleted->ID ), 'EA Cron' );
				} else {
					tribe( 'logger' )->log_debug( sprintf( 'Record (%d) was not pruned', $deleted ), 'EA Cron' );
				}
			} else {
				tribe( 'logger' )->log_debug( sprintf( 'Record (%d) did not have a `$record->id` so it did not require pruning', $deleted ),
					'EA Cron' );
			}
		}
	}

	/**
	 * Tries to fetch the data for the scheduled import and immediately process it.
	 *
	 * @since 4.6.16
	 *
	 * @param Tribe__Events__Aggregator__Record__Abstract $record
	 */
	protected function maybe_process_immediately( Tribe__Events__Aggregator__Record__Abstract $record ) {
		$import_data = $record->prep_import_data();

		if ( empty( $import_data ) || $import_data instanceof WP_Error || ! is_array( $import_data ) ) {
			return;
		}

		tribe( 'logger' )->log_debug( sprintf( 'Import %s data available: processing immediately', $record->id ), 'EA Cron' );
		$record->process_posts( $import_data, true );
	}

	/**
	 * Deletes expired records from the database using direct queries, bypassing the filters and actions associated
	 * with WordPress posts functions.
	 *
	 * @since 6.0.9
	 *
	 * @param array<string> $deletable_statuses The statuses to use to fetch the records that should be purged.
	 * @param string        $date_threshold     The date threshold to use to determine if a record should be purged or not.
	 *
	 * @return int The number of records purged.
	 */
	private function purge_expired_records_directly( array $deletable_statuses, string $date_threshold ): int {
		global $wpdb;
		$deletable_statuses_interval = $wpdb->prepare(
			implode( ', ', array_fill( 0, count( $deletable_statuses ), '%s' ) ),
			...$deletable_statuses
		);

		/*
		 * Calculate how many of the post ID with the most digits would fit in the current database
		 * `max_allowed_packet` value with some margin.
		 */
		$max_allowed_packet = (int) ( $wpdb->get_var( 'SELECT @@max_allowed_packet' ) );
		if ( $max_allowed_packet > 1000 ) {
			// Fetch the ID with the most digits from the wp_posts table, aka the ID of the last post.
			$post_id            = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} ORDER BY ID DESC LIMIT 1" );
			$strlen             = strlen( $post_id ) || 1;
			$dynamic_batch_size = (int) floor( ( $max_allowed_packet - 1000 ) / $strlen );
		}
		// Set a default batch size of 100.
		$batch_size = empty( $dynamic_batch_size ) ? 100 : $dynamic_batch_size;
		// Set a maximum batch size of 50000.
		$batch_size = min( $batch_size, 50000 );

		/**
		 * Filters the number of records to be deleted in a single query when purging Event Aggregator records
		 * using the direct deletion method.
		 *
		 * @since 6.0.9
		 *
		 * @param int $batch_size The number of records to be deleted in a single query. Calculated from the current
		 *                        `max_allowed_packet` value set in the database.
		 */
		$batch_size = apply_filters( 'tec_event_aggregator_direct_record_deletion_batch_size', $batch_size );

		/*
		 * Get the IDs to flush the cache for a number of IDs at a time, with a reasonable chance of not running
		 * into the `max_allowed_packet` limit.
		 * Do this before the records are deleted from the database.
		 */
		$records_post_ids = (array) $wpdb->get_col(
			$wpdb->prepare(
				'SELECT ID
				FROM %1$s
				WHERE post_type = "%2$s"
				AND post_status IN ( ' . $deletable_statuses_interval . ' )
				AND post_date_gmt < "%3$s"
				ORDER BY ID DESC
				LIMIT %4$d',
				$wpdb->posts,
				Records::$post_type,
				$date_threshold,
				$batch_size
			)
		);

		foreach ( $records_post_ids as $post_id ) {
			clean_post_cache( $post_id );
		}

		// ORDER BY ID DESC is important here to make sure the run will insist on the same set of records.

		// Use a sub-query to avoid running into the max_allowed_packet limit.
		if (
			$wpdb->query(
				$wpdb->prepare(
					'DELETE FROM %1$s
					WHERE comment_post_ID IN (
						SELECT ID
						FROM %2$s
						WHERE post_type = "%3$s"
						AND post_status in ( ' . $deletable_statuses_interval . ' )
						AND post_date_gmt < "%4$s"
						ORDER BY ID DESC
					) LIMIT %5$d',
					$wpdb->comments,
					$wpdb->posts,
					Tribe__Events__Aggregator__Records::$post_type,
					$date_threshold,
					$batch_size
				)
			) === false
		) {
			tribe( 'logger' )->log_error( 'Failed to delete expired records comments using direct delete: ' . $wpdb->last_error, 'EA Cron' );
		}

		/*
		 * let's only run this when we need it.
		codecept_debug(
			$wpdb->prepare(
				'DELETE FROM %1$s
				WHERE post_id IN (
					SELECT ID
					FROM %2$s
					WHERE post_type = "%3$s"
					AND post_status in ( ' . $deletable_statuses_interval . ' )
					AND post_date_gmt < "%4$s"
					ORDER BY ID DESC
				) LIMIT %5$d',
				$wpdb->postmeta,
				$wpdb->posts,
				Tribe__Events__Aggregator__Records::$post_type,
				$date_threshold,
				$batch_size
			)
		);
		*/

		// Use a sub-query to avoid running into the max_allowed_packet limit.
		if (
			$wpdb->query(
				$wpdb->prepare(
					'DELETE FROM %1$s
					WHERE post_id IN (
						SELECT ID
						FROM %2$s
						WHERE post_type = "%3$s"
						AND post_status in ( ' . $deletable_statuses_interval . ' )
						AND post_date_gmt < "%4$s"
						ORDER BY ID DESC
					) LIMIT %5$d',
					$wpdb->postmeta,
					$wpdb->posts,
					Tribe__Events__Aggregator__Records::$post_type,
					$date_threshold,
					$batch_size
				)
			) === false
		) {
			tribe( 'logger' )->log_error( 'Failed to delete expired records postmeta using direct delete: ' . $wpdb->last_error, 'EA Cron' );
		}

		$deleted = $wpdb->query(
			$wpdb->prepare(
				'DELETE FROM %1$s
				WHERE post_type = "%2$s"
				AND post_status in ( ' . $deletable_statuses_interval . ' )
				AND post_date_gmt < "%3$s"
				ORDER BY ID DESC
				LIMIT %4$d',
				$wpdb->posts,
				Tribe__Events__Aggregator__Records::$post_type,
				$date_threshold,
				$batch_size
			)
		);

		if ( $deleted === false ) {
			tribe( 'logger' )->log_error( 'Failed to delete expired records using direct delete: ' . $wpdb->last_error, 'EA Cron' );
		}

		return $deleted;
	}
}
