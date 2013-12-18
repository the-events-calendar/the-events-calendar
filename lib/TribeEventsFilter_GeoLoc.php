<?php

class TribeEventsFilter_GeoLoc extends TribeEventsFilter {
	public $type = 'select';

	protected function get_values() {
		$distances = array(
			'5'   => '5 miles',
			'10'  => '10 miles',
			'25'  => '25 miles',
			'50'  => '50 miles',
			'100' => '100 miles',
			'250' => '250 miles',
		);
		$distances = apply_filters( 'geoloc-values-for-filters', $distances );

		$distances_values = array();
		foreach ( $distances as $value => $name ) {
			$distances_values[] = array(
				'name'  => $name,
				'value' => $value,
			);
		}

		return $distances_values;
	}


	public function get_admin_form() {
		$title = $this->get_title_field();
		$type = $this->get_type_field();
		return $title.$type;
	}

	protected function get_type_field() {
		$name = $this->get_admin_field_name('type');
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
		return '<div class="tribe_events_active_filter_type_options">'.$field.'</div>';
	}

	protected function setup_query_filters() {
		if ( $this->currentValue ) {
			add_filter( 'tribe_geoloc_geofence', array( $this, 'setup_geofence_in_query' ) );
		}
	}

	/**
	 * If the user selected a geofence in the Filters Bar add-on, use it for the query filter.
	 * @param $distance
	 *
	 * @return mixed
	 */
	public function setup_geofence_in_query( $distance ) {
		if ( !empty( $this->currentValue ) ) {
			$distance = tribe_convert_units( $this->currentValue, 'miles', 'kms' );
		}
		return $distance;
	}
}
