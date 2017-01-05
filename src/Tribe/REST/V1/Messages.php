<?php


class Tribe__Events__REST__V1__Messages implements Tribe__REST__Messages_Interface {

	public function __construct() {
		$this->messages = array(
			'missing-event-id'     => __( 'The event ID is missing from the request', 'the-events-calendar' ),
			'event-not-found'      => __( 'The requested post ID does not exist or is not an event', 'the-events-calendar' ),
			'event-no-venue'       => __( 'The event does not have a venue assigned', 'the-events-calendar' ),
			'event-no-organizer'   => __( 'The event does not have an organizer assigned', 'the-events-calendar' ),
			'event-not-accessible' => __( 'The requested event is not accessible', 'the-events-calendar' ),
			'venue-not-found'      => __( 'The requested post ID does not exist or is not an venue', 'the-events-calendar' ),
			'organizer-not-found'  => __( 'The requested post ID does not exist or is not an organizer', 'the-events-calendar' ),
		);
	}

	/**
	 * Returns the localized message associated with the slug.
	 *
	 * @param string $message_slug
	 *
	 * @return string
	 */
	public function get_message( $message_slug ) {
		if ( isset( $this->messages[ $message_slug ] ) ) {
			return $this->messages[ $message_slug ];
		}

		return '';
	}
}