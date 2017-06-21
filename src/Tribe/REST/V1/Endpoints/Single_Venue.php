<?php

/**
 * Class Tribe__Events__REST__V1__Endpoints__Single_Venue
 *
 * @since bucket/full-rest-api
 */
class Tribe__Events__REST__V1__Endpoints__Single_Venue
	extends Tribe__Events__REST__V1__Endpoints__Linked_Post_Base
	implements Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface, Tribe__Documentation__Swagger__Provider_Interface {
	/**
	 * @var string
	 */
	protected $post_type = Tribe__Events__Main::VENUE_POST_TYPE;

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 *
	 * @since bucket/full-rest-api
	 */
	public function get( WP_REST_Request $request ) {
		$venue = get_post( $request['id'] );

		$cap = get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE )->cap->read_post;
		if ( ! ( 'publish' === $venue->post_status || current_user_can( $cap, $request['id'] ) ) ) {
			$message = $this->messages->get_message( 'venue-not-accessible' );

			return new WP_Error( 'venue-not-accessible', $message, array( 'status' => 403 ) );
		}

		$data = $this->post_repository->get_venue_data( $request['id'] );

		return is_wp_error( $data ) ? $data : new WP_REST_Response( $data );
	}

	/**
	 * Handles POST requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 * @param bool            $return_id Whether the created post ID should be returned or the full response object.
	 *
	 * @return WP_Error|WP_REST_Response|int An array containing the data on success or a WP_Error instance on failure.
	 *
	 * @since bucket/full-rest-api
	 */
	public function post( WP_REST_Request $request, $return_id = false ) {
		$post_date = isset( $request['date'] ) ? Tribe__Date_Utils::reformat( $request['date'], 'Y-m-d H:i:s' ) : false;
		$post_date_gmt = isset( $request['date_utc'] ) ? Tribe__Timezones::localize_date( 'Y-m-d H:i:s', $request['date_utc'], 'UTC' ) : false;

		$post_status = $this->scale_back_post_status( $request['status'], Tribe__Events__Main::POSTTYPE );
		$postarr     = array(
			$this->get_id_index() => $request['id'],
			'post_author'         => $request['author'],
			'post_date'           => $post_date,
			'post_date_gmt'       => $post_date_gmt,
			'post_status'         => $post_status,
			'Venue'               => $request['venue'],
			'Description'         => $request['description'],
			'Address'             => $request['address'],
			'City'                => $request['city'],
			'Country'             => $request['country'],
			'Province'            => $request['province'],
			'State'               => $request['state'],
			'StateProvince'       => $request['stateprovince'],
			'Zip'                 => $request['zip'],
			'Phone'               => $request['phone'],
			'URL'  => $request['website'],
			'FeaturedImage'       => tribe_upload_image( $request['image'] ),
		);

		$postarr = array_filter( $postarr );

		if ( isset( $request['show_map'] ) ) {
			$postarr['ShowMap'] = tribe_is_truthy( $request['show_map'] );
		}
		if ( isset( $request['show_map_link'] ) ) {
			$postarr['ShowMapLink'] = tribe_is_truthy( $request['show_map_link'] );
		}

		/**
		 * Filters whether the API should try to avoid inserting duplicate venues or not.
		 *
		 * @param bool  $avoid_duplicates
		 * @param array $postarr The venue data provided in the request.
		 *
		 * @since TBD
		 */
		$avoid_duplicates = apply_filters( 'tribe_events_rest_venue_insert_avoid_duplicates', true, $postarr );

		$id               = Tribe__Events__Venue::instance()->create( $postarr, $post_status, $avoid_duplicates );

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'could-not-create-venue' );

			return new WP_Error( 'could-not-create-venue', $message, array( 'status' => 400 ) );
		}

		if ( $return_id ) {
			return $id;
		}

		$data = $this->post_repository->get_venue_data( $id );

		$response = new WP_REST_Response( $data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link  http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 *
	 * @since bucket/full-rest-api
	 */
	public function get_documentation() {
		$GET_defaults = array( 'in' => 'query', 'default' => '', 'type' => 'string' );
		$POST_defaults = array( 'in' => 'body', 'default' => '', 'type' => 'string' );

		return array(
			'get' => array(
				'parameters' => $this->swaggerize_args( $this->GET_args(), $GET_defaults ),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Returns the data of the venue with the specified post ID', 'the-event-calendar' ),
						'schema'      => array(
							'$ref' => '#/definitions/Venue',
						),
					),
					'400' => array(
						'description' => __( 'The venue post ID is missing.', 'the-events-calendar' ),
					),
					'403' => array(
						'description' => __( 'The venue with the specified ID is not accessible.', 'the-events-calendar' ),
					),
					'404' => array(
						'description' => __( 'A venue with the specified post ID does not exist.', 'the-events-calendar' ),
					),
				),
			),
			'post' => array(
				'parameters' => $this->swaggerize_args( $this->POST_args(), $POST_defaults ),
				'responses'  => array(
					'201' => array(
						'description' => __( 'Returns the data of the created venue', 'the-event-calendar' ),
						'schema'      => array(
							'$ref' => '#/definitions/Venue',
						),
					),
					'400' => array(
						'description' => __( 'A required parameter is missing or an input parameter is in the wrong format', 'the-events-calendar' ),
					),
					'403' => array(
						'description' => __( 'The user is not authorized to create venues', 'the-events-calendar' ),
					),
				),
			),
		);
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 *
	 * @since bucket/full-rest-api
	 */
	public function GET_args() {
		return array(
			'id' => array(
				'in'                => 'path',
				'type'              => 'integer',
				'description'       => __( 'the venue post ID', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => array( $this->validator, 'is_venue_id' ),
			),
		);
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 *
	 * @since bucket/full-rest-api
	 */
	public function POST_args() {
		return array(
			// Post fields
			'author'        => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_user_id' ) ),
			'date'          => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'date_utc'      => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'venue'         => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'description'   => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'status'        => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_post_status' ) ),
			// Venue meta fields
			'show_map'      => array( 'required' => false ),
			'show_map_link' => array( 'required' => false ),
			'address'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'city'          => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'country'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'province'      => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'state'         => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'zip'           => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'phone'         => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'stateprovince' => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'website'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_url' ) ),
			'image'         => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_image' ) ),
		);
	}

	/**
	 * Returns the post type handled by this linked post endpoint.
	 *
	 * @return string
	 *
	 * @since bucket/full-rest-api
	 */
	protected function get_post_type() {
		return Tribe__Events__Main::VENUE_POST_TYPE;
	}

	/**
	 * Whether the data represents a valid post type ID.
	 *
	 * @param mixed $data
	 *
	 * @return bool
	 *
	 * @since bucket/full-rest-api
	 */
	protected function is_post_type( $data ) {
		return tribe_is_venue( $data );
	}

	/**
	 * @return bool Whether the current user can post or not.
	 */
	public function can_post() {
		$cap = get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE )->cap->edit_posts;

		return current_user_can( $cap );
	}
}
