<?php


class Tribe__Events__REST__V1__Endpoints__Archive_Event
	extends Tribe__Events__REST__V1__Endpoints__Archive_Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface, Tribe__Documentation__Swagger__Provider_Interface {
	/**
	 * @var array An array mapping the REST request supported query vars to the args used in a TEC WP_Query.
	 */
	protected $supported_query_vars = [
		'page'        => 'paged',
		'per_page'    => 'posts_per_page',
		'start_date'  => 'start_date',
		'end_date'    => 'end_date',
		'search'      => 's',
		'categories'  => 'categories',
		'tags'        => 'tags',
		'venue'       => 'venue',
		'organizer'   => 'organizer',
		'featured'    => 'featured',
		'geoloc'      => 'tribe_geoloc',
		'geoloc_lat'  => 'tribe_geoloc_lat',
		'geoloc_lng'  => 'tribe_geoloc_lng',
		'status'      => 'post_status',
		'post_parent' => 'post_parent',
		'include'     => 'post__in',
		'starts_before' => 'starts_before',
		'starts_after' => 'starts_after',
		'ends_before' => 'ends_before',
		'ends_after' => 'ends_after',
		'ticketed' => 'ticketed',
	];

	/**
	 * Tribe__Events__REST__V1__Endpoints__Archive_Event constructor.
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
		$this->post_type = Tribe__Events__Main::POSTTYPE;
	}


	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$args        = [];
		$date_format = Tribe__Date_Utils::DBDATETIMEFORMAT;
		$relative_dates = false;

		$args['paged']          = $request['page'];
		$args['posts_per_page'] = $request['per_page'];

		if ( isset( $request['starts_before'] ) ) {
			$args['starts_before'] = Tribe__Timezones::localize_date( $date_format, $request['starts_before'] );
			$relative_dates = true;
		}

		if ( isset( $request['starts_after'] ) ) {
			$args['starts_after'] = Tribe__Timezones::localize_date( $date_format, $request['starts_after'] );
			$relative_dates = true;
		}

		if ( isset( $request['ends_before'] ) ) {
			$args['ends_before'] = Tribe__Timezones::localize_date( $date_format, $request['ends_before'] );
			$relative_dates = true;
		}

		if ( isset( $request['ends_after'] ) ) {
			$args['ends_after'] = Tribe__Timezones::localize_date( $date_format, $request['ends_after'] );
			$relative_dates = true;
		}

		$args['start_date']     = isset( $request['start_date'] ) ?
			Tribe__Timezones::localize_date( $date_format, $request['start_date'] )
			: false;

		$args['end_date']       = isset( $request['end_date'] ) ?
			Tribe__Timezones::localize_date( $date_format, $request['end_date'] )
			: false;

		$args['s']              = $request['search'];

		if ( $post__in = $request['include'] ) {
			$args['post__in']                  = $request['include'];
			$args['tribe_remove_date_filters'] = true;
		}

		$args['post_parent'] = $request['post_parent'];

		$args['ticketed'] = $request['ticketed'];


		// Allow REST API users to define the default behavior of the inclusive date parameters.
		$use_inclusive_start_end_dates = isset( $request['strict_dates'] ) ? ! filter_var( $request['strict_dates'], FILTER_VALIDATE_BOOLEAN ) : true;

		/**
		 * Allows users to override the default "inclusive" start and end dates and  make the REST API use a
		 * timezone-adjusted date range.
		 *
		 * Example: wp-json/tribe/events/v1/events?start_date=2017-12-21&end_date=2017-12-22
		 *
		 * - The "inclusive" behavior, which is the default here, would set start_date to
		 *   2017-12-21 00:00:00 and end_date to 2017-12-22 23:59:59. Events within this range will
		 *   be retrieved.
		 *
		 * - If you set this filter to true on a site whose timezone is America/New_York, then the
		 *   REST API would set start_date to 2017-12-20 19:00:00 and end_date to
		 *   2017-12-21 19:00:00. A different range of events to draw from.
		 *
		 * @since 4.6.8
		 *
		 * @param bool $use_inclusive Defaults to false (inclusive, not strict). Whether to use "inclusive" start and end dates.
		 */
		if ( apply_filters( 'tribe_events_rest_use_inclusive_start_end_dates', $use_inclusive_start_end_dates ) ) {

			if ( ! empty( $args['start_date'] ) ) {
				$args['start_date'] = tribe_beginning_of_day( $request['start_date'] );
			}

			if ( ! empty( $args['end_date'] ) ) {
				$args['end_date'] = tribe_end_of_day( $request['end_date'] );
			}

			if ( ! empty( $args['ends_after'] ) ) {
				$args['ends_after'] = tribe_end_of_day( $request['ends_after'] );
			}

			if ( ! empty( $args['ends_before'] ) ) {
				$args['ends_before'] = tribe_end_of_day( $request['ends_before'] );
			}

			if ( ! empty( $args['starts_before'] ) ) {
				$args['starts_before'] = tribe_end_of_day( $request['starts_before'] );
			}

			if ( ! empty( $args['starts_after'] ) ) {
				$args['starts_after'] = tribe_end_of_day( $request['starts_after'] );
			}
		}

		$args['meta_query'] = array_filter( [
			$this->parse_meta_query_entry( $request['venue'], '_EventVenueID', '=', 'NUMERIC' ),
			$this->parse_meta_query_entry( $request['organizer'], '_EventOrganizerID', '=', 'NUMERIC' ),
			$this->parse_featured_meta_query_entry( $request['featured'] ),
		] );

		$args['tax_query'] = array_filter( [
			$this->parse_terms_query( $request['categories'], Tribe__Events__Main::TAXONOMY ),
			$this->parse_terms_query( $request['tags'], 'post_tag' ),
		] );

		$extra_rest_args = [
			'venue'     => Tribe__Utils__Array::to_list( $request['venue'] ),
			'organizer' => Tribe__Utils__Array::to_list( $request['organizer'] ),
			'featured'  => $request['featured'],
		];
		$extra_rest_args = array_diff_key( $extra_rest_args, array_filter( $extra_rest_args, 'is_null' ) );

		// Filter by geoloc
		if ( ! empty( $request['geoloc'] ) ) {
			$args['tribe_geoloc']     = 1;
			$args['tribe_geoloc_lat'] = isset( $request['geoloc_lat'] ) ? $request['geoloc_lat'] : '';
			$args['tribe_geoloc_lng'] = isset( $request['geoloc_lng'] ) ? $request['geoloc_lng'] : '';
		}

		// When including specific posts date queries will be voided
		if ( isset( $args['post__in'] ) ) {
			unset( $args['start_date'], $args['end_date'] );
			$args['orderby'] = Tribe__Utils__Array::get( $args, 'orderby', [ 'date', 'ID' ] );
			$args['order']   = Tribe__Utils__Array::get( $args, 'order', 'ASC' );
		}

		$args = $this->parse_args( $args, $request->get_default_params() );

		if ( $relative_dates ) {
			$query_params = $request->get_query_params();
			if ( ! isset( $query_params['start_date'] ) ) {
				unset( $args['start_date'] );
			}

			if ( ! isset( $query_params['end_date'] ) ) {
				unset( $args['end_date'] );
			}
		}

		if ( null === $request['status'] ) {
			$cap                 = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->edit_posts;
			$args['post_status'] = current_user_can( $cap ) ? 'any' : 'publish';
		} else {
			$args['post_status'] = $this->filter_post_status_list( $request['status'] );
		}

		// Due to an incompatibility between date based queries and 'ids' fields we cannot do this, see `wp_list_pluck` use down
		// $args['fields'] = 'ids';

		if ( empty( $args['posts_per_page'] ) ) {
			$args['posts_per_page'] = $this->get_default_posts_per_page();
		}

		/** @var Tribe__Cache $cache */
		$cache     = tribe( 'cache' );
		$cache_key = 'rest_get_events_data_' . get_current_user_id() . '_' . wp_json_encode( $args );

		$data = $cache->get( $cache_key, 'save_post' );

		if ( ! is_array( $data ) ) {
			$data = [ 'events' => [] ];

			$data['rest_url'] = $this->get_current_rest_url( $args, $extra_rest_args );

			/**
			 * Filter the arguments used to get the events on the archive page via REST API.
			 *
			 * @since 4.9.4
			 *
			 * @param array            $args Arguments used to get the events from the archive page.
			 * @param array            $data Array with the data to be returned in the REST response.
			 * @param \WP_REST_Request $request
			 */
			$args = apply_filters( 'tribe_events_archive_get_args', $args, $data, $request );

			$events = tribe_get_events( $args );

			$page = $this->parse_page( $request ) ? $this->parse_page( $request ) : 1;

			if ( empty( $events ) && (int) $page > 1 ) {
				$message = $this->messages->get_message( 'event-archive-page-not-found' );

				return new WP_Error( 'event-archive-page-not-found', $message, [ 'status' => 404 ] );
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
				$event = $this->repository->get_event_data( $event_id );

				if ( $event && ! is_wp_error( $event ) ) {
					$data['events'][] = $event;
				}
			}

			$data['total']       = $total = $this->get_total( $args );
			$data['total_pages'] = $this->get_total_pages( $total, $args['posts_per_page'] );

			$cache->set( $cache_key, $data, Tribe__Cache::NON_PERSISTENT, 'save_post' );
		}

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
		return ! empty( $request['page'] ) ? intval( $request['page'] ) : false;
	}

	/**
	 * Parses the request for featured events.
	 *
	 * @param string $featured
	 *
	 * @return array|bool Either the meta query for featured events or `false` if not specified.
	 */
	protected function parse_featured_meta_query_entry( $featured ) {
		if ( null === $featured ) {
			return false;
		}

		$parsed = [
			'key'     => Tribe__Events__Featured_Events::FEATURED_EVENT_KEY,
			'compare' => $featured ? 'EXISTS' : 'NOT EXISTS',
		];

		return $parsed;
	}

	/**
	 * @param array|string $terms A list of terms term_id or slugs or a single term term_id or slug.
	 * @param string $taxonomy The taxonomy of the terms to parse.
	 *
	 * @return array|bool Either an array of `terms_ids` or `false` on failure.
	 *
	 * @throws Tribe__REST__Exceptions__Exception If one of the terms does not exist for the specified taxonomy.
	 */
	protected function parse_terms_query( $terms, $taxonomy ) {
		if ( empty( $terms ) ) {
			return false;
		}

		$parsed    = [];
		$requested = Tribe__Utils__Array::list_to_array( $terms );

		foreach ( $requested as $t ) {
			$term = get_term_by( 'slug', $t, $taxonomy );

			if ( false === $term ) {
				$term = get_term_by( 'id', $t, $taxonomy );
			}

			$parsed[] = $term->term_id;
		}

		if ( ! empty( $parsed ) ) {
			$parsed = [
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $parsed,
			];
		}

		return $parsed;
	}

	/**
	 * Parses and created a meta query entry in from the request.
	 *
	 * @param string $meta_value The value that should be used for comparison.
	 * @param string $meta_key   The meta key that should be used for the comparison.
	 * @param string $compare    The comparison operator.
	 * @param string $type       The type to which compared values should be cast.
	 * @param string $relation   If multiple meta values are provided then this is the relation that the query should use.
	 *
	 * @return array|bool The meta query entry or `false` on failure.
	 */
	protected function parse_meta_query_entry( $meta_value, $meta_key, $compare = '=', $type = 'CHAR', $relation = 'OR' ) {
		if ( empty( $meta_value ) ) {
			return false;
		}

		$meta_values = Tribe__Utils__Array::list_to_array( $meta_value );

		$parsed = [ 'relation' => 'OR' ];
		foreach ( $meta_values as $value ) {
			$parsed[] = [
				'key'     => $meta_key,
				'value'   => $value,
				'type'    => $type,
				'compare' => $compare,
			];
		}

		return $parsed;
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
		$overrides = [
			'paged'                  => $page + 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];
		$next      = tribe_get_events( array_merge( $args, $overrides ) );

		return ! empty( $next );
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
		$overrides = [
			'paged'                  => $page - 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];
		$previous  = tribe_get_events( array_merge( $args, $overrides ) );

		return 1 !== $page && ! empty( $previous );
	}

	/**
	 * Returns the maximum number of posts per page fetched via the REST API.
	 *
	 * @return int
	 */
	public function get_max_posts_per_page() {
		/**
		 * Filters the maximum number of events per page that should be returned.
		 *
		 * @param int $per_page Default to 50.
		 */
		return apply_filters( 'tribe_rest_event_max_per_page', 50 );
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
		return [
			'get' => [
				'parameters' => $this->swaggerize_args( $this->READ_args(), [ 'in' => 'query', 'default' => '' ] ),
				'responses'  => [
					'200' => [
						'description' => __( 'Returns all the upcoming events matching the search criteria', 'the-events-calendar' ),
						'content' => [
							'application/json' => [
								'schema' => [
									'title' => 'events',
									'type'  => 'array',
									'items' => [ '$ref' => '#/components/schemas/Event' ],
								],
							]
						]
					],
					'400' => [
						'description' => __( 'One or more of the specified query variables has a bad format', 'the-events-calendar' ),
					],
					'404' => [
						'description' => __( 'The requested page was not found.', 'the-events-calendar' ),
					],
				],
			],
		];
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 6.0.0 Added support for `ticketed` parameter.
	 *
	 * @return array
	 */
	public function READ_args() {
		return [
			'page'        => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
				'default'           => 1,
				'description'       => __( 'The archive page to return', 'the-events-calendar' ),
				'type'              => 'integer',
			],
			'per_page'    => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
				'sanitize_callback' => [ $this, 'sanitize_per_page' ],
				'default'           => $this->get_default_posts_per_page(),
				'description'       => __( 'The number of events to return on each page', 'the-events-calendar' ),
				'type'              => 'integer',
			],
			'start_date'  => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_time' ],
				'default'           => Tribe__Timezones::localize_date( Tribe__Date_Utils::DBDATETIMEFORMAT, 'yesterday 23:59' ),
				'description'       => __( 'Events that start on the specified date', 'the-events-calendar' ),
				'swagger_type'      => 'string',
			],
			'end_date'    => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_time' ],
				'default'           => date( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( '+24 months' ) ),
				'description'       => __( 'Events that end on the specified date', 'the-events-calendar' ),
				'swagger_type'      => 'string',
			],
			'starts_before'  => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_time' ],
				'description'       => __( 'Events that start before the specified date', 'the-events-calendar' ),
				'swagger_type'      => 'string',
			],
			'starts_after'  => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_time' ],
				'description'       => __( 'Events that start after the specified date', 'the-events-calendar' ),
				'swagger_type'      => 'string',
			],
			'ends_before'  => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_time' ],
				'description'       => __( 'Events that end before the specified date', 'the-events-calendar' ),
				'swagger_type'      => 'string',
			],
			'ends_after'  => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_time' ],
				'description'       => __( 'Events that end after the specified date', 'the-events-calendar' ),
				'swagger_type'      => 'string',
			],
			'strict_dates'  => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_string' ],
				'default'           => 'false',
				'description'       => __( 'Dates set using the start_date/end_date, starts_*/ends_* are set to start at the specified times. The default behavior is to include the entire days.', 'the-events-calendar' ),
				'swagger_type'      => 'string',
			],
			'search'      => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_string' ],
				'description'       => __( 'Events should contain the specified string in the title or description', 'the-events-calendar' ),
				'type'              => 'string',
			],
			'categories'  => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_event_category' ],
				'description'       => __( 'Events should be assigned one of the specified categories slugs or IDs', 'the-events-calendar' ),
				'swagger_type'      => 'array',
				'items'             => [ 'type' => 'integer' ],
				'collectionFormat'  => 'csv',
			],
			'tags'        => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_post_tag' ],
				'description'       => __( 'Events should be assigned one of the specified tags slugs or IDs', 'the-events-calendar' ),
				'swagger_type'      => 'array',
				'items'             => [ 'type' => 'integer' ],
				'collectionFormat'  => 'csv',
			],
			'venue'       => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_venue_id_list' ],
				'description'       => __( 'Events should be assigned one of the specified venue IDs', 'the-events-calendar' ),
				'swagger_type'      => 'array',
				'items'             => [ 'type' => 'integer' ],
				'collectionFormat'  => 'csv',
			],
			'organizer'   => [
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_organizer_id_list' ],
				'description'       => __( 'Events should be assigned one of the specified organizer IDs', 'the-events-calendar' ),
				'swagger_type'      => 'array',
				'items'             => [ 'type' => 'integer' ],
				'collectionFormat'  => 'csv',
			],
			'featured'    => [
				'required'    => false,
				'type'        => 'boolean',
				'description' => __( 'Events should be filtered by their featured status', 'the-events-calendar' ),
			],
			'status'      => [
				'required'          => false,
				'validate_callback' => [ $this, 'filter_post_status_list' ],
				'swagger_type'      => 'string',
				'format'            => 'string',
				'description'       => __( 'The event post status', 'the-events-calendar' ),
			],
			'geoloc'      => [
				'required'    => false,
				'type'        => 'boolean',
				'description' => __( 'Requires Events Calendar Pro. Events should be filtered by whether their venue has geolocation data', 'the-events-calendar' ),
			],
			'geoloc_lat'  => [
				'required'     => false,
				'swagger_type' => 'number',
				'format'       => 'double',
				'description'  => __( 'Requires Events Calendar Pro. Events should be filtered by their venue latitude location, must also provide geoloc_lng', 'the-events-calendar' ),
			],
			'geoloc_lng'  => [
				'required'     => false,
				'swagger_type' => 'number',
				'format'       => 'double',
				'description'  => __( 'Requires Events Calendar Pro. Events should be filtered by their venue longitude location, must also provide geoloc_lat', 'the-events-calendar' ),
			],
			'include'     => [
				'required'          => false,
				'description'       => __( 'Include events with one of the post IDs specified in the array of CSV list, date filters will be ignored.', 'the-events-calendar' ),
				'swagger_type'      => 'array',
				'items'             => [ 'type' => 'integer' ],
				'collectionFormat'  => 'csv',
				'validate_callback' => [ $this->validator, 'is_positive_int_list' ],
				'sanitize_callback' => [ 'Tribe__Utils__Array', 'list_to_array' ],
			],
			'post_parent' => [
				'required'          => false,
				'type'              => 'integer',
				'description'       => __( 'Events should be filtered by their post_parent being the specified one.', 'the-events-calendar' ),
				'validate_callback' => [ $this->validator, 'is_event_id' ],
			],
			'ticketed' => [
				'required'          => false,
				'type'              => 'boolean',
				'description'       => __( 'Filter events with or without tickets.', 'the-events-calendar' ),
				'validate_callback' => [ $this->validator, 'supports_ticketed' ],
			],
		];
	}

	/**
	 * Returns the total number of posts matching the request.
	 *
	 * @since 4.6
	 *
	 * @param array $args
	 *
	 * @return int
	 */
	protected function get_total( $args ) {
		$this->total = tribe_get_events( array_merge( $args, [
			'found_posts'            => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] ) );

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
		$url = tribe_events_rest_url( 'events/' );

		return $url;
	}
}
