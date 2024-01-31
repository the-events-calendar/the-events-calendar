<?php

/**
 * Class Tribe__Events__REST__V1__Endpoints__Single_Organizer
 *
 * @since bucket/full-rest-api
 */
class Tribe__Events__REST__V1__Endpoints__Single_Organizer
	extends Tribe__Events__REST__V1__Endpoints__Linked_Post_Base
	implements Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

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
		$organizer = get_post( $request['id'] );

		$cap = get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE )->cap->read_post;
		if ( ! ( 'publish' === $organizer->post_status || current_user_can( $cap, $request['id'] ) ) ) {
			$message = $this->messages->get_message( 'organizer-not-accessible' );

			return new WP_Error( 'organizer-not-accessible', $message, [ 'status' => 403 ] );
		}

		$data = $this->post_repository->get_organizer_data( $request['id'], 'single' );

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
	public function create( WP_REST_Request $request, $return_id = false ) {
		$postarr = $this->prepare_postarr( $request );

		/**
		 * Filters whether the API should try to avoid inserting duplicate organizers or not.
		 *
		 * @param bool  $avoid_duplicates
		 * @param array $postarr The organizer data provided in the request.
		 *
		 * @since 4.6
		 */
		$avoid_duplicates = apply_filters( 'tribe_events_rest_organizer_insert_avoid_duplicates', true, $postarr );

		$id = Tribe__Events__Organizer::instance()->create( $postarr, $postarr['post_status'], $avoid_duplicates );

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'could-not-create-organizer' );

			return new WP_Error( 'could-not-create-organizer', $message, [ 'status' => 400 ] );
		}

		if ( $return_id ) {
			return $id;
		}

		$data = $this->post_repository->get_organizer_data( $id );

		$response = new WP_REST_Response( $data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Inserts one or more organizers.
	 *
	 * @param int|array $data Either an existing linked post ID or the linked post data or an array of the previous options.
	 *
	 * @return false|array|WP_Error `false` if the linked post data is empty, the linked post ID (in an array as requested by the
	 *                              linked posts engine) or a `WP_Error` if the linked post insertion failed.
	 *
	 * @since bucket/full-rest-api
	 */
	public function insert( $data ) {
		$data = (array) $data;

		$inserted = [];
		foreach ( $data as $entry ) {
			$organizer_id = parent::insert( $entry );

			if ( $organizer_id instanceof WP_Error ) {
				return $organizer_id;
			}

			$inserted[] = $organizer_id;
		}

		return [ $this->get_id_index() => wp_list_pluck( $inserted, $this->get_id_index() ) ];
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
		$get_defaults  = $delete_defaults = [ 'in' => 'query', 'default' => '', 'type' => 'string' ];
		$post_args     = array_merge( $this->READ_args(), $this->CREATE_args() );

		return [
			'get'    => [
				'parameters' => $this->swaggerize_args( $this->READ_args(), $get_defaults ),
				'responses'  => [
					'200' => [
						'description' => __( 'Returns the data of the organizer with the specified post ID', 'the-events-calendar' ),
						'content' => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Organizer',
								]
							]
						],
					],
					'400' => [
						'description' => __( 'The organizer post ID is missing.', 'the-events-calendar' ),
					],
					'403' => [
						'description' => __( 'The organizer with the specified ID is not accessible.', 'the-events-calendar' ),
					],
					'404' => [
						'description' => __( 'An organizer with the specified event does not exist.', 'the-events-calendar' ),
					],
				],
			],
			'post'   => [
				'parameters'  => $this->swaggerize_args( $this->READ_args(), [] ),
				'requestBody' => $this->swaggerize_post_args( 'application/x-www-form-urlencoded', $post_args ),
				'responses'  => [
					'201' => [
						'description' => __( 'Returns the data of the created organizer', 'the-events-calendar' ),
						'content' => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Organizer',
								]
							]
						],
					],
					'400' => [
						'description' => __( 'A required parameter is missing or an input parameter is in the wrong format', 'the-events-calendar' ),
					],
					'403' => [
						'description' => __( 'The user is not authorized to create organizers', 'the-events-calendar' ),
					],
				],
			],
			'delete' => [
				'parameters' => $this->swaggerize_args( $this->DELETE_args(), $delete_defaults ),
				'responses'  => [
					'200' => [
						'description' => __( 'Deletes an organizer and returns its data', 'the-events-calendar' ),
						'content' => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Organizer',
								]
							]
						],
					],
					'400' => [
						'description' => __( 'The organizer post ID is missing or does not exist.', 'the-venues-calendar' ),
					],
					'403' => [
						'description' => __( 'The current user cannot delete the organizer with the specified ID.', 'the-venues-calendar' ),
					],
					'410' => [
						'description' => __( 'The organizer with the specified ID has been deleted already.', 'the-venues-calendar' ),
					],
					'500' => [
						'description' => __( 'The organizer with the specified ID could not be deleted.', 'the-venues-calendar' ),
					],
				],
			],
		];
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 *
	 * @since bucket/full-rest-api
	 */
	public function READ_args() {
		return [
			'id' => [
				'in'                => 'path',
				'type'              => 'integer',
				'description'       => __( 'the organizer post ID', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_organizer_id' ],
			],
		];
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 *
	 * @since bucket/full-rest-api
	 */
	public function CREATE_args() {
		return [
			// Post fields
			'author'      => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_user_id' ],
				'type'              => 'integer',
				'default'           => null,
				'description'       => __( 'The organizer author ID', 'the-events-calendar' ),
			],
			'date'        => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_time' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer publication date', 'the-events-calendar' ),
			],
			'date_utc'    => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_time' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer publication date (UTC time zone)', 'the-events-calendar' ),
			],
			'organizer'   => [
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_string' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer name', 'the-events-calendar' ),
			],
			'description' => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_string_or_empty' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer description', 'the-events-calendar' ),
			],
			'status'      => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_post_status' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer post status', 'the-events-calendar' ),
			],
			// Organizer meta fields
			'phone'       => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_string_or_empty' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer phone number', 'the-events-calendar' ),
			],
			'website'     => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_url_or_empty' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer website', 'the-events-calendar' ),
			],
			'email'       => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_string_or_empty' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer e-mail address', 'the-events-calendar' ),
			],
			'image'       => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_image_or_empty' ],
				'type'              => 'string',
				'default'           => null,
				'description'       => __( 'The organizer featured image ID or URL', 'the-events-calendar' ),
			],
		];
	}

	/**
	 * Whether the current user can create content of the specified type or not.
	 *
	 * @return bool Whether the current user can post or not.
	 */
	public function can_create() {
		$cap = get_post_type_object( Tribe__Events__Main::ORGANIZER_POST_TYPE )->cap->edit_posts;

		return current_user_can( $cap );
	}

	/**
	 * Prepares an array with the or data from the specified request.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function prepare_postarr( WP_REST_Request $request ) {
		$post_date = isset( $request['date'] )
			? Tribe__Date_Utils::reformat( $request['date'], 'Y-m-d H:i:s' )
			: false;
		$post_date_gmt = isset( $request['date_utc'] )
			? Tribe__Timezones::localize_date( 'Y-m-d H:i:s', $request['date_utc'], 'UTC' )
			: false;

		$post_status = $this->scale_back_post_status( $request['status'], Tribe__Events__Main::POSTTYPE );
		$postarr = [
			$this->get_id_index() => $request['id'],
			'post_author'         => $request['author'],
			'post_date'           => $post_date,
			'post_date_gmt'       => $post_date_gmt,
			'post_status'         => $post_status,
			'Organizer'           => $request['organizer'],
			'Description'         => $request['description'],
			'Phone'               => $request['phone'],
			'Website'             => $request['website'],
			'Email'               => $request['email'],
			'FeaturedImage'       => tribe_upload_image( $request['image'] ),
		];

		/**
		 * Allow filtering of $postarr data with additional $request arguments.
		 *
		 * @param array           $postarr Post array used for create/update
		 * @param WP_REST_Request $request REST request object
		 *
		 * @since 4.6.9
		 */
		$postarr = apply_filters( 'tribe_events_rest_organizer_prepare_postarr', $postarr, $request );

		$postarr = array_filter( $postarr, [ $this->validator, 'is_not_null' ] );

		return $postarr;
	}

	/**
	 * Returns the post type handled by this linked post endpoint.
	 *
	 * @return string
	 *
	 * @since bucket/full-rest-api
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
	 *
	 * @since bucket/full-rest-api
	 */
	protected function is_post_type( $data ) {
		return tribe_is_organizer( $data );
	}

	/**
	 * Handles DELETE requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the trashed post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function delete( WP_REST_Request $request ) {
		$organizer_id = $request['id'];

		$organizer = get_post( $organizer_id );

		if ( 'trash' === $organizer->post_status ) {
			$message = $this->messages->get_message( 'organizer-is-in-trash' );

			return new WP_Error( 'organizer-is-in-trash', $message, [ 'status' => 410 ] );
		}

		/**
		 * Filters the organizer delete operation.
		 *
		 * Returning a non `null` value here will override the default trashing operation.
		 *
		 * @param int|bool        $deleted Whether the organizer was successfully deleted or not.
		 * @param WP_REST_Request $request The original API request.
		 *
		 * @since 4.6
		 */
		$deleted = apply_filters( 'tribe_organizers_rest_organizer_delete', null, $request );
		if ( null === $deleted ) {
			$deleted = wp_trash_post( $organizer_id );
		}

		if ( false === $deleted ) {
			$message = $this->messages->get_message( 'could-not-delete-organizer' );

			return new WP_Error( 'could-not-delete-organizer', $message, [ 'status' => 500 ] );
		}

		$data = $this->post_repository->get_organizer_data( $organizer_id );

		return is_wp_error( $data ) ? $data : new WP_REST_Response( $data );
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function DELETE_args() {
		return $this->READ_args();
	}

	/**
	 * Whether the current user can delete posts of the type managed by the endpoint or not.
	 *
	 * @since 4.6
	 *
	 * @return bool
	 */
	public function can_delete() {
		$cap = get_post_type_object( Tribe__Events__Main::ORGANIZER_POST_TYPE )->cap->delete_posts;

		return current_user_can( $cap );
	}

	/**
	 * Handles UPDATE requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the updated post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function update( WP_REST_Request $request ) {
		$postarr = $this->prepare_postarr( $request );

		$id = Tribe__Events__Organizer::instance()->update( $request['id'], $postarr );

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'could-not-update-organizer' );

			return new WP_Error( 'could-not-update-organizer', $message, [ 'status' => 400 ] );
		}

		$data = $this->post_repository->get_organizer_data( $id );

		$response = new WP_REST_Response( $data );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function EDIT_args() {
		// when editing the only required argument is the ID ('id')
		$create_args = $this->CREATE_args();
		array_walk( $create_args, [ $this, 'unrequire_arg' ] );

		return array_merge( $this->READ_args(), $create_args );
	}

	/**
	 * Whether the current user can update content of this type or not.
	 *
	 * @since 4.6
	 *
	 * @return bool Whether the current user can update or not.
	 */
	public function can_edit() {
		return $this->can_create();
	}
}
