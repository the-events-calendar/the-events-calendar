<?php


class Tribe__Events__REST__V1__Validator__Base
	extends Tribe__Events__Validator__Base
	implements Tribe__Events__REST__V1__Validator__Interface {

	/**
	 * The event fields that are password protected when a password is required.
	 *
	 * @since 6.8.2.1
	 *
	 * @var array
	 */
	protected const PASSWORD_PROTECTED_FIELDS = [
		'description'          => '',
		'excerpt'              => '',
		'image'                => false,
		'all_day'              => 'null',
		'cost'                 => '',
		'cost_details'         => [
			'currency_symbol'   => '',
			'currency_position' => '',
			'currency_code'     => '',
			'values'            => [],
		],
		'website'              => '',
		'show_map'             => 'null',
		'show_map_link'        => 'null',
		'hide_from_listings'   => 'null',
		'sticky'               => 'null',
		'featured'             => 'null',
		'categories'           => [],
		'tags'                 => [],
		'venue'                => [],
		'organizer'            => [],
		'ticketed'             => 'null',
		'is_virtual'           => 'null',
		'virtual_url'          => '',
		'virtual_video_source' => '',
		'attendance'           => [
			'total_attendees' => 'null',
			'checked_in'      => 'null',
			'not_checked_in'  => 'null',
		],
	];

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

	/**
	 * Checks if the user can access password-protected content.
	 *
	 * This method determines whether we need to override the regular password
	 * check in core with a filter.
	 *
	 * @since 6.5.0.1
	 *
	 * @param WP_Post         $post    Post to check against.
	 * @param WP_REST_Request $request Request data to check.
	 *
	 * @return bool True if the user can access password-protected content, otherwise false.
	 */
	public function can_access_password_content( WP_Post $post, WP_REST_Request $request ): bool {
		// It has no password, so yes.
		if ( empty( $post->post_password ) ) {
			// No filter required.
			return true;
		}

		$edit_cap = get_post_type_object( $post->post_type )->cap->edit_post;

		/*
		 * Users always gets access to password protected content in the edit
		 * context if they have the `edit_post` meta capability.
		 */
		if (
			'edit' === $request['context'] &&
			current_user_can( $edit_cap, $post->ID )
		) {
			return true;
		}

		// No password, no auth.
		if ( empty( $request['password'] ) ) {
			return false;
		}

		// Double-check the request password.
		return hash_equals( $post->post_password, $request['password'] );
	}

	/**
	 * Removes password-protected content from the response.
	 *
	 * @since 6.8.2.1
	 *
	 * @return array
	 */
	public function remove_password_protected_content( array $data ): array {
		/**
		 * Filters the password-protected fields that should be removed from the response.
		 *
		 * @since 6.8.2.1
		 *
		 * @param array $password_protected_fields The password-protected fields to remove.
		 */
		$password_protected_fields = (array) apply_filters( 'tec_events_rest_api_password_protected_fields', self::PASSWORD_PROTECTED_FIELDS );

		foreach ( $data as $key => $value ) {
			if ( isset( $password_protected_fields[ $key ] ) ) {
				$data[ $key ] = 'null' === $password_protected_fields[ $key ] ? null : $password_protected_fields[ $key ];
				continue;
			}

			if ( is_array( $value ) || is_object( $value ) ) {
				$data[ $key ] = $this->remove_password_protected_content( (array) $value );
			}
		}

		return $data;
	}
}
