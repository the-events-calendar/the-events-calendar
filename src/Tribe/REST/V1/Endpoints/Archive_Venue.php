<?php

class Tribe__Events__REST__V1__Endpoints__Archive_Venue
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
	);

	/**
	 * Returns an array in the format used by Swagger 2.0. *
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
		// TODO: Implement get_documentation() method.
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$args                       = array();
		$args['posts_per_page']     = $request['per_page'];
		$args['paged']              = $request['page'];
		$args['s']                  = $request['search'];
		$args['event']              = $request['event'];
		$args['has_events']         = $request['has_events'];

		$cap                 = get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE )->cap->edit_posts;
		$args['post_status'] = current_user_can( $cap ) ? 'any' : 'publish';

		$args = $this->parse_args( $args, $request->get_default_params() );

		$only_with_upcoming = isset( $request['only_with_upcoming'] )
			? tribe_is_truthy( $request['only_with_upcoming'] )
			: true;
		unset( $args['only_with_upcoming'] );

		if ( ! empty( $args['s'] ) ) {
			/** @var Tribe__Events__Venue $linked_post */
			$linked_post = tribe( 'tec.linked-posts.venue' );
			$matches = $linked_post->find_like( $args['s'] );
			unset( $args['s'] );
			if ( ! empty( $matches ) ) {
				$args['post__in'] = $matches;
			} else {
				$message = $this->messages->get_message( 'venue-archive-page-not-found' );

				return new WP_Error( 'venue-archive-page-not-found', $message, array( 'status' => 404 ) );
			}
		}

		$venues = tribe_get_venues( $only_with_upcoming, $args['posts_per_page'], true, $args );

		unset( $args['fields'] );

		if ( empty( $venues ) ) {
			$message = $this->messages->get_message( 'venue-archive-page-not-found' );

			return new WP_Error( 'venue-archive-page-not-found', $message, array( 'status' => 404 ) );
		}

		$ids = wp_list_pluck( $venues, 'ID' );

		$data = array( 'venues' => array() );

		foreach ( $ids as $venue_id ) {
			$data['venues'][] = $this->repository->get_venue_data( $venue_id );
		}

		$data['rest_url'] = $this->get_current_rest_url( $args );

		$page = $args['paged'];

		if ( $this->has_next( $args, $page ) ) {
			$data['next_rest_url'] = $this->get_next_rest_url( $data['rest_url'], $page );
		}

		if ( $this->has_previous( $page, $args ) ) {
			$data['previous_rest_url'] = $this->get_previous_rest_url( $data['rest_url'], $page );;
		}

		$total_args = $args;
		$total_args['only_with_upcoming'] = $only_with_upcoming;
		$data['total'] = $total = $this->get_total( $total_args );
		$data['total_pages'] = $this->get_total_pages( $total, $args['posts_per_page'] );

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
	 */
	public function READ_args() {
		return array(
			'page'                => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'default'           => 1,
				'description'       => __( 'The archive page to return', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'per_page'            => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'sanitize_callback' => array( $this, 'sanitize_per_page' ),
				'default'           => $this->get_default_posts_per_page(),
				'description'       => __( 'The number of venues to return on each page', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'search'              => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Venues should contain the specified string in the title, description or custom fields', 'the-events-calendar' ),
				'type'              => 'string',
			),
			'event'               => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_id' ),
				'description'       => __( 'Venues should be related to this event', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'has_events'          => array(
				'required'     => false,
				'description'  => __( 'Venues should have events associated to them', 'the-events-calendar' ),
				'swagger_type' => 'boolean',
			),
			'only_with_upcoming' => array(
				'required'     => false,
				'description'  => __( 'Venues should have upcoming events associated to them', 'the-events-calendar' ),
				'swagger_type' => 'boolean',
				'default' => true,
			),
		);
	}

	/**
	 * Returns the maximum number of posts per page fetched via the REST API.
	 *
	 * @return int
	 */
	public function get_max_posts_per_page() {
		/**
		 * Filters the maximum number of venues per page that should be returned.
		 *
		 * @param int $per_page Default to 50.
		 */
		return apply_filters( 'tribe_rest_venue_max_per_page', 50 );
	}

	/**
	 * @param array $args
	 *
	 * @return int
	 */
	protected function get_total( $args ) {
		$only_with_upcoming = $args['only_with_upcoming'];
		unset($args['posts_per_page']);

		$this->total = count( tribe_get_venues( $only_with_upcoming, -1, false,
			array_merge( $args, array(
				'fields'                 => 'ids',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false
			) ) ) );

		return $this->total;
	}

	/**
	 * Returns the archive base REST URL
	 *
	 * @return string
	 */
	protected function get_base_rest_url() {
		$url = tribe_events_rest_url( 'venues/' );

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
		$only_with_upcoming = false;

		$overrides = array(
			'paged'                  => $page + 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		);

		$per_page = $args['posts_per_page'];
		$overrides = array_merge( $args, $overrides );

		$next = tribe_get_venues( $only_with_upcoming, $per_page, false, $overrides );

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
		$only_with_upcoming = false;

		$overrides = array(
			'paged'                  => $page - 1,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		);

		$per_page = $args['posts_per_page'];
		$overrides = array_merge( $args, $overrides );

		$previous = tribe_get_venues( $only_with_upcoming, $per_page, false, array_merge( $args, $overrides ) );

		return 1 !== $page && ! empty( $previous );
	}
}