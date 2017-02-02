<?php
// Don't load directly
defined( 'WPINC' ) or die;

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
	private $limit = 5;

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
		add_action( 'init', array( $this, 'action_register_cron' ) );

		// Register the Required Cron Schedules
		add_filter( 'cron_schedules', array( $this, 'filter_add_cron_schedules' ) );

		// Check for imports on cron action
		add_action( self::$action, array( $this, 'run' ) );
		add_action( self::$single_action, array( $this, 'run' ) );

		// Decreases limit after each Request, runs late for security
		add_filter( 'pre_http_request', array( $this, 'filter_check_http_limit' ), 25, 3 );

		// Add the Actual Process to run on the Action
		add_action( 'tribe_aggregator_cron_run', array( $this, 'verify_child_record_creation' ), 5 );
		add_action( 'tribe_aggregator_cron_run', array( $this, 'verify_fetching_from_service' ), 15 );
		add_action( 'tribe_aggregator_cron_run', array( $this, 'purge_expired_records' ), 25 );
	}

	/**
	 * Frequencies in which a Scheduled import can Happen
	 *
	 * @param  array  $search  Search on existing schedules with `array_intersect_assoc`
	 *
	 * @return array|stdClass
	 */
	public function get_frequency( $search = array() ) {
		$search = wp_parse_args( $search, array() );

		/**
		 * Allow developers to filter to add or remove schedules
		 * @param array $schedules
		 */
		$found = $schedules = apply_filters( 'tribe_aggregator_record_frequency', array(
			(object) array(
				'id'       => 'on_demand',
				'interval' => false,
				'text'     => esc_html_x( 'On Demand', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'       => 'every30mins',
				'interval' => MINUTE_IN_SECONDS * 30,
				'text'     => esc_html_x( 'Every 30 Minutes', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'       => 'hourly',
				'interval' => HOUR_IN_SECONDS,
				'text'     => esc_html_x( 'Hourly', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'       => 'daily',
				'interval' => DAY_IN_SECONDS,
				'text'     => esc_html_x( 'Daily', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'       => 'weekly',
				'interval' => WEEK_IN_SECONDS,
				'text'     => esc_html_x( 'Weekly', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'       => 'monthly',
				'interval' => DAY_IN_SECONDS * 30,
				'text'     => esc_html_x( 'Monthly', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
		) );

		if ( ! empty( $search ) ) {
			$found = array();

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

		// Based on the Minutes construct a Cron
		$minutes = (int) date( 'i' );
		if ( $minutes < 15 ) {
			$date .= ':00';
		} elseif ( $minutes >= 15 && $minutes < 30 ) {
			$date .= ':15';
		}elseif ( $minutes >= 30 && $minutes < 45 ) {
			$date .= ':30';
		} else {
			$date .= ':45';
		}
		$date .= ':00';

		// Fetch the last half hour as a timestamp
		$start_timestamp = strtotime( $date );

		// Now add an action twice hourly
		wp_schedule_event( $start_timestamp, 'tribe-every15mins', self::$action );
	}

	/**
	 * Adds the Frequency to WP cron schedules
	 * Instead of having cron be scheduled to specific times, we will check every 30 minutes
	 * to make sure we can insert without having to expire cache.
	 *
	 * @param  array $schedules
	 *
	 * @return array
	 */
	public function filter_add_cron_schedules( array $schedules ) {
		// Adds the Min frequency to WordPress cron schedules
		$schedules['tribe-every15mins'] = array(
			'interval' => MINUTE_IN_SECONDS * 15,
			'display'  => esc_html_x( 'Every 15 minutes', 'aggregator schedule frequency', 'the-events-calendar' ),
		);

		return (array) $schedules;
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

			return tribe_error( 'core:aggregator:http_request-limit', array( 'request' => $request, 'url' => $url ) );
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

		$records = Tribe__Events__Aggregator__Records::instance();
		$service = tribe( 'events-aggregator.service' );

		$query = $records->query( array(
			'post_status' => Tribe__Events__Aggregator__Records::$status->schedule,
			'posts_per_page' => -1,
		) );

		if ( ! $query->have_posts() ) {
			$this->log( 'debug', 'No Records Scheduled, skipped creating childs' );
			return;
		}

		foreach ( $query->posts as $post ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );

			if ( ! $record->is_schedule_time() ) {
				$this->log( 'debug', sprintf( 'Record (%d) skipped, not scheduled time', $record->id ) );
				continue;
			}

			if ( $record->get_child_record_by_status( 'pending' ) ) {
				$this->log( 'debug', sprintf( 'Record (%d) skipped, has pending childs', $record->id ) );
				continue;
			}

			// if there are no remaining imports for today, log that and skip
			if ( $service->is_over_limit( true ) ) {
				$this->log( 'debug', sprintf( $service->get_service_message( 'error:usage-limit-exceeded' ) . ' (%1$d)', $record->id ) );
				$record->update_meta( 'last_import_status', 'error:usage-limit-exceeded' );
				continue;
			}

			// Creating the child records based on this Parent
			$child = $record->create_child_record();

			if ( ! is_wp_error( $child ) ) {
				$this->log( 'debug', sprintf( 'Record (%d), was created as a child', $child->id ) );

				// Creates on the Service a Queue to Fetch the events
				$response = $child->queue_import();

				if ( ! empty( $response->status ) ) {
					$this->log( 'debug', sprintf( '%s — %s (%s)', $response->status, $response->message, $response->data->import_id ) );

					$record->update_meta( 'last_import_status', 'success:queued' );
				} else {
					$this->log( 'debug', 'Could not create Queue on Service' );

					$record->update_meta( 'last_import_status', 'error:import-failed' );
				}
			} else {
				$this->log( 'debug', $child->get_error_message() );
				$record->update_meta( 'last_import_status', 'error:import-failed' );
			}
		}
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

		$records = Tribe__Events__Aggregator__Records::instance();

		$query = $records->query( array(
			'post_status' => Tribe__Events__Aggregator__Records::$status->pending,
			'posts_per_page' => -1,
			'order' => 'ASC',
			'meta_query' => array(
				array(
					'key' => '_tribe_aggregator_origin',
					'value' => 'csv',
					'compare' => '!=',
				),
			),
		) );

		if ( ! $query->have_posts() ) {
			$this->log( 'debug', 'No Records Pending, skipped Fetching from service' );
			return;
		}

		foreach ( $query->posts as $post ) {
			$record = $records->get_by_post_id( $post );

			// Just double Check for CSV
			if ( 'csv' === $record->origin ) {
				$this->log( 'debug', sprintf( 'Record (%d) skipped, has CSV origin', $record->id ) );
				continue;
			}

			// Open a Queue to try to process the posts
			$queue = $record->process_posts();

			if ( ! is_wp_error( $queue ) ) {
				/** @var Tribe__Events__Aggregator__Record__Queue $queue */
				$this->log( 'debug', sprintf( 'Record (%d) has processed queue ', $queue->record->id ) );

				if ( $queue instanceof Tribe__Events__Aggregator__Record__Queue ) {
					$activity = $queue->activity()->get();
				} else {
					// if fetching or on error
					$activity = $queue;
				}

				foreach ( $activity as $key => $actions ) {
					foreach ( $actions as $action => $ids ) {
						if ( empty( $ids ) ) {
							continue;
						}
						$this->log( 'debug', sprintf( "\t" . '%s — %s: %s', $key, $action, implode( ', ', $ids ) ) );
					}
				}
			} else {
				$this->log( 'debug', sprintf( 'Record (%d) — %s', $record->id, $queue->get_error_message() ) );
			}
		}
	}

	/**
	 * @since  4.3.2
	 * @return void
	 */
	public function purge_expired_records() {
		global $wpdb;

		$records = Tribe__Events__Aggregator__Records::instance();
		$statuses = Tribe__Events__Aggregator__Records::$status;

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

		$args = array(
			'post_status' => array(
				$statuses->pending,
				$statuses->success,
				$statuses->failed,
				$statuses->draft,
			),
			'date_query' => array(
				array(
					'before' => date( 'Y-m-d H:i:s', time() - $records->get_retention() ),
					'column' => 'post_date_gmt',
				),
			),
			'order' => 'ASC',
			'posts_per_page' => 100,
		);

		if ( $records_to_retain ) {
			$args['post__not_in'] = $records_to_retain;
		}

		$query = $records->query( $args );

		if ( ! $query->have_posts() ) {
			$this->log( 'debug', 'No Records over retetion limit, skipped pruning expired' );
			return;
		}

		foreach ( $query->posts as $post ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );

			if ( ! $record->has_passed_retention_time() ) {
				$this->log( 'debug', sprintf( 'Record (%d) skipped, not passed retetion time', $record->id ) );
				continue;
			}

			// Creating the child records based on this Parent
			$deleted = wp_delete_post( $record->id, true );

			if ( $deleted ) {
				$this->log( 'debug', sprintf( 'Record (%d), was pruned', $deleted->ID ) );
			} else {
				$this->log( 'debug', sprintf( 'Record (%d), was not pruned', $deleted ) );
			}
		}
	}

	/**
	 * Allows us to log if we are using WP_CLI to fire this cron task
	 *
	 * @see    http://wp-cli.org/docs/internal-api/#output
	 *
	 * @param  string $type    What kind of log is this
	 * @param  string $message message displayed
	 *
	 * @return void
	 */
	public function log( $type = 'colorize', $message = '' ) {
		// Log on our Structure
		Tribe__Main::instance()->log()->log_debug( $message, 'aggregator' );

		// Only go further if we have WP_CLI
		if ( ! class_exists( 'WP_CLI' ) ) {
			return false;
		}

		switch ( $type ) {
			case 'error':
				WP_CLI::error( $message );
				break;
			case 'warning':
				WP_CLI::warning( $message );
				break;
			case 'success':
				WP_CLI::success( $message );
				break;
			case 'debug':
				WP_CLI::debug( $message, 'aggregator' );
				break;

			case 'colorize':
			default:
				WP_CLI::log( WP_CLI::colorize( $message ) );
				break;
		}
	}
}
