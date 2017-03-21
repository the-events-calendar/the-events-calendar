<?php


interface Tribe__Events__REST__Interfaces__Post_Repository {

	/**
	 * Returns an array representation of an event.
	 *
	 * @param int $event_id An event post ID.
	 *
	 * @return array|WP_Error Either the array representation of an event or an error object.
	 */
	public function get_event_data( $event_id );

	/**
	 * Returns an array representation of an event venue.
	 *
	 * @param int $event_or_venue_id An event or venue post ID.
	 *
	 * @return array|WP_Error Either the array representation of a venue or an error object.
	 */
	public function get_venue_data( $event_or_venue_id );

	/**
	 * Returns an array representation of an event organizer(s).
	 *
	 * @param int $event_or_organizer_id An event or organizer post ID.
	 *
	 * @return array|WP_Error Either an the array representation of an orgnanizer, an
	 *                        arrya of array representations of an event organizer or
	 *                        an error object.
	 */
	public function get_organizer_data( $event_or_organizer_id );
}