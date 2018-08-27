<?php


class Tribe__Events__REST__V1__Messages implements Tribe__REST__Messages_Interface {

	/**
	 * @var string
	 */
	protected $message_prefix = 'rest-v1:';

	public function __construct() {
		$this->messages = array(
			'missing-event-id'                 => __( 'The event ID is missing from the request', 'the-events-calendar' ),
			'event-not-found'                  => __( 'The requested post ID does not exist or is not an event', 'the-events-calendar' ),
			'event-no-venue'                   => __( 'The event does not have a venue assigned', 'the-events-calendar' ),
			'event-no-organizer'               => __( 'The event does not have an organizer assigned', 'the-events-calendar' ),
			'event-not-accessible'             => __( 'The requested event is not accessible', 'the-events-calendar' ),
			'event-archive-bad-page'           => __( "The 'page' parameter must be a positive integer greater than 1", 'the-events-calendar' ),
			'event-archive-bad-per-page'       => __( "The 'per_page' parameter must be a positive integer greater than 1", 'the-events-calendar' ),
			'event-archive-bad-start-date'     => __( "The 'start_date' parameter must be in a supported format", 'the-events-calendar' ),
			'event-archive-bad-end-date'       => __( "The 'end_date' parameter must be in a supported format", 'the-events-calendar' ),
			'event-archive-bad-search-string'  => __( "The 'search' parameter must be a string", 'the-events-calendar' ),
			'event-archive-bad-categories'     => __( "The 'categories' parameter contains invalid category slugs or IDs", 'the-events-calendar' ),
			'event-archive-bad-tags'           => __( "The 'tags' parameter contains invalid tag slugs or IDs", 'the-events-calendar' ),
			'event-archive-page-not-found'     => __( 'The requested event archive page does not exist', 'the-events-calendar' ),
			'venue-not-found'                  => __( 'The requested post ID does not exist or is not an venue', 'the-events-calendar' ),
			'organizer-not-found'              => __( 'The requested post ID does not exist or is not an organizer', 'the-events-calendar' ),
			'could-not-create-venue'           => __( 'The venue could not be created from the provided data', 'the-events-calendar' ),
			'could-not-create-organizer'       => __( 'The organizer could not be created from the provided data', 'the-events-calendar' ),
			'invalid-linked-post-id'           => __( 'The provided linked post ID is not valid', 'the-events-calendar' ),
			'venue-not-accessible'             => __( 'The requested venue is not accessible', 'the-events-calendar' ),
			'organizer-not-accessible'         => __( 'The requested organizer is not accessible', 'the-events-calendar' ),
			'could-not-delete-event'           => __( 'The event could not be deleted', 'the-events-calendar' ),
			'event-is-in-trash'                => __( 'The event is already trashed', 'the-events-calendar' ),
			'could-not-delete-venue'           => __( 'The venue could not be deleted', 'the-venues-calendar' ),
			'venue-is-in-trash'                => __( 'The venue is already trashed', 'the-venues-calendar' ),
			'could-not-delete-organizer'       => __( 'The organizer could not be deleted', 'the-organizers-calendar' ),
			'organizer-is-in-trash'            => __( 'The organizer is already trashed', 'the-organizers-calendar' ),
			'could-not-update-event'           => __( 'The event could not be updated', 'the-events-calendar' ),
			'could-not-update-venue'           => __( 'The venue could not be updated', 'the-venues-calendar' ),
			'could-not-update-organizer'       => __( 'The organizer could not be updated', 'the-organizers-calendar' ),
			'venue-archive-page-not-found'     => __( 'The requested venue archive page does not exist', 'the-venues-calendar' ),
			'organizer-archive-page-not-found' => __( 'The requested organizer archive page does not exist', 'the-organizers-calendar' ),
			'archive-bad-search-string'        => __( "The 'search' parameter must be a string", 'the-venues-calendar' ),
			'term-archive-page-not-found'  => __( 'The requested event term archive page does not exist', 'the-events-calendar' ),
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
