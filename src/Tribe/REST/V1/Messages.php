<?php


class Tribe__Events__REST__V1__Messages implements Tribe__REST__Messages_Interface {

	public function __construct() {
		$this->messages = array(
			'missing-event-id'     => __( 'The event ID is missing from the request', 'the-events-calendar' ),
			'event-not-found'      => __( 'The requested post ID does not exist or is not an event', 'the-events-calendar' ),
			'event-not-accessible' => __( 'The requested event is not accessible', 'the-events-calendar' ),
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