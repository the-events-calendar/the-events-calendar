<?php

/**
 * Handles output of Google structured data markup
 */
class Tribe__Events__Pro__Google_Data_Markup__Venue extends Tribe__Events__Google_Data_Markup {

	protected $filter = 'tribe_google_venue_data';

	/**
	 * Compile the schema.org event data into an array
	 */
	protected function build_data() {

		global $post;
		$id = $post->ID;

		$lat = tribe_get_event_meta( $id, Tribe__Events__Pro__Geo_Loc::LAT );
		$lng = tribe_get_event_meta( $id, Tribe__Events__Pro__Geo_Loc::LNG );

		$venue_data = parent::build_data();

		$venue_data[ $id ]->{'@type'}    = 'Place';
		$venue_data[ $id ]->address   = strip_tags( str_replace( "\n", '', tribe_get_full_address( $post->ID ) ) );
		$venue_data[ $id ]->url       = esc_js( tribe_get_event_meta( $id, '_VenueURL', true ) );
		$venue_data[ $id ]->telephone = esc_js( tribe_get_event_meta( $id, '_VenuePhone', true ) );
		if ( $lat && $lng ) {
			$venue_data[ $id ]->geo            = new stdClass();
			$venue_data[ $id ]->geo->{'@type'} = 'GeoCoordinates';
			$venue_data[ $id ]->geo->latitude  = esc_js( $lat );
			$venue_data[ $id ]->geo->longitude = esc_js( $lng );
		}

		return $venue_data;
	}

}
