<?php

class TribeEventsFilter_GeoLoc extends TribeEventsFilter {
	public $type = 'select';

	/**
	 * Default values for the distance filter (regardless of the actual unit of measure).
	 *
	 * @var array
	 */
	protected $distances = array( 5, 10, 25, 50, 100, 250 );


	protected function get_values() {
		$distances = array();
		$steps     = apply_filters( 'geoloc-values-for-filters', $this->distances );
		$unit      = tribe_get_option( 'geoloc_default_unit', 'miles' );

		switch ( $unit ) {
			case 'miles':
				$unit = __( 'Miles', 'tribe-events-calendar-pro' );
				break;
			case 'kms':
				$unit = __( 'Kilometers', 'tribe-events-calendar-pro' );
				break;
		}

		foreach ( $steps as $value ) {
			$distances[] = array(
				'name'  => sprintf( __( '%d %s', 'tribe-events-calendar-pro' ), $value, $unit ),
				'value' => $value,
			);
		}

		return $distances;
	}

	public function get_admin_form() {
		$title = $this->get_title_field();
		$type  = $this->get_type_field();

		return $title . $type;
	}

	protected function get_type_field() {
		$name  = $this->get_admin_field_name( 'type' );
		$field = sprintf( __( 'Type: %s %s', 'tribe-events-calendar-pro' ),
			sprintf( '<label><input type="radio" name="%s" value="select" %s /> %s</label>',
				$name,
				checked( $this->type, 'select', false ),
				__( 'Dropdown', 'tribe-events-calendar-pro' )
			),
			sprintf( '<label><input type="radio" name="%s" value="radio" %s /> %s</label>',
				$name,
				checked( $this->type, 'radio', false ),
				__( 'Radio Buttons', 'tribe-events-calendar-pro' )
			)
		);

		return '<div class="tribe_events_active_filter_type_options">' . $field . '</div>';
	}

	protected function setup_query_filters() {
		if ( $this->currentValue ) {
			add_filter( 'tribe_geoloc_geofence', array( $this, 'setup_geofence_in_query' ) );
		}
	}

	/**
	 * Alter the geofence size if necessary.
	 *
	 * Any corrections to the unit of measure that may be required will take place
	 * in TribeEventsGeoLoc::get_geofence_size().
	 *
	 * @param $distance
	 *
	 * @return mixed
	 */
	public function setup_geofence_in_query( $distance ) {
		return ! empty( $this->currentValue ) ? $this->currentValue : $distance;
	}
}
