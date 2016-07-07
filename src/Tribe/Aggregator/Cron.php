<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Cron {
	/**
	 * Action where the cron will run
	 * @var string
	 */
	public static $action = 'tribe_ea_cron';

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
		add_action( self::$cron_action, array( $this, 'action_check_scheduled_imports' ) );
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

		$schedules = array(
			(object) array(
				'name'     => 'hourly',
				'interval' => HOUR_IN_SECONDS,
				'display'  => esc_html_x( 'Hourly', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'name'     => 'daily',
				'interval' => DAY_IN_SECONDS,
				'display'  => esc_html_x( 'Daily', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'name'     => 'weekly',
				'interval' => WEEK_IN_SECONDS,
				'display'  => esc_html_x( 'Weekly', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'name'     => 'monthly',
				'interval' => DAY_IN_SECONDS * 30,
				'display'  => esc_html_x( 'Monthly', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
		);

		/**
		 * Allow developers to filter to add or remove schedules
		 * @param array $schedules
		 */
		$schedules = array_merge( array(
			(object) array(
				'name'     => 'every15mins',
				'interval' => MINUTE_IN_SECONDS * 15,
				'display'  => esc_html_x( 'Every 15 minutes', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
			(object) array(
				'name'     => 'every30mins',
				'interval' => MINUTE_IN_SECONDS * 30,
				'display'  => esc_html_x( 'Every 30 minutes', 'aggregator schedule frequency', 'the-events-calendar' ),
			),
		), apply_filters( 'tribe_ea_record_frequency', $schedules ) );

		$found = $schedules;

		if ( ! empty( $search ) ){
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
		if ( wp_next_scheduled( self::$cron_action ) ) {
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
		wp_schedule_event( $start_timestamp, 'every15mins', self::$cron_action );
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
		// Fetch the 15mins frequency
		$frequency = $this->get_frequency( 'name=every15mins' );

		// Adds the Min frequency to WordPress cron schedules
		$schedules[ $frequency->name ] = (array) $frequency;

		return (array) $schedules;
	}


}