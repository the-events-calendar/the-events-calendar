<?php


class Tribe__Events__REST__V1__Endpoints__Single_Event
	extends Tribe__Events__REST__V1__Endpoints__Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__REST__Endpoints__CREATE_Endpoint_Interface,
	Tribe__REST__Endpoints__DELETE_Endpoint_Interface,
    Tribe__REST__Endpoints__UPDATE_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * @var Tribe__REST__Main
	 */
	protected $main;

	/**
	 * @var WP_REST_Request
	 */
	protected $serving;
	/**
	 * @var Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $post_repository;

	/**
	 * @var Tribe__Events__REST__V1__Validator__Interface
	 */
	protected $validator;

	/**
	 * @var Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface
	 */
	protected $venue_endpoint;

	/**
	 * @var Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface
	 */
	protected $organizer_endpoint;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Single_Event constructor.
	 *
	 * @param Tribe__REST__Messages_Interface                                    $messages
	 * @param Tribe__Events__REST__Interfaces__Post_Repository                   $post_repository
	 * @param Tribe__Events__REST__V1__Validator__Interface                      $validator
	 * @param Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface $venue_endpoint
	 * @param Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface $organizer_endpoint
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $post_repository,
		Tribe__Events__REST__V1__Validator__Interface $validator,
		Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface $venue_endpoint,
		Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface $organizer_endpoint
	) {
		parent::__construct( $messages );
		$this->post_repository = $post_repository;
		$this->validator = $validator;
		$this->venue_endpoint = $venue_endpoint;
		$this->organizer_endpoint = $organizer_endpoint;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$this->serving = $request;

		$event = get_post( $request['id'] );

		$cap = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->read_post;
		if ( ! ( 'publish' === $event->post_status || current_user_can( $cap, $request['id'] ) ) ) {
			$message = $this->messages->get_message( 'event-not-accessible' );

			return new WP_Error( 'event-not-accessible', $message, array( 'status' => 403 ) );
		}

		$data = $this->post_repository->get_event_data( $request['id'], 'single' );

		/**
		 * Filters the data that will be returned for a single event request.
		 *
		 * @since 4.5.13
		 *
		 * @param array           $data    The retrieved data.
		 * @param WP_REST_Request $request The original request.
		 */
		$data = apply_filters( 'tribe_rest_single_event_data', $data, $request );

		return is_wp_error( $data ) ? $data : new WP_REST_Response( $data );
	}

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation() {
		$GET_defaults = $DELETE_defaults = array( 'in' => 'query', 'default' => '', 'type' => 'string' );
		$POST_defaults = array( 'in' => 'formData', 'default' => '', 'type' => 'string' );
		$post_args = array_merge( $this->READ_args(), $this->CREATE_args() );

		return array(
			'get'  => array(
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Returns the data of the event with the specified post ID', 'the-event-calendar' ),
						'schema'      => array(
							'$ref' => '#/definitions/Event',
						),
					),
					'400' => array(
						'description' => __( 'The event post ID is missing.', 'the-events-calendar' ),
					),
					'403' => array(
						'description' => __( 'The event with the specified ID is not accessible.', 'the-events-calendar' ),
					),
					'404' => array(
						'description' => __( 'An event with the specified ID does not exist.', 'the-events-calendar' ),
					),
				),
			),
			'post' => array(
				'consumes' => array( 'application/x-www-form-urlencoded' ),
				'parameters' => $this->swaggerize_args( $post_args, $POST_defaults ),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Returns the data of the updated event', 'the-event-calendar' ),
						'schema'      => array(
							'$ref' => '#/definitions/Event',
						),
					),
					'201' => array(
						'description' => __( 'Returns the data of the created event', 'the-event-calendar' ),
						'schema'      => array(
							'$ref' => '#/definitions/Event',
						),
					),
					'400' => array(
						'description' => __( 'A required parameter is missing or an input parameter is in the wrong format', 'the-events-calendar' ),
					),
					'403' => array(
						'description' => __( 'The user is not authorized to create events', 'the-events-calendar' ),
					),
				),
			),
			'delete'  => array(
				'parameters' => $this->swaggerize_args( $this->DELETE_args(), $DELETE_defaults ),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Deletes an event and returns its data', 'the-event-calendar' ),
						'schema'      => array(
							'$ref' => '#/definitions/Event',
						),
					),
					'400' => array(
						'description' => __( 'The event post ID is missing or does not exist.', 'the-events-calendar' ),
					),
					'403' => array(
						'description' => __( 'The current user cannot delete the event with the specified ID.', 'the-events-calendar' ),
					),
					'410' => array(
						'description' => __( 'The event with the specified ID has been deleted already.', 'the-events-calendar' ),
					),
					'500' => array(
						'description' => __( 'The event with the specified ID could not be deleted.', 'the-events-calendar' ),
					),
				),
			),
		);
	}

	/**
	 * Provides the content of the `args` array to register the endpoint support for GET requests.
	 *
	 * @return array
	 */
	public function READ_args() {
		return array(
			'id' => array(
				'in'                => 'path',
				'type'              => 'integer',
				'description'       => __( 'the event post ID', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => array( $this->validator, 'is_event_id' ),
			),
		);
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function CREATE_args() {
		return array(
			// Post fields
			'author'             => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_user_id' ),
				'type'              => 'integer',
				'description'       => __( 'The event author ID', 'the-events-calendar' ),
			),
			'date'               => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_time' ),
				'type'              => 'string',
				'description'       => __( 'The event publication date', 'the-events-calendar' ),
			),
			'date_utc'           => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_time' ),
				'type'              => 'string',
				'description'       => __( 'The event publication date (UTC timezone)', 'the-events-calendar' ),
			),
			'title'              => array(
				'required'          => true,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'type'              => 'string',
				'description'       => __( 'The event title', 'the-events-calendar' ),
			),
			'description'        => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'type'              => 'string',
				'description'       => __( 'The event description', 'the-events-calendar' ),
			),
			'slug'               => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'type'              => 'string',
				'description'       => __( 'The event slug', 'the-events-calendar' ),
			),
			'excerpt'            => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'type'              => 'string',
				'description'       => __( 'The event excerpt', 'the-events-calendar' ),
			),
			'status'             => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_post_status' ),
				'type'              => 'string',
				'description'       => __( 'The event post status', 'the-events-calendar' ),
			),
			// Event meta fields
			'timezone'           => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_timezone' ),
				'type'              => 'string',
				'description'       => __( 'The event timezone', 'the-events-calendar' ),
			),
			'all_day'            => array(
				'required'    => false,
				'default'     => false,
				'type'        => 'boolean',
				'description' => __( 'Whether the event lasts the whole day or not', 'the-events-calendar' ),
			),
			'start_date'         => array(
				'required'          => true,
				'validate_callback' => array( $this->validator, 'is_time' ),
				'type'              => 'string',
				'description'       => __( 'The event start date and time', 'the-events-calendar' ),
			),
			'end_date'           => array(
				'required'          => true,
				'validate_callback' => array( $this->validator, 'is_time' ),
				'type'              => 'string',
				'description'       => __( 'The event end date and time', 'the-events-calendar' ),
			),
			'image'              => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_image' ),
				'type'              => 'string',
				'description'       => __( 'The event featured image ID or URL', 'the-events-calendar' ),
			),
			'cost'               => array(
				'required'     => false,
				'swagger_type' => 'string',
				'description'  => __( 'The event cost', 'the-events-calendar' ),
			),
			'website'            => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_url' ),
				'swagger_type'      => 'string',
				'description'       => __( 'The event website URL', 'the-events-calendar' ),
			),
			// Event presentation data
			'show_map'           => array(
				'required'    => false,
				'type'        => 'boolean',
				'description' => __( 'Whether the event should show a map or not', 'the-events-calendar' ),
			),
			'show_map_link'      => array(
				'required'    => false,
				'type'        => 'boolean',
				'description' => __( 'Whether the event should show a map link or not', 'the-events-calendar' ),
			),
			'hide_from_listings' => array(
				'required'    => false,
				'type'        => 'boolean',
				'description' => __( 'Whether events should be hidden in the calendar view or not', 'the-events-calendar' ),
			),
			'sticky'             => array(
				'required'    => false,
				'type'        => 'boolean',
				'description' => __( 'Whether the event should be sticky in the calendar view or not', 'the-events-calendar' ),
			),
			'featured'           => array(
				'required'    => false,
				'type'        => 'boolean',
				'description' => __( 'Whether the event should be featured on the site or not', 'the-events-calendar' ),
			),
			// Linked Posts
			'venue'              => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_venue_id_or_entry' ),
				'swagger_type'      => 'array',
				'items'             => array( 'type' => 'integer' ),
				'description'       => __( 'The event venue ID or data', 'the-events-calendar' ),
			),
			'organizer'          => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_organizer_id_or_entry' ),
				'swagger_type'      => 'array',
				'items'             => array( 'type' => 'integer' ),
				'description'       => __( 'The event organizer IDs or data', 'the-events-calendar' ),
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
	public function create( WP_REST_Request $request, $return_id = false ) {
		$this->serving = $request;

		$postarr = $this->prepare_postarr( $request );

		if ( is_wp_error( $postarr ) ) {
			return $postarr;
		}

		$id = Tribe__Events__API::createEvent( array_filter( $postarr ) );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		if ( $return_id ) {
			return $id;
		}

		$data = $this->post_repository->get_event_data( $id );

		$response = new WP_REST_Response( $data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Whether the current user can create content of the specified type or not.
	 *
	 * @return bool Whether the current user can post or not.
	 */
	public function can_create() {
		$cap = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->edit_posts;

		return current_user_can( $cap );
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
	 * Handles DELETE requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the trashed post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function delete( WP_REST_Request $request ) {
		$event_id = $request['id'];

		$event = get_post( $event_id );

		if ( 'trash' === $event->post_status ) {
			$message = $this->messages->get_message( 'event-is-in-trash' );

			return new WP_Error( 'event-is-in-trash', $message, array( 'status' => 410 ) );
		}

		/**
		 * Filters the event delete operation.
		 *
		 * Returning a non `null` value here will override the default trashing operation.
		 *
		 * @param int|bool        $deleted Whether the event was successfully deleted or not.
		 * @param WP_REST_Request $request The original API request.
		 *
		 * @since 4.6
		 */
		$deleted = apply_filters( 'tribe_events_rest_event_delete', null, $request );
		if ( null === $deleted ) {
			$deleted = wp_trash_post( $event_id );
		}

		if ( false === $deleted ) {
			$message = $this->messages->get_message( 'could-not-delete-event' );

			return new WP_Error( 'could-not-delete-event', $message, array( 'status' => 500 ) );
		}

		$data = $this->post_repository->get_event_data( $event_id );

		return is_wp_error( $data ) ? $data : new WP_REST_Response( $data );
	}

	/**
	 * Whether the current user can delete posts of the type managed by the endpoint or not.
	 *
	 * @since 4.6
	 *
	 * @return bool
	 */
	public function can_delete() {
		$cap = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->delete_posts;

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
		$this->serving = $request;

		$postarr = $this->prepare_postarr( $request );

		if ( is_wp_error( $postarr ) ) {
			return $postarr;
		}

		$id = Tribe__Events__API::updateEvent( $request['id'], array_filter( $postarr ) );

		if ( is_wp_error( $id ) ) {
			/** @var WP_Error $id */
			return $id;
		}

		if ( false === $id ) {
			$message = $this->messages->get_message( 'could-not-update-event' );

			return new WP_Error( 'could-not-update-event', $message, array( 'status' => 403 ) );
		}

		$data = $this->post_repository->get_event_data( $id );

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
		array_walk( $create_args, array( $this, 'unrequire_arg' ) );

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

	/**
	 * Prepares an array with the event data from the specified request.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error An array specifying the event data, a `WP_Error` if an error occurred.
	 */
	protected function prepare_postarr( WP_REST_Request $request ) {
		$events_cat            = Tribe__Events__Main::TAXONOMY;

		$post_date     = isset( $request['date'] )
			? Tribe__Date_Utils::reformat( $request['date'], 'Y-m-d H:i:s' )
			: false;
		$post_date_gmt = isset( $request['date_utc'] )
			? Tribe__Timezones::localize_date( 'Y-m-d H:i:s', $request['date_utc'], 'UTC' )
			: false;

		$postarr = array(
			// Post fields
			'post_author'           => $request['author'],
			'post_date'             => $post_date,
			'post_date_gmt'         => $post_date_gmt,
			'post_title'            => $request['title'],
			'post_name'             => $request['slug'],
			'post_content'          => $request['description'],
			'post_excerpt'          => $request['excerpt'],
			'post_status'           => $this->scale_back_post_status( $request['status'], Tribe__Events__Main::POSTTYPE ),
			// Event data
			'EventTimezone'         => $request['timezone'],
			'EventAllDay'           => isset( $request['all_day'] ) ? tribe_is_truthy( $request['all_day'] ) : null,
			'EventStartDate'        => $request['start_date'] ? Tribe__Date_Utils::reformat( $request['start_date'], 'Y-m-d' ) : null,
			'EventStartTime'        => $request['start_date'] ? Tribe__Date_Utils::reformat( $request['start_date'], 'H:i:s' ) : null,
			'EventEndDate'          => $request['end_date'] ? Tribe__Date_Utils::reformat( $request['end_date'], 'Y-m-d' ) : null,
			'EventEndTime'          => $request['end_date'] ? Tribe__Date_Utils::reformat( $request['end_date'], 'H:i:s' ) : null,
			'FeaturedImage'         => tribe_upload_image( $request['image'] ),
			'EventCost'             => $request['cost'],
			'EventCurrencyPosition' => tribe( 'cost-utils' )->parse_currency_position( $request['cost'] ),
			'EventCurrencySymbol'   => tribe( 'cost-utils' )->parse_currency_symbol( $request['cost'] ),
			'EventURL'              => filter_var( $request['website'], FILTER_SANITIZE_URL ),
			// Taxonomies
			'tax_input'             => array_filter( array(
				$events_cat => Tribe__Terms::translate_terms_to_ids( $request['categories'], $events_cat ),
				'post_tag'  => Tribe__Terms::translate_terms_to_ids( $request['tags'], 'post_tag' ),
			) ),
		);

		$venue = $this->venue_endpoint->insert( $request['venue'] );

		if ( is_wp_error( $venue ) ) {
			return $venue;
		}

		$postarr['venue'] = $venue;

		$organizer = $this->organizer_endpoint->insert( $request['organizer'] );

		if ( is_wp_error( $organizer ) ) {
			return $organizer;
		}

		$postarr['organizer'] = $organizer;

		// Event presentation data
		$postarr['EventShowMap']          = tribe_is_truthy( $request['show_map'] );
		$postarr['EventShowMapLink']      = tribe_is_truthy( $request['show_map_link'] );
		$postarr['EventHideFromUpcoming'] = tribe_is_truthy( $request['hide_from_listings'] ) ? 'yes' : false;
		$postarr['EventShowInCalendar']   = tribe_is_truthy( $request['sticky'] );
		$postarr['feature_event']         = tribe_is_truthy( $request['featured'] );

		// If we are scheduling an event and a date has been provided, WP requires an additional argument
		if ( ! empty( $postarr['post_status'] ) && 'future' === $postarr['post_status'] && ! empty( $postarr['post_date'] ) ) {
			$postarr['edit_date'] = true;
		}

		/**
		 * Allow filtering of $postarr data with additional $request arguments.
		 *
		 * @param array           $postarr Post array used for create/update
		 * @param WP_REST_Request $request REST request object
		 *
		 * @since 4.6
		 */
		$postarr = apply_filters( 'tribe_events_rest_event_prepare_postarr', $postarr, $request );

		return $postarr;
	}
}
