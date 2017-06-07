<?php


class Tribe__Events__REST__V1__Endpoints__Single_Organizer
	extends Tribe__Events__REST__V1__Endpoints__Linked_Post_Base
	implements Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface {

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$organizer = get_post( $request['id'] );

		$cap = get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE )->cap->read_post;
		if ( ! ( 'publish' === $organizer->post_status || current_user_can( $cap, $request['id'] ) ) ) {
			$message = $this->messages->get_message( 'organizer-not-accessible' );

			return new WP_Error( 'organizer-not-accessible', $message, array( 'status' => 403 ) );
		}

		$data = $this->post_repository->get_organizer_data( $request['id'] );

		return is_wp_error( $data ) ? $data : new WP_REST_Response( $data );
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function GET_args() {
		return array(
			'id' => array(
				'in'                => 'path',
				'type'              => 'integer',
				'description'       => __( 'the organizer post ID', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => array( $this->validator, 'is_organizer_id' ),
			),
		);
	}

	/**
	 * Handles POST requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 * @param bool            $return_id Whether the created post ID should be returned or the full response object.
	 *
	 * @return WP_Error|WP_REST_Response|int An array containing the data on success or a WP_Error instance on failure.
	 */
	public function post( WP_REST_Request $request, $return_id = false ) {
		$postarr = array(
			'OrganizerID'   => $request['id'],
			'post_author'   => $request['author'],
			'post_date'     => Tribe__Date_Utils::reformat( $request['date'], 'Y-m-d H:i:s' ),
			'post_date_gmt' => Tribe__Timezones::localize_date( 'Y-m-d H:i:s', $request['date_utc'], 'UTC' ),
			'post_status'   => $this->scale_back_post_status( $request['status'], Tribe__Events__Main::POSTTYPE ),
			'Organizer'     => $request['organizer'],
			'Description'   => $request['description'],
			'Phone'         => $request['phone'],
			'Website'       => $request['website'],
			'Email'         => $request['email'],
			'FeaturedImage' => tribe_upload_image( $request['image'] ),
		);

		$id = Tribe__Events__Organizer::instance()->create( array_filter( $postarr ) );

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'could-not-create-organizer' );

			return new WP_Error( 'could-not-create-organizer', $message, array( 'status' => 400 ) );
		}

		return $return_id ? $id : $this->post_repository->get_organizer_data( $id );
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function POST_args() {
		return array(
			// Post fields
			'author'      => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_user_id' ) ),
			'date'        => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'date_utc'    => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'organizer'   => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'description' => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'status'      => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_post_status' ) ),
			// Organizer meta fields
			'phone'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'website'     => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_url' ) ),
			'email'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'image'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_image' ) ),
		);
	}

	/**
	 * Inserts one or more organizers.
	 *
	 * @param int|array $data Either an existing linked post ID or the linked post data or an array of the previous options.
	 *
	 * @return false|array|WP_Error `false` if the linked post data is empty, the linked post ID (in an array as requested by the
	 *                              linked posts engine) or a `WP_Error` if the linked post insertion failed.
	 */
	public function insert( $data ) {
		$data = (array) $data;

		$inserted = array();
		foreach ( $data as $entry ) {
			$organizer_id = parent::insert( $entry );

			if ( $organizer_id instanceof WP_Error ) {
				return $organizer_id;
			}

			$inserted[] = $organizer_id;
		}

		return array( $this->get_id_index() => wp_list_pluck( $inserted, $this->get_id_index() ) );
	}

	/**
	 * Returns the post type handled by this linked post endpoint.
	 *
	 * @return string
	 */
	protected function get_post_type() {
		return Tribe__Events__Main::ORGANIZER_POST_TYPE;
	}

	/**
	 * Whether the data represents a valid post type ID.
	 *
	 * @param mixed $data
	 *
	 * @return bool
	 */
	protected function is_post_type( $data ) {
		return tribe_is_organizer( $data );
	}
}
