<?php
/*-------------------------------------------------------------------------------------
* File description: Main class for Geo Location functionality
*
*
* Created by:  Daniel Dvorkin
* For:         Modern Tribe Inc . ( http://tri.be/)
*
* Date: 		9 / 18 / 12 12:31 PM
*-------------------------------------------------------------------------------------*/

class TribeEventsGeoLoc {

	const LAT     = '_VenueLat';
	const LNG     = '_VenueLng';
	const ADDRESS = '_VenueGeoAddress';


	function __construct() {
		add_action( 'tribe_events_venue_updated', array( $this, 'save_venue_geodata' ), 10, 2 );
		add_action( 'tribe_events_venue_created', array( $this, 'save_venue_geodata' ), 10, 2 );
	}

	function save_venue_geodata( $venueId, $data ) {

		$address = trim( $data["Address"] . ' ' . $data["City"] . ' ' . $data["Province"] . ' ' . $data["State"] . ' ' . $data["Zip"] . ' ' . $data["Country"] );

		if ( empty( $address ) )
			return;

		// If the address didn't change, doesn't make sense to query google again for the geo data
		if ( $address === get_post_meta( $venueId, self::ADDRESS, true ) )
			return;

		$data = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode( $address ) . "&sensor=false" );

		if ( is_wp_error( $data ) || !isset( $data["body"] ) )
			return;

		$data_arr = json_decode( $data["body"] );

		if ( !empty( $data_arr->results[0]->geometry->location->lat ) ) {
			update_post_meta( $venueId, self::LAT, $data_arr->results[0]->geometry->location->lat );
		}

		if ( !empty( $data_arr->results[0]->geometry->location->lng ) ) {
			update_post_meta( $venueId, self::LNG, $data_arr->results[0]->geometry->location->lng );
		}

		// Saving the aggregated address so we don't need to ping google on every save
		update_post_meta( $venueId, self::ADDRESS, $address );

	}


	/* Static Singleton Factory Method */
	private static $instance;

	public static function instance() {
		if ( !isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

}

TribeEventsGeoLoc::instance();