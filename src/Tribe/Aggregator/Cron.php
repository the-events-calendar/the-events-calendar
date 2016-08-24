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
				'id'     => 'every30mins',
				'interval' => MINUTE_IN_SECONDS * 30,
				'text'  => esc_html_x( 'Every 30 minutes', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'     => 'hourly',
				'interval' => HOUR_IN_SECONDS,
				'text'  => esc_html_x( 'Hourly', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'     => 'daily',
				'interval' => DAY_IN_SECONDS,
				'text'  => esc_html_x( 'Daily', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'     => 'weekly',
				'interval' => WEEK_IN_SECONDS,
				'text'  => esc_html_x( 'Weekly', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'id'     => 'monthly',
				'interval' => DAY_IN_SECONDS * 30,
				'text'  => esc_html_x( 'Monthly', 'aggregator schedule frequency', 'the-events-calendar' ),
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
	public function filter_check_http_limit( $run = false, $request, $url ) {
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

		$service = Tribe__Events__Aggregator__Service::instance();

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
	 * @return void
	 */
	public function verify_child_record_creation() {
		$records = Tribe__Events__Aggregator__Records::instance();
		/**
		 * @todo Upon creation or modification of the schedule record, we need to store when should be the next
		 */
		$query = $records->query( array(
			'post_status' => Tribe__Events__Aggregator__Records::$status->schedule,
			'posts_per_page' => -1,
		) );

		if ( ! $query->have_posts() ) {
			return false;
		}

		foreach ( $query->posts as $post ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );
			if ( ! $record->is_schedule_time() ) {
				continue;
			}

			if ( $record->get_child_record_by_status( 'pending' ) ) {
				continue;
			}

			$child = $record->create_child_record();
			$child->queue_import();
		}
	}

	/**
	 * Checks if any record data needs to be fetched from the service, this will run on the Cron every 15m
	 *
	 * @return void
	 */
	public function verify_fetching_from_service() {
		$records = Tribe__Events__Aggregator__Records::instance();

		$query = $records->query( array(
			'post_status' => Tribe__Events__Aggregator__Records::$status->pending,
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_tribe_aggregator_origin',
					'value' => 'csv',
					'compare' => '!=',
				),
			),
		) );

		if ( ! $query->have_posts() ) {
			return false;
		}

		foreach ( $query->posts as $post ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_post_id( $post );
			if ( 'csv' === $record->origin ) {
				continue;
			}

			$record->process_posts();
		}
	}
}
