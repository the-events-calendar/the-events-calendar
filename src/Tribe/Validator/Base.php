<?php

/**
 * Class Tribe__Events__Validator__Base
 *
 * @since 4.6
 */
class Tribe__Events__Validator__Base extends Tribe__Validator__Base
	implements Tribe__Events__Validator__Interface {

	/**
	 * Whether the provided value is an existing Venue post ID or not.
	 *
	 * @since 4.6
	 *
	 * @param mixed $venue_id
	 *
	 * @return bool
	 */
	public function is_venue_id( $venue_id ) {
		return $this->is_numeric( $venue_id ) && tribe_is_venue( $venue_id );
	}

	/**
	 * Whether the provided value is an existing Organizer post ID or not.
	 *
	 * @since 4.6
	 *
	 * @param mixed $organizer Either an array of Organizer post IDs or a single Organizer post ID.
	 *
	 * @return bool
	 */
	public function is_organizer_id( $organizer ) {
		$organizers = array_filter( (array) $organizer, 'is_numeric' );

		if ( empty( $organizers ) || count( $organizers ) !== count( (array) $organizer ) ) {
			return false;
		}

		return count( array_filter( $organizers, 'tribe_is_organizer' ) ) === count( $organizers );
	}

	/**
	 * Whether a list or array of organizer post IDs only contains valid organizer IDs or not.
	 *
	 * @since 4.6
	 *
	 * @param  string|array $organizers A list of organizer post IDs separated by the specified separator or an array
	 *                                  of organizer post IDs.
	 * @param string        $sep        The separator used in the list to separate the organizer post IDs; ignored if
	 *                                  the input value is an array.
	 *
	 * @return bool
	 */
	public function is_organizer_id_list( $organizers, $sep = ',' ) {
		$sep        = is_string( $sep ) ? $sep : ',';
		$organizers = Tribe__Utils__Array::list_to_array( $organizers, $sep );

		$valid = array_filter( $organizers, [ $this, 'is_organizer_id' ] );

		return ! empty( $organizers ) && count( $valid ) === count( $organizers );
	}

	/**
	 * Whether the event category exists or not.
	 *
	 * @since 4.6
	 *
	 * @param mixed $category Either a single event category `term_id` or `slug` or an array of
	 *                        `term_id`s and `slug`s
	 *
	 * @return bool
	 */
	public function is_event_category( $category ) {
		return $this->is_term_of_taxonomy( $category, Tribe__Events__Main::TAXONOMY );
	}

	/**
	 * Whether the value is the post ID of an existing event or not.
	 *
	 * Note: This method validates the format and type of the ID, but does not check
	 * if the event actually exists. Existence checks should be done in the request handler
	 * to allow proper 404 responses instead of 400 validation errors.
	 *
	 * @since TBD Added WP_Error if ID is empty, or an invalid number.
	 * @since 4.6
	 *
	 * @param int|string $event_id
	 *
	 * @return bool|WP_Error True if valid format, false or WP_Error if invalid format.
	 */
	public function is_event_id( $event_id ) {
		// Check if empty, but allow 0 as a valid numeric value.
		if ( $event_id === null || $event_id === false || $event_id === '' ) {
			return new WP_Error(
				'rest_invalid_param',
				__( 'Event ID cannot be empty.', 'the-events-calendar' ),
				[ 'status' => 400 ]
			);
		}

		// Only validate that it's a numeric value, not that the event exists.
		// Existence checks are done in the request handler to allow proper 404 responses.
		if ( ! is_numeric( $event_id ) ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: The PHP data type of the invalid event ID value. */
				sprintf( __( 'Event ID must be a number, got: %s.', 'the-events-calendar' ), gettype( $event_id ) ),
				[ 'status' => 400 ]
			);
		}

		$is_event_id = true;

		/**
		 * Validator filter to define if is a valid event_id.
		 *
		 * @param bool|WP_Error $is_event_id True if valid format, false or WP_Error if invalid format.
		 * @param int|string $event_id The event ID being validated.
		 *
		 * @since 4.9.4
		 * @since TBD Use $event_id instead of $event.
		 */
		return apply_filters( 'tribe_events_validator_is_event_id', $is_event_id, $event_id );
	}

	/**
	 * Whether the value is the post name of an existing event or not.
	 *
	 * @since 4.6.8
	 *
	 * @param string $event_slug
	 *
	 * @return bool
	 */
	public function is_event_slug( $event_slug ) {
		if ( empty( $event_slug ) ) {
			return false;
		}

		$event = get_page_by_path( $event_slug, OBJECT, Tribe__Events__Main::POSTTYPE );

		return ! empty( $event ) && Tribe__Events__Main::POSTTYPE === $event->post_type;
	}

	/**
	 * Whether the value is the post name of an existing organizer or not.
	 *
	 * @since 4.6.9
	 *
	 * @param string $organizer_slug
	 *
	 * @return bool
	 */
	public function is_organizer_slug( $organizer_slug ) {
		if ( empty( $organizer_slug ) ) {
			return false;
		}

		$organizer = get_page_by_path( $organizer_slug, OBJECT, Tribe__Events__Organizer::POSTTYPE );

		return ! empty( $organizer ) && Tribe__Events__Organizer::POSTTYPE === $organizer->post_type;
	}

	/**
	 * Whether the value is the post name of an existing venue or not.
	 *
	 * @since 4.6.9
	 *
	 * @param string $venue_slug
	 *
	 * @return bool
	 */
	public function is_venue_slug( $venue_slug ) {
		if ( empty( $venue_slug ) ) {
			return false;
		}

		$venue = get_page_by_path( $venue_slug, OBJECT, Tribe__Events__Venue::POSTTYPE );

		return ! empty( $venue ) && Tribe__Events__Venue::POSTTYPE === $venue->post_type;
	}

	/**
	 * Whether the string represents a valid PHP timezone or not.
	 *
	 * @since 4.6
	 *
	 * @param string $candidate
	 *
	 * @return bool
	 */
	public function is_timezone( $candidate ) {
		return is_string( $candidate ) && Tribe__Timezones::is_valid_timezone( $candidate );
	}

	/**
	 * Whether the string is empty or represents a valid PHP timezone.
	 *
	 * @since 4.6.13
	 *
	 * @param string $candidate
	 *
	 * @return bool
	 */
	public function is_timezone_or_empty( $candidate ) {
		if ( empty( $candidate ) ) {
			return true;
		}

		return $this->is_timezone( $candidate );
	}

	/**
	 * Whether a list or array of venue post IDs only contains valid venue IDs or not.
	 *
	 * @since 4.6
	 *
	 * @param  string|array $venues A list of venue post IDs separated by the specified separator or an array
	 *                                  of venue post IDs.
	 * @param string        $sep        The separator used in the list to separate the venue post IDs; ignored if
	 *                                  the input value is an array.
	 *
	 * @return bool
	 */
	public function is_venue_id_list( $venues, $sep = ',' ) {
		$sep    = is_string( $sep ) ? $sep : ',';
		$venues = Tribe__Utils__Array::list_to_array( $venues, $sep );

		$valid = array_filter( $venues, [ $this, 'is_venue_id' ] );

		return ! empty( $venues ) && count( $valid ) === count( $venues );
	}

	/**
	 * Whether a list or array of event post IDs only contains valid event IDs or not.
	 *
	 * @since 4.6.22
	 *
	 * @param  string|array $events A list of event post IDs separated by the specified separator or an array
	 *                                  of event post IDs.
	 * @param string        $sep        The separator used in the list to separate the event post IDs; ignored if
	 *                                  the input value is an array.
	 *
	 * @return bool
	 */
	public function is_event_id_list( $events, $sep = ',' ) {
		$sep    = is_string( $sep ) ? $sep : ',';
		$events = Tribe__Utils__Array::list_to_array( $events, $sep );

		$valid = array_filter(
			$events,
			function ( $event_id ) {
				$result = $this->is_event_id( $event_id );
				// Return true only if the result is exactly true (not WP_Error).
				return $result === true;
			}
		);

		return ! empty( $events ) && count( $valid ) === count( $events );
	}

	/**
	 * Checks whether `ticketed` param is valid or not.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $value Can be true or false.
	 *
	 * @return bool|WP_Error
	 */
	public function supports_ticketed( $value ) {
		// Valid when value is false.
		if ( ! tribe_is_truthy( $value ) ) {
			return true;
		}

		// When value is true then we need to check if Event Tickets REST API is available or not.
		try {
			/** @var Tribe__Tickets__REST__V1__System $system */
			$system = tribe( 'tickets.rest-v1.system' );
		} catch ( Exception $exception ) {
			return new WP_Error( 'event-tickets-not-active', __( 'Event Tickets plugin is not activated.', 'the-events-calendar' ), [ 'status' => 400 ] );
		}

		return $system->et_rest_api_is_enabled() ? true : new WP_Error( 'event-tickets-api-not-active', __( 'Event Tickets REST API is not available.', 'the-events-calendar' ), [ 'status' => 400 ] );
	}
}
