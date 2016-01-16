<?php

/**
 * Handles output of Google structured data markup
 */
class Tribe__Events__Pro__Google_Data_Markup__Organizer extends Tribe__Events__Google_Data_Markup {

	protected $filter = 'tribe_google_organizer_data';

	/**
	 * Compile the schema.org event data into an array
	 */
	protected function build_data() {

		global $post;
		$id = $post->ID;

		$organizer_data = parent::build_data();

		$organizer_data[ $id ]->{'@type'}    = 'Person';
		$organizer_data[ $id ]->telephone = esc_js( tribe_get_event_meta( $id, '_OrganizerPhone', true ) );
		$organizer_data[ $id ]->url       = esc_js( tribe_get_event_meta( $id, '_OrganizerWebsite', true ) );
		$organizer_data[ $id ]->email     = esc_js( tribe_get_event_meta( $id, '_OrganizerEmail', true ) );

		return $organizer_data;
	}

}
