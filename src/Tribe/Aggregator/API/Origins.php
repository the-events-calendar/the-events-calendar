<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__API__Origins extends Tribe__Events__Aggregator__API__Abstract {
	public $origins;

	public function __construct() {
		parent::__construct();

		$this->origins = array(
			'csv' => (object) array(
				'id' => 'csv',
				'name' => __( 'CSV File', 'the-events-calendar' ),
				'disabled' => false,
			),
			'facebook' => (object) array(
				'id' => 'facebook',
				'name' => __( 'Facebook', 'the-events-calendar' ),
				'disabled' => true,
			),
			'gcal' => (object) array(
				'id' => 'gcal',
				'name' => __( 'Google Calendar', 'the-events-calendar' ),
				'disabled' => true,
			),
			'ical' => (object) array(
				'id' => 'ical',
				'name' => __( 'iCalendar', 'the-events-calendar' ),
				'disabled' => true,
			),
			'ics' => (object) array(
				'id' => 'ics',
				'name' => __( 'ICS File', 'the-events-calendar' ),
				'disabled' => true,
			),
			'meetup' => (object) array(
				'id' => 'meetup',
				'name' => __( 'Meetup', 'the-events-calendar' ),
				'disabled' => true,
			),
		);
	}

	/**
	 * Get event-aggregator origins
	 *
	 * @return array
	 */
	public function get() {
		if ( Tribe__Events__Aggregator::instance()->is_service_active() ) {
			$this->enable_service_origins();
		}

		return apply_filters( 'tribe_aggregator_origins', $this->origins );
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

		$service_origins = $this->service->get_origins();

		if ( is_wp_error( $service_origins ) ) {
			return $this->origins;
		}

		// enable the options for any that come back from the Service
		foreach ( $service_origins as $origin ) {
			if ( ! empty( $this->origins[ $origin->id ] ) ) {
				$this->origins[ $origin->id ]->disabled = false;
			}
		}
		set_transient( "{$this->cache_group}_origins", $this->origins, 6 * HOUR_IN_SECONDS );

		return $this->origins;
	}

	public function get_name( $id ) {
		$this->get();

		if ( empty( $this->origins[ $id ] ) ) {
			return __( 'Event Aggregator', 'the-events-calendar' );
		}
		return $this->origins[ $id ]->name;
	}
}
