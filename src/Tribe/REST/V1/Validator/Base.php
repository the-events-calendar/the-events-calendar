<?php


class Tribe__Events__REST__V1__Validator__Base
	extends Tribe__Events__Validator__Base
	implements Tribe__Events__REST__V1__Validator__Interface {

	/**
	 * Determine if a value is a Venue ID, entry, or empty.
	 *
	 * @since 4.6.20
	 *
	 * @param string|array $venue Venue ID or entry.
	 *
	 * @return bool Whether a value is a Venue ID, entry, or empty.
	 */
	public function is_venue_id_or_entry_or_empty( $venue ) {
		return $this->is_linked_post_id_or_entry_or_empty( 'venue', $venue );
	}

	/**
	 * Determine if a value is a Organizer ID, entry, or empty.
	 *
	 * @since 4.6.20
	 *
	 * @param string|array $organizer Organizer ID or entry.
	 *
	 * @return bool Whether a value is a Organizer ID, entry, or empty.
	 */
	public function is_organizer_id_or_entry_or_empty( $organizer ) {
		return $this->is_linked_post_id_or_entry_or_empty( 'organizer', $organizer );
	}

	/**
	 * Determine if a value is a post ID or entry.
	 *
	 * @since 6.2.0
	 *
	 * @param string $type Type of linked post to check.
	 * @param string|array $linked_post Post ID or data.
	 *
	 * @return bool
	 */
	public function is_linked_post_id_or_entry( $type, $linked_post ) {
		$tribe_is_function = 'tribe_is_' . $type;
		$rest_endpoint     = 'single-' . $type;

		if ( ! is_array( $linked_post ) ) {
			$items = preg_split( '/\\s*,\\s*/', $linked_post );
			$numeric = array_filter( $items, 'is_numeric' );
			$filtered = array_filter( $numeric, $tribe_is_function );

			return count( $filtered ) === count( $items );
		}

		$is_associative_array = is_array( $linked_post ) && ( array_values( $linked_post ) !== $linked_post );
		if ( $is_associative_array ) {
			$linked_posts = [ $linked_post ];
		} else {
			$linked_posts = (array) $linked_post;
		}

		foreach ( $linked_posts as $entry ) {
			if ( $this->is_numeric( $entry ) ) {
				if ( ! $tribe_is_function( $entry ) ) {
					return false;
				}
				continue;
			}

			if ( ! empty( $entry['id'] ) ) {
				if ( $tribe_is_function( $entry['id'] ) ) {
					continue;
				}

				return false;
			}

			$is_associative_array = is_array( $entry ) && ( array_values( $entry ) !== $entry );
			if ( ! $is_associative_array ) {
				return false;
			}

			$request = new WP_REST_Request();
			/** @var Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface $endpoint */
			$endpoint = tribe( 'tec.rest-v1.endpoints.' . $rest_endpoint );

			$request->set_attributes( [ 'args' => $endpoint->CREATE_args() ] );
			foreach ( $entry as $key => $value ) {
				$request->set_param( $key, $value );
			}

			$has_valid_params = $request->has_valid_params();

			if ( true !== $has_valid_params ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if a value is a post ID, entry, or empty.
	 *
	 * @since 6.2.0
	 *
	 * @param string $type Type of linked post to check.
	 * @param string|array $linked_post Post ID or data.
	 *
	 * @return bool
	 */
	public function is_linked_post_id_or_entry_or_empty( $type, $linked_post ) {
		if ( empty( $linked_post ) ) {
			return true;
		}

		if ( is_array( $linked_post ) ) {
			$check_if_empty = array_filter( $linked_post );

			if ( empty( $check_if_empty ) ) {
				return true;
			}
		}

		return $this->is_linked_post_id_or_entry( $type, $linked_post );
	}
}
