<?php

/**
 * Class TribeEventsImporter_FileImporter_Venues
 */
class TribeEventsImporter_FileImporter_Venues extends TribeEventsImporter_FileImporter {

	protected $required_fields = array( 'venue_name' );

	protected function match_existing_post( array $record ) {
		$name = $this->get_value_by_key( $record, 'venue_name' );
		$id   = $this->find_matching_post_id( $name, TribeEvents::VENUE_POST_TYPE );

		return $id;
	}

	protected function update_post( $post_id, array $record ) {
		$venue = $this->build_venue_array( $record );
		TribeEventsAPI::updateVenue( $post_id, $venue );
	}

	protected function create_post( array $record ) {
		$venue = $this->build_venue_array( $record );
		$id    = TribeEventsAPI::createVenue( $venue );

		return $id;
	}

	private function build_venue_array( array $record ) {
		$venue_address = trim( $this->get_value_by_key( $record, 'venue_address' ) . ' ' . $this->get_value_by_key( $record, 'venue_address2' ) );
		$venue         = array(
			'Venue'    => $this->get_value_by_key( $record, 'venue_name' ),
			'Address'  => $venue_address,
			'City'     => $this->get_value_by_key( $record, 'venue_city' ),
			'Country'  => $this->get_value_by_key( $record, 'venue_country' ),
			'Province' => $this->get_value_by_key( $record, 'venue_state' ),
			'State'    => $this->get_value_by_key( $record, 'venue_state' ),
			'Zip'      => $this->get_value_by_key( $record, 'venue_zip' ),
			'Phone'    => $this->get_value_by_key( $record, 'venue_phone' ),
		);
		if ( empty( $venue['Country'] ) ) {
			$venue['Country'] = 'United States';
		}

		return $venue;
	}

}
