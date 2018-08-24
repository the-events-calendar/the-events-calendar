<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__API__Origins extends Tribe__Events__Aggregator__API__Abstract {

	/**
	 * @var array
	 */
	public $origins;

	/**
	 * @var bool Whether EA is enabled or not.
	 *           While EA might be ready to work on a license and functional level
	 *           the user might disable it; this flag tracks that choice.
	 */
	protected $is_ea_disabled = true;

	/**
	 * @var array An array of origins that will still be available when EA has
	 *            been disabled by the user.
	 */
	protected $available_when_disabled = array( 'csv' );

	public function __construct() {
		parent::__construct();

		$this->origins = array(
			'csv' => (object) array(
				'id' => 'csv',
				'name' => __( 'CSV File', 'the-events-calendar' ),
				'disabled' => false,
			),
			'eventbrite' => (object) array(
				'id' => 'eventbrite',
				'name' => __( 'Eventbrite', 'the-events-calendar' ),
				'disabled' => true,
				'upsell' => true,
			),
			'gcal' => (object) array(
				'id' => 'gcal',
				'name' => __( 'Google Calendar', 'the-events-calendar' ),
				'disabled' => true,
				'upsell' => true,
			),
			'ical' => (object) array(
				'id' => 'ical',
				'name' => __( 'iCalendar', 'the-events-calendar' ),
				'disabled' => true,
				'upsell' => true,
			),
			'ics' => (object) array(
				'id' => 'ics',
				'name' => __( 'ICS File', 'the-events-calendar' ),
				'disabled' => true,
				'upsell' => true,
			),
			'meetup' => (object) array(
				'id' => 'meetup',
				'name' => __( 'Meetup', 'the-events-calendar' ),
				'disabled' => true,
				'upsell' => true,
			),
			'url' => (object) array(
				'id' => 'url',
				'name' => __( 'Other URL (beta)', 'the-events-calendar' ),
				'disabled' => true,
				'upsell' => true,
			),
		);

		$this->is_ea_disabled = tribe_get_option( 'tribe_aggregator_disable', false );
	}

	/**
	 * Get event-aggregator origins
	 *
	 * @return array
	 */
	public function get() {
		if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
			$this->enable_service_origins();
		}

		$origins = $this->origins;
		$origins = array_filter( $origins, array( $this, 'is_origin_available' ) );

		/**
		 * The origins (sources) that EA can import from
		 *
		 * @param array $origins The origins
		 */
		$origins = apply_filters( 'tribe_aggregator_origins', $origins );

		return $origins;
	}

	/**
	 * Get event-aggregator origins from the service or cache
	 *
	 * @return array
	 */
	private function enable_service_origins() {
		$cached_origins = get_transient( "{$this->cache_group}_origins" );
		if ( $cached_origins ) {
			$this->origins = $cached_origins;
			return $this->origins;
		}

		$service_origins = $this->fetch_origin_data();

		if ( is_wp_error( $service_origins ) ) {
			return $this->origins;
		}

		if ( empty( $service_origins->origin ) ) {
			return $this->origins;
		}

		// enable the options for any that come back from the Service
		foreach ( $service_origins->origin as $origin ) {
			if ( ! empty( $this->origins[ $origin->id ] ) ) {
				$this->origins[ $origin->id ]->disabled = false;
			}
		}

		// use the specified expiration if available
		if ( isset( $this->origins->expiration ) ) {
			$expiration = $this->origins->expiration;
			unset( $this->origins->expiration );
		} else {
			$expiration = 6 * HOUR_IN_SECONDS;
		}

		set_transient( "{$this->cache_group}_origins", $this->origins, $expiration );

		return $this->origins;
	}

	/**
	 * Fetches origin data from the service and sets necessary transients
	 */
	private function fetch_origin_data() {
		$cached = tribe_get_var( 'events-aggregator.origins-data' );
		if ( empty( $cached ) ) {
			// try to see if we have a lock in place
			$cached = get_transient( "{$this->cache_group}_fetch_lock" );
		}

		if ( ! empty( $cached ) ) {
			return $cached;
		}

		list( $origin_data, $error ) = $this->service->get_origins( true );
		$origin_data = (object) $origin_data;

		if ( empty( $error ) ) {
			if ( ! get_transient( "{$this->cache_group}_origin_oauth" ) && ! empty( $origin_data->oauth ) ) {
				set_transient( "{$this->cache_group}_origin_oauth", $origin_data->oauth, 6 * HOUR_IN_SECONDS );
			}

			if ( ! get_transient( "{$this->cache_group}_origin_limit" ) && ! empty( $origin_data->limit ) ) {
				set_transient( "{$this->cache_group}_origin_limit", $origin_data->limit, 6 * HOUR_IN_SECONDS );
			}
		} elseif ( 403 == wp_remote_retrieve_response_code( $error ) ) {
			// store the origins data for 5' only
			$origin_data->expiration = 300;
			// and avoid bugging the service for 5'
			set_transient( "{$this->cache_group}_fetch_lock", $origin_data, 300 );
		}

		tribe_set_var( 'events-aggregator.origins-data', $origin_data );

		return $origin_data;

	}

	/**
	 * Returns whether oauth for a given origin is enabled
	 *
	 * @param string $origin Origin
	 *
	 * @return boolean
	 */
	public function is_oauth_enabled( $origin ) {

		if (  'eventbrite' !== $origin && ! tribe( 'events-aggregator.main' )->is_service_active() ) {
			return false;
		}

		if (  'eventbrite' === $origin && class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ) ) {
			return true;
		}

		$cached_oauth_settings = get_transient( "{$this->cache_group}_origin_oauth" );
		if ( $cached_oauth_settings && isset( $cached_oauth_settings->$origin ) ) {
			return (bool) $cached_oauth_settings->$origin;
		}

		$service_origins = $this->fetch_origin_data();

		if ( ! isset( $service_origins->oauth->$origin ) ) {
			return false;
		}

		return (bool) $service_origins->oauth->$origin;
	}

	/**
	 * Get origin limit values
	 *
	 * @param string $type Type of limit to retrieve
	 *
	 * @return int
	 */
	public function get_limit( $type = 'import' ) {
		$cached_limit_settings = get_transient( "{$this->cache_group}_origin_limit" );
		if ( $cached_limit_settings && isset( $cached_limit_settings->$type ) ) {
			return (int) $cached_limit_settings->$type;
		}

		$service_origins = $this->fetch_origin_data();

		if ( ! isset( $service_origins->limit->$type ) ) {
			return false;
		}

		return (int) $service_origins->limit->$type;
	}

	public function get_name( $id ) {
		$this->get();

		if ( empty( $this->origins[ $id ] ) ) {
			return __( 'Event Aggregator', 'the-events-calendar' );
		}
		return $this->origins[ $id ]->name;
	}

	/**
	 * Whether an origin is available or not in respect to the user possibility
	 * to disable EA functions.
	 *
	 * @param stdClass|string $origin The origin to check for availability as an object
	 *                                or a slug.
	 *
	 * @return bool
	 */
	public function is_origin_available( $origin ) {
		if ( is_object( $origin ) ) {
			$origin = $origin->id;
		}

		return $this->is_ea_disabled ? in_array( $origin, $this->available_when_disabled ) : true;
	}
}
