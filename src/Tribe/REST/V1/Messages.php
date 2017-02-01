<?php


class Tribe__Events__REST__V1__Messages implements Tribe__REST__Messages_Interface {

	/**
	 * @var string
	 */
	protected $message_prefix = 'rest-v1:';

	public function __construct() {
		$this->messages = array(
			'missing-event-id'                => __( 'The event ID is missing from the request', 'the-events-calendar' ),
			'event-not-found'                 => __( 'The requested post ID does not exist or is not an event', 'the-events-calendar' ),
			'event-no-venue'                  => __( 'The event does not have a venue assigned', 'the-events-calendar' ),
			'event-no-organizer'              => __( 'The event does not have an organizer assigned', 'the-events-calendar' ),
			'event-not-accessible'            => __( 'The requested event is not accessible', 'the-events-calendar' ),
			'event-archive-bad-page'          => __( "The 'page' parameter must be a positive integer greater than 1", 'the-events-calendar' ),
			'event-archive-bad-per-page'      => __( "The 'per_page' parameter must be a positive integer greater than 1", 'the-events-calendar' ),
			'event-archive-bad-start-date'    => __( "The 'start_date' parameter must be in a supported format", 'the-events-calendar' ),
			'event-archive-bad-end-date'      => __( "The 'end_date' parameter must be in a supported format", 'the-events-calendar' ),
			'event-archive-bad-search-string' => __( "The 'search' parameter must be a string", 'the-events-calendar' ),
			'event-archive-bad-categories'      => __( "The 'categories' parameter contains invalid category slugs or IDs", 'the-events-calendar' ),
			'event-archive-bad-tags'           => __( "The 'tags' parameter contains invalid tag slugs or IDs", 'the-events-calendar' ),
			'event-archive-page-not-found'    => __( 'The requested event archive page does not exist', 'the-events-calendar' ),
			'venue-not-found'                 => __( 'The requested post ID does not exist or is not an venue', 'the-events-calendar' ),
			'organizer-not-found'             => __( 'The requested post ID does not exist or is not an organizer', 'the-events-calendar' ),
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

	/**
	 * Returns the associative array of all the messages handled by the class.
	 *
	 * @return array An associative array in the `[ <slug> => <localized message> ]` format.
	 */
	public function get_messages() {
		return $this->messages;
	}

	/**
	 * Prefixes a message slug with a common root.
	 *
	 * Used to uniform the slug format to the one used by the `Tribe__Events__Aggregator__Service` class.
	 *
	 * @see Tribe__Events__Aggregator__Service::register_messages()
	 *
	 * @param string $message_slug
	 *
	 * @return string The prefixed message slug.
	 */
	public function prefix_message_slug( $message_slug ) {
		return $this->message_prefix . $message_slug;
	}
}
