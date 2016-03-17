<?php


class Tribe__Events__Pro__CSV_Importer__Fields {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * Singleton constructor for the class.
	 *
	 * @return Tribe__Events__Pro__CSV_Importer__Fields
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function filter_venue_array( array $venue, array $record, $venue_id, Tribe__Events__Importer__File_Importer_Venues $importer ) {
		$record_latitude  = $importer->get_value_by_key( $record, 'venue_latitude' );
		$record_longitude = $importer->get_value_by_key( $record, 'venue_longitude' );
		$overwrite_coords = empty( $record_latitude ) || empty( $record_longitude ) ? '0' : '1';

		$venue['OverwriteCoords'] = $venue_id ? get_post_meta( $venue_id, '_VenueOverwriteCoords', true ) : $overwrite_coords;
		$venue['Lat']             = $venue_id ? get_post_meta( $venue_id, '_VenueLat', true ) : $record_latitude;
		$venue['Lng']             = $venue_id ? get_post_meta( $venue_id, '_VenueLng', true ) : $record_longitude;

		return $venue;
	}
}