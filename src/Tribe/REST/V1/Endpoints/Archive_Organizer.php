<?php

class Tribe__Events__REST__V1__Endpoints__Archive_Organizer
	extends Tribe__Events__REST__V1__Endpoints__Archive_Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	           Tribe__Documentation__Swagger__Provider_Interface {
	/**
	 * @var array An array mapping the REST request supported query vars to the args used in a TEC WP_Query.
	 */
	protected $supported_query_vars = array(
		'page'               => 'paged',
		'per_page'           => 'posts_per_page',
		'search'             => 's',
		'event'              => 'event',
		'has_events'         => 'has_events',
		'only_with_upcoming' => 'only_with_upcoming',
		'status'             => 'post_status',
	);

	/**
	 * Tribe__Events__REST__V1__Endpoints__Archive_Organizer constructor.
	 *
	 * @since 4.6
	 *
	 * @param Tribe__REST__Messages_Interface                  $messages
	 * @param Tribe__Events__REST__Interfaces__Post_Repository $repository
	 * @param Tribe__Events__Validator__Interface              $validator
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $repository,
		Tribe__Events__Validator__Interface $validator
	) {
		parent::__construct( $messages, $repository, $validator );
		$this->post_type = Tribe__Events__Main::ORGANIZER_POST_TYPE;
	}

	/**
	 * Returns an array in the format used by Swagger 2.0
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
		return array(
			'get' => array(
				'parameters' => $this->swaggerize_args( $this->READ_args(), array( 'in' => 'query', 'default' => '' ) ),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Returns all the organizers matching the search criteria', 'the-event-calendar' ),
						'schema'      => array(
							'title' => 'organizers',
							'type'  => 'array',
							'items' => array( '$ref' => '#/definitions/Organizer' ),
						),
					),
					'400' => array(
						'description' => __( 'One or more of the specified query variables has a bad format', 'the-events-calendar' ),
					),
					'404' => array(
						'description' => __( 'No events match the query or the requested page was not found.', 'the-events-calendar' ),
					),
				),
			),
		);
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 *
	 * @since 4.6
	 */
	public function get( WP_REST_Request $request ) {
		$args = array(
			'posts_per_page' => $request['per_page'],
			'paged'          => $request['page'],
			's'              => $request['search'],
			'event'          => $request['event'],
			'has_events'     => $request['has_events'],
		);

		if ( null === $request['status'] ) {
			$cap                 = get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE )->cap->edit_posts;
			$args['post_status'] = current_user_can( $cap ) ? 'any' : 'publish';
		} else {
			$args['post_status'] = $this->filter_post_status_list( $request['status'] );
		}

		/**
		 * Filters whether only organizers with upcoming events should be shown (`true`) or not (`false`) when
		 * the request parameter `only_with_upcoming` is not explicitly set.
		 *
		 * @param bool $default_only_with_upcoming
		 *
		 * @since 4.6
		 */
		$default_only_with_upcoming = apply_filters( 'tribe_rest_organizer_default_only_with_upcoming', false );

		$only_with_upcoming = isset( $request['only_with_upcoming'] )
			? tribe_is_truthy( $request['only_with_upcoming'] )
			: $default_only_with_upcoming;
		unset( $args['only_with_upcoming'] );

		if ( ! empty( $args['s'] ) ) {
			if ( ! is_string( $args['s'] ) ) {
				$message = $this->messages->get_message( 'archive-bad-search-string' );

				return new WP_Error( 'archive-bad-search-string', $message, array( 'status' => 400 ) );
			}

			/** @var Tribe__Events__Organizer $linked_post */
			$linked_post = tribe( 'tec.linked-posts.organizer' );
			$matches     = $linked_post->find_like( $args['s'] );
			unset( $args['s'] );
			if ( ! empty( $matches ) ) {
				$args['post__in'] = $matches;
			} else {
				$message = $this->messages->get_message( 'organizer-archive-page-not-found' );

				return new WP_Error( 'organizer-archive-page-not-found', $message, array( 'status' => 404 ) );
			}
		}

		$posts_per_page = Tribe__Utils__Array::get( $args, 'posts_per_page', $this->get_default_posts_per_page() );
		$organizers         = tribe_get_organizers( $only_with_upcoming, $posts_per_page, true, $args );

		unset( $args['fields'] );

		if ( empty( $organizers ) ) {
			$message = $this->messages->get_message( 'organizer-archive-page-not-found' );

			return new WP_Error( 'organizer-archive-page-not-found', $message, array( 'status' => 404 ) );
		}

		$ids = wp_list_pluck( $organizers, 'ID' );

		$data = array( 'organizers' => array() );

		foreach ( $ids as $organizer_id ) {
			$data['organizers'][] = $this->repository->get_organizer_data( $organizer_id );
		}

		$data['rest_url'] = $this->get_current_rest_url( $args );

		$page = Tribe__Utils__Array::get( $args, 'paged', 1 );

		if ( $this->has_next( $args, $page, $only_with_upcoming ) ) {
			$data['next_rest_url'] = $this->get_next_rest_url( $data['rest_url'], $page );
		}

		if ( $this->has_previous( $page, $args, $only_with_upcoming ) ) {
			$data['previous_rest_url'] = $this->get_previous_rest_url( $data['rest_url'], $page );;
		}

		$data['total']       = $total = $this->get_total( $args, $only_with_upcoming );
		$data['total_pages'] = $this->get_total_pages( $total, $posts_per_page );

		$response = new WP_REST_Response( $data );

		if ( isset( $data['total'] ) && isset( $data['total_pages'] ) ) {
			$response->header( 'X-TEC-Total', $data['total'], true );
			$response->header( 'X-TEC-TotalPages', $data['total_pages'], true );
		}

		return $response;
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 *
	 * @since 4.5
	 */
	public function READ_args() {
		return array(
			'page'               => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'default'           => 1,
				'description'       => __( 'The archive page to return', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'per_page'           => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'sanitize_callback' => array( $this, 'sanitize_per_page' ),
				'default'           => $this->get_default_posts_per_page(),
				'description'       => __( 'The number of organizers to return on each page', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'search'             => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Organizers should contain the specified string in the title, description or custom fields', 'the-events-calendar' ),
				'type'              => 'string',
			),
			'event'              => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_id' ),
				'description'       => __( 'Organizers should be related to this event', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'has_events'         => array(
				'required'     => false,
				'description'  => __( 'Organizers should have events associated to them', 'the-events-calendar' ),
				'swagger_type' => 'boolean',
			),
			'only_with_upcoming' => array(
				'required'     => false,
				'description'  => __( 'Organizers should have upcoming events associated to them', 'the-events-calendar' ),
				'swagger_type' => 'boolean',
				'default'      => false,
			),
			'status'             => array(
				'required'          => false,
				'validate_callback' => array( $this, 'filter_post_status_list' ),
				'swagger_type'      => 'string',
				'format'            => 'string',
				'description'       => __( 'The organizer post status', 'the-events-calendar' ),
			),
		);
	}

	/**
	 * Returns the maximum number of posts per page fetched via the REST API.
	 *
	 * @return int
	 *
	 * @since 4.5
	 */
	public function get_max_posts_per_page() {
		/**
		 * Filters the maximum number of organizers per page that should be returned.
		 *
		 * @param int $per_page Default to 50.
		 */
		return apply_filters( 'tribe_rest_organizer_max_per_page', 50 );
	}

	/**
	 * Returns the total number of posts matching the request.
	 *
	 * @since 4.6
	 *
	 * @param array $args
	 * @param bool  $only_with_upcoming
	 *
	 * @return int
	 */
	protected function get_total( $args, $only_with_upcoming = false ) {
		unset( $args['posts_per_page'] );

		$this->total = tribe_get_organizers( $only_with_upcoming, - 1, true,
			array_merge( $args, array(
				'found_posts'            => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			) ) );

		return $this->total;
	}

	/**
	 * Returns the archive base REST URL
	 *
	 * @since 4.6
	 *
	 * @return string
	 */
	protected function get_base_rest_url() {
		$url = tribe_events_rest_url( 'organizers/' );

		return $url;
	}

	/**
	 * Whether there is a next page in respect to the specified one.
	 *
	 * @param array $args
	 * @param int   $page
	 *
	 * @param bool  $only_with_upcoming
	 *
	 * @return bool
	 *
	 * @since 4.6
	 */
	protected function has_next( $args, $page, $only_with_upcoming ) {
		$overrides = array(
			'paged'                  => $page + 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$per_page = Tribe__Utils__Array::get( $args, 'posts_per_page', $this->get_default_posts_per_page() );
		$overrides = array_merge( $args, $overrides );

		$next = tribe_get_organizers( $only_with_upcoming, $per_page, false, $overrides );

		return ! empty( $next );
	}

	/**
	 * Whether there is a previous page in respect to the specified one.
	 *
	 * @param int   $page
	 * @param array $args
	 * @param bool  $only_with_upcoming
	 *
	 * @return bool
	 *
	 * @since 4.5
	 */
	protected function has_previous( $page, $args, $only_with_upcoming ) {
		$overrides = array(
			'paged'                  => $page - 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$per_page = Tribe__Utils__Array::get( $args, 'posts_per_page', $this->get_default_posts_per_page() );
		$overrides = array_merge( $args, $overrides );

		$previous = tribe_get_organizers( $only_with_upcoming, $per_page, false, array_merge( $args, $overrides ) );

		return 1 !== $page && ! empty( $previous );
	}
}
