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
			$event_data[ $id ]->location->address = strip_tags( str_replace( "\n", '', tribe_get_full_address( $post->ID ) ) );
		}

		return $event_data;

	}

}
