<?php


class Tribe__Events__REST__V1__Endpoints__Archive_Event
	extends Tribe__Events__REST__V1__Endpoints__Base
	implements Tribe__REST__Endpoints__GET_Endpoint_Interface, Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * @var Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	/**
	 * @var array An array mapping the REST request supported query vars to the args used in a TEC WP_Query.
	 */
	protected $supported_query_vars = array(
		'page'       => 'paged',
		'per_page'   => 'posts_per_page',
		'start_date' => 'start_date',
		'end_date'   => 'end_date',
		'search'     => 's',
		'categories' => 'categories',
		'tags'       => 'tags',
		'venue'      => 'venue',
		'organizer'  => 'organizer',
		'featured'   => 'featured',
	);

	/**
	 * @var int The total number of events according to the current request parameters and user access rights.
	 */
	protected $total;

	/**
	 * @var Tribe__Validator__Interface
	 */
	protected $validator;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Archive_Event constructor.
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
		parent::__construct( $messages );
		$this->repository = $repository;
		$this->validator = $validator;
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$defaults = array( 'posts_per_page' => get_option( 'posts_per_page' ) );

		$args = array();

		try {
			$args['paged']          = $this->parse_page( $request );
			$args['posts_per_page'] = $this->parse_per_page( $request );
			$args['start_date']     = $this->parse_start_date( $request );
			$args['end_date']       = $this->parse_end_date( $request );
			$args['s']              = $this->parse_search( $request );

			$args['meta_query'] = array_filter( array(
				$this->parse_meta_query_entry( $request, 'venue', '_EventVenueID', '=', 'NUMERIC' ),
				$this->parse_meta_query_entry( $request, 'organizer', '_EventOrganizerID', '=', 'NUMERIC' ),
				$this->parse_featured_meta_query_entry( $request ),
			) );

			$args['tax_query'] = array_filter( array(
				$this->parse_categories( $request ),
				$this->parse_tags( $request ),
			) );

			$args = array_filter( wp_parse_args( $args, $defaults ) );

			$data = array( 'events' => array() );

			$data['rest_url'] = $this->get_current_rest_url( $args );

			if ( ! isset( $args['posts_per_page'] ) ) {
				$args['posts_per_page'] = get_option( 'posts_per_page' );
			}
		} catch ( Tribe__REST__Exceptions__Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage(), array( 'status' => $e->getStatus() ) );
		}

		$cap = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->edit_posts;
		$args['post_status'] = current_user_can( $cap ) ? 'any' : 'publish';
		// Due to an incompatibility between date based queries and 'ids' fields we cannot do this, see `wp_list_pluck` use down
		// $args['fields'] = 'ids';

		$events = tribe_get_events( $args );

		$page = $this->parse_page( $request ) ? $this->parse_page( $request ) : 1;

		if ( empty( $events ) ) {
			$message = $this->messages->get_message( 'event-archive-page-not-found' );

			return new WP_Error( 'event-archive-page-not-found', $message, array( 'status' => 404 ) );
		}

		$events = wp_list_pluck( $events, 'ID' );

		unset( $args['fields'] );

		if ( $this->has_next( $args, $page ) ) {
			$data['next_rest_url'] = $this->get_next_rest_url( $data['rest_url'], $page );
		}

		if ( $this->has_previous( $page, $args ) ) {
			$data['previous_rest_url'] = $this->get_previous_rest_url( $data['rest_url'], $page );;
		}

		foreach ( $events as $event_id ) {
			$data['events'][] = $this->repository->get_event_data( $event_id );
		}

		$data['total']       = $total = $this->get_total( $args );
		$data['total_pages'] = $this->get_total_pages( $total, $this->parse_per_page( $request ) );

		/**
		 * Filters the data that will be returned for an events archive request.
		 *
		 * @param array           $data    The retrieved data.
		 * @param WP_REST_Request $request The original request.
		 */
		$data = apply_filters( 'tribe_rest_events_archive_data', $data, $request );

		$response = new WP_REST_Response( $data );

		if ( isset( $data['total'] ) && isset( $data['total_pages'] ) ) {
			$response->header( 'X-TEC-Total', $data['total'], true );
			$response->header( 'X-TEC-TotalPages', $data['total_pages'], true );
		}

		return $response;
	}

	/**
	 * Parses the `page` argument from the request.
	 *
	 * @param WP_REST_Request $request
	 * @return bool|int The `page` argument provided in the request or `false` if not set.
	 */
	protected function parse_page( WP_REST_Request $request ) {
		return !empty( $request['page'] ) ? intval( $request['page'] ) : false;
	}

	/**
	 * Parsed the `per_page` argument from the request.
	 *
	 * @param WP_REST_Request $request
	 * @return bool|int The `per_page` argument provided in the request or `false` if not set.
	 */
	protected function parse_per_page( WP_REST_Request $request ) {
		return ! empty( $request['per_page'] ) ?
			min( $this->get_max_posts_per_page(), intval( $request['per_page'] ) )
			: false;
	}

	/**
	 * Parses and converts the start date specified in the request.
	 *
	 * @param WP_REST_Request $request
	 * @return bool|string The parsed date in db format or `false` if not set.
	 *
	 * @throws Tribe__REST__Exceptions__Exception If the start date cannot be parsed.
	 */
	protected function parse_start_date( WP_REST_Request $request ) {
		return $this->parse_date_value( $request, 'start_date' );
	}

	/**
	 * Parses and converts the end date specified in the request.
	 *
	 * @param WP_REST_Request $request
	 * @return bool|string The parsed date in db format or `false` if not set.
	 *
	 * @throws Tribe__REST__Exceptions__Exception If the end date cannot be parsed.
	 */
	protected function parse_end_date( WP_REST_Request $request ) {
		return $this->parse_date_value( $request, 'end_date' );
	}

	/**
	 * Parses the event categories specified in the request.
	 *
	 * @param WP_REST_Request $request
	 * @return array|bool Either an array of event categories `term_id`s or `false` if not specified.
	 */
	protected function parse_categories( WP_REST_Request $request ) {
		return $this->parse_terms( $request, 'categories', Tribe__Events__Main::TAXONOMY );
	}

	/**
	 * Parses the event tags specified in the request.
	 *
	 * @param WP_REST_Request $request
	 * @return array|bool Either an array of event tags `term_id`s or `false` if not specified.
	 */
	protected function parse_tags( $request ) {
		return $this->parse_terms( $request, 'tags', 'post_tag' );
	}

	/**
	 * Parses the request for featured events.
	 *
	 * @param WP_REST_Request $request
	 * @return array|bool Either the meta query for featured events or `false` if not specified.
	 */
	protected function parse_featured_meta_query_entry( $request ) {
		if ( ! isset( $request['featured'] ) ) {
			return false;
		}

		$parsed = array(
			'key' => Tribe__Events__Featured_Events::FEATURED_EVENT_KEY,
			'compare' => $request['featured'] ? 'EXISTS' : 'NOT EXISTS',
		);

		return $parsed;
	}

	/**
	 * @param WP_REST_Request $request
	 * @param string $key The key where the terms are stored in the request..
	 * @param string $taxonomy The taxonomy of the terms to parse.
	 *
	 * @return array|bool Either an array of `terms_ids` or `false` on failure.
	 *
	 * @throws Tribe__REST__Exceptions__Exception If one of the terms does not exist for the specified taxonomy.
	 */
	protected function parse_terms( $request, $key, $taxonomy ) {
		if ( ! isset( $request[ $key ] ) ) {
			return false;
		}

		$parsed    = array();
		$requested = (array) $request[ $key ];

		foreach ( $requested as $requeste_term ) {
			$term = get_term_by( 'slug', $requeste_term, $taxonomy );

			if ( false === $term ) {
				$term = get_term_by( 'id', $requeste_term, $taxonomy );
			}

			if ( false === $term ) {
				$message = $this->messages->get_message( 'event-archive-bad-' . $key );

				throw new Tribe__REST__Exceptions__Exception( 'event-archive-bad-' . $key, $message, 400 );
			}

			$parsed[] = $term->term_id;
		}

		if ( ! empty( $parsed ) ) {
			$parsed = array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $parsed,
			);
		}

		return $parsed;
	}

	/**
	 * Parses and created a meta query entry in from the request.
	 *
	 * @param WP_REST_Request $request
	 * @param string          $key      The key where the values for the meta query are stored in the request.
	 * @param string          $meta_key The meta key that should be used for the comparison.
	 * @param string          $compare  The comparison operator.
	 * @param string          $type     The type to which compared values should be cast.
	 *
	 * @return array|bool The meta query entry or `false` on failure.
	 */
	protected function parse_meta_query_entry( $request, $key, $meta_key, $compare = '=', $type = 'CHAR' ) {
		if ( ! isset( $request[ $key ] ) ) {
			return false;
		}

		$parsed = array(
			'key'     => $meta_key,
			'value'   => $request[ $key ],
			'type'    => $type,
			'compare' => $compare,
		);

		return $parsed;
	}

	/**
	 * Builds and returns the current rest URL depending on the query arguments.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	protected function get_current_rest_url( array $args = array() ) {
		$url = tribe_events_rest_url( 'events/' );

		$flipped = array_flip( $this->supported_query_vars );
		$values  = array_intersect_key( $args, $flipped );
		$keys    = array_intersect_key( $flipped, $values );

		if ( ! empty( $keys ) ) {
			$url = add_query_arg( array_combine( array_values( $keys ), array_values( $values ) ), $url );
		}

		return $url;
	}

	/**
	 * Whether there is a next page in respect to the specified one.
	 *
	 * @param array $args
	 * @param int $page
	 *
	 * @return bool
	 */
	protected function has_next( $args, $page ) {
		$overrides = array(
			'paged'                  => $page + 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		);
		$next      = tribe_get_events( array_merge( $args, $overrides ) );

		return ! empty( $next );
	}

	/**
	 * Builds and returns the next page REST URL.
	 *
	 * @param string $rest_url
	 * @param int $page
	 *
	 * @return string
	 */
	protected function get_next_rest_url( $rest_url, $page ) {
		return add_query_arg( array( 'page' => $page + 1 ), remove_query_arg( 'page', $rest_url ) );
	}

	/**
	 * Whether there is a previous page in respect to the specified one.
	 *
	 * @param array $args
	 * @param int $page
	 *
	 * @return bool
	 */
	protected function has_previous( $page, $args ) {
		$overrides = array(
			'paged'                  => $page - 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		);
		$previous  = tribe_get_events( array_merge( $args, $overrides ) );

		return 1 !== $page && ! empty( $previous );
	}

	/**
	 * Builds and returns the previous page REST URL.
	 *
	 * @param string $rest_url
	 * @param int $page
	 *
	 * @return string
	 */
	protected function get_previous_rest_url( $rest_url, $page ) {
		$rest_url = remove_query_arg( 'page', $rest_url );

		return 2 === $page ? $rest_url : add_query_arg( array( 'page' => $page - 1 ), $rest_url );
	}

	protected function is_positive_int_gte( $value, $min ) {
		return filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => $min ) ) );
	}

	/**
	 * @return int
	 */
	protected function get_max_posts_per_page() {
		/**
		 * Filters the maximum number of events per page that should be returned.
		 *
		 * @param int $per_page Default to 50.
		 */
		return apply_filters( 'tribe_rest_event_max_per_page', 50 );
	}

	protected function parse_search( $request ) {
		if ( ! isset( $request['search'] ) ) {
			return false;
		}

		$filtered = filter_var( $request['search'], FILTER_SANITIZE_STRING );

		if ( ! $filtered ) {
			$message = $this->messages->get_message( 'event-archive-bad-search-string' );

			throw new Tribe__REST__Exceptions__Exception( 'event-archive-bad-search-string', $message, 400 );
		}

		return $filtered;
	}

	/**
	 * @param array $args
	 *
	 * @return int
	 */
	protected function get_total( $args ) {
		$this->total = count( tribe_get_events( array_merge( $args, array(
			'posts_per_page'         => - 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		) ) ) );

		return $this->total;
	}

	/**
	 * Returns the total number of pages depending on the `per_page` setting.
	 *
	 * @param int $total
	 * @param int $per_page
	 *
	 * @return int
	 */
	protected function get_total_pages( $total, $per_page = null ) {
		$per_page    = $per_page ? $per_page : get_option( 'posts_per_page' );
		$total_pages = intval( ceil( $total / $per_page ) );

		return $total_pages;
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
		return array(
			'get' => array(
				'parameters' => array(
					array(
						'name'        => 'page',
						'in'          => 'query',
						'description' => __( 'The archive page to return', 'the-events-calendar' ),
						'type'        => 'integer',
						'required'    => false,
						'default'     => 1,
					),
					array(
						'name'        => 'per_page',
						'in'          => 'query',
						'description' => __( 'The number of events to return on each page', 'the-events-calendar' ),
						'type'        => 'integer',
						'required'    => false,
						'default'     => get_option( 'posts_per_page' ),
					),
					array(
						'name'        => 'start_date',
						'in'          => 'query',
						'description' => __( 'Events should start after the specified date', 'the-events-calendar' ),
						'type'        => 'date',
						'required'    => false,
						'default'     => date( Tribe__Date_Utils::DBDATETIMEFORMAT, time() ),
					),
					array(
						'name'        => 'end_date',
						'in'          => 'query',
						'description' => __( 'Events should start before the specified date', 'the-events-calendar' ),
						'type'        => 'string',
						'required'    => false,
						'default'     => date( Tribe__Date_Utils::DBDATETIMEFORMAT, time() ),
					),
					array(
						'name'        => 'search',
						'in'          => 'query',
						'description' => __( 'Events should contain the specified string in the title or description', 'the-events-calendar' ),
						'type'        => 'string',
						'required'    => false,
						'default'     => '',
					),
					array(
						'name'        => 'categories',
						'in'          => 'query',
						'description' => __( 'Events should be assigned one of the specified categories slugs or IDs', 'the-events-calendar' ),
						'type'        => 'array',
						'required'    => false,
						'default'     => '',
					),
					array(
						'name'        => 'tags',
						'in'          => 'query',
						'description' => __( 'Events should be assigned one of the specified tags slugs or IDs', 'the-events-calendar' ),
						'type'        => 'array',
						'required'    => false,
						'default'     => '',
					),
				),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Returns all the upcoming events matching the search criteria', 'the-event-calendar' ),
						'schema'      => array(
							'title' => 'events',
							'type'  => 'array',
							'items' => array( '$ref' => '#/definitions/Event' ),
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
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function GET_args() {
		return array(
			'page'       => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'default'           => 1,
			),
			'per_page'   => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'default'           => $this->get_default_posts_per_page(),
			),
			'start_date' => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_time' ),
				'default'           => date( Tribe__Date_Utils::DBDATETIMEFORMAT, time() ),
			),
			'end_date' => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_time' ),
				'default'           => date( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( '+24 months' ) ),
			),
			's'          => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
			),
			'venue'      => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, array( $this->validator, 'is_venue_id' ) ),
			),
			'organizer'  => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, array( $this->validator, 'is_organizer_id' ) ),
			),
			'featured'   => array(
				'required' => false,
			),
			'categories' => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_category' ),
			),
			'tags'       => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_post_tag' ),
			),
		);
	}
}