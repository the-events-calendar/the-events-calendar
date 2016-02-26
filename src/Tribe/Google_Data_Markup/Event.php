<?php

/**
 * Handles output of Google structured data markup
 */
class Tribe__Events__Google_Data_Markup__Event extends Tribe__Events__Google_Data_Markup {

	protected $filter = 'tribe_google_event_data';

	/**
	 * Compile the schema.org event data into an array
	 */
	protected function build_data() {
		global $post;
		$id  = $post->ID;

		$event_data = parent::build_data();

		$event_data[ $id ]->{'@type'} = 'Event';
		$event_data[ $id ]->startDate = get_gmt_from_date( tribe_get_start_date( $post, true, Tribe__Date_Utils::DBDATETIMEFORMAT ), 'c' );
		$event_data[ $id ]->endDate   = get_gmt_from_date( tribe_get_end_date( $post, true, Tribe__Date_Utils::DBDATETIMEFORMAT ), 'c' );
		if ( tribe_has_venue( $id ) ) {
			$event_data[ $id ]->location          = new stdClass();
			$event_data[ $id ]->location->{'@type'} = 'Place';
			$event_data[ $id ]->location->name    = tribe_get_venue( $post->ID );

			if ( tribe_get_address( $post->ID ) ) {
				$event_data[ $id ]->location->address->streetAddress    = tribe_get_address();
			}

			if ( tribe_get_city( $post->ID ) ) {
				$event_data[ $id ]->location->address->addressLocality    = tribe_get_city();
			}

			if ( tribe_get_region( $post->ID ) ) {
				$event_data[ $id ]->location->address->addressRegion    = tribe_get_region();
			}

			if ( tribe_get_zip( $post->ID ) ) {
				$event_data[ $id ]->location->address->postalCode    = tribe_get_zip();
			}

			if ( tribe_get_country( $post->ID ) ) {
				$event_data[ $id ]->location->address->addressCountry    = tribe_get_country();
			}

		}

		return $event_data;

	}

}
