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
		$valid = $this->organizer_id_list( $organizers, $sep );

		$organizers = Tribe__Utils__Array::extract_values( Tribe__Utils__Array::list_to_array( $organizers ) );

		return ! empty( $valid ) && ! empty( $organizers ) && count( $valid ) === count( $organizers );
	}

	/**
	 * Get list or array of organizer post IDs that contain valid organizer IDs.
	 *
	 * @since TBD
	 *
	 * @param  string|array $organizers A list of organizer post IDs separated by the specified separator or an array
	 *                                  of organizer post IDs.
	 * @param string        $sep        The separator used in the list to separate the organizer post IDs; ignored if
	 *                                  the input value is an array.
	 *
	 * @return array
	 */
	public function organizer_id_list( $organizers, $sep = ',' ) {
		$sep = is_string( $sep ) ? $sep : ',';

		if ( is_array( $organizers ) && Tribe__Utils__Array::is_associative( $organizers ) ) {
			// if the organizers array is associative we presume each entry will specify one or more organizer IDs
			$organizers = Tribe__Utils__Array::extract_values( $organizers );
		} else {
			$organizers = Tribe__Utils__Array::list_to_array( $organizers, $sep );
		}

		return array_filter( $organizers, array( $this, 'is_organizer_id' ) );
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
	 * @since 4.6
	 *
	 * @param int|string $event_id
	 *
	 * @return bool
	 */
	public function is_event_id( $event_id ) {
		if ( empty( $event_id ) ) {
			return false;
		}

		$event = get_post( $event_id );

		return ! empty( $event ) && Tribe__Events__Main::POSTTYPE === $event->post_type;
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

		// the post might exist but the user might be unable to access it so we query the db directly
		// auth will be handled in the endpoint
		$event_id = $this->get_id_for_slug( $event_slug, Tribe__Events__Main::POSTTYPE );

		return (bool) $event_id;
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

		// the post might exist but the user might be unable to access it so we query the db directly
		// auth will be handled in the endpoint
		$organizer_id = $this->get_id_for_slug( $organizer_slug, Tribe__Events__Organizer::POSTTYPE );

		return (bool) $organizer_id;
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

		// the post might exist but the user might be unable to access it so we query the db directly
		// auth will be handled in the endpoint
		$venue_id = $this->get_id_for_slug( $venue_slug, Tribe__Events__Venue::POSTTYPE );

		return (bool) $venue_id;
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
	 * @since TBD
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
		$valid = $this->venue_id_list( $venues, $sep );

		$venues = Tribe__Utils__Array::extract_values( Tribe__Utils__Array::list_to_array( $venues ) );

		return ! empty( $valid ) && ! empty( $venues ) && count( $valid ) === count( $venues );
	}

	/**
	 * Get list or array of venue post IDs that contains valid venue IDs.
	 *
	 * @since TBD
	 *
	 * @param  string|array $venues A list of venue post IDs separated by the specified separator or an array
	 *                                  of venue post IDs.
	 * @param string        $sep        The separator used in the list to separate the venue post IDs; ignored if
	 *                                  the input value is an array.
	 *
	 * @return array
	 */
	public function venue_id_list( $venues, $sep = ',' ) {
		$sep = is_string( $sep ) ? $sep : ',';

		if ( is_array( $venues ) && Tribe__Utils__Array::is_associative( $venues ) ) {
			// if the venues array is associative we presume each entry will specify one or more venue IDs
			$venues = Tribe__Utils__Array::extract_values( $venues );
		} else {
			$venues = Tribe__Utils__Array::list_to_array( $venues, $sep );
		}

		return array_filter( $venues, array( $this, 'is_venue_id' ) );
	}
}
