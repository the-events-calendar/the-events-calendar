<?php


class Tribe__Events__REST__V1__Endpoints__Archive_Event extends Tribe__Events__REST__V1__Endpoints__Base implements
	Tribe__REST__Endpoints__Endpoint_Interface {

	/**
	 * @var Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	/**
	 * @var array An array mapping the REST request supported query vars to the args used in a TEC WP_Query.
	 */
	protected $supported_query_vars
		= array(
			'page'       => 'paged',
			'per_page'   => 'posts_per_page',
			'start_date' => 'start_date',
			'end_date'   => 'end_date',
			'search'     => 's',
		);

	/**
	 * @var int The total number of events according to the current request parameters and user access rights.
	 */
	protected $total;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Archive_Event constructor.
	 *
	 * @param Tribe__REST__Messages_Interface                  $messages
	 * @param Tribe__Events__REST__Interfaces__Post_Repository $repository
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $repository
	) {
		parent::__construct( $messages );
		$this->repository = $repository;
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$defaults = array( 'fields' => 'ids', 'posts_per_page' => get_option( 'posts_per_page' ) );

		$args = array();

		$page = $this->parse_page( $request );

		if ( is_wp_error( $page ) ) {
			return $page;
		}

		$args['paged'] = $page;

		$per_page = $this->parse_per_page( $request );

		if ( is_wp_error( $per_page ) ) {
			return $per_page;
		}

		$args['posts_per_page'] = $per_page;

		$start_date = $this->parse_start_date( $request );

		if ( is_wp_error( $start_date ) ) {
			return $start_date;
		}

		$args['start_date'] = $start_date;

		$end_date = $this->parse_end_date( $request );

		if ( is_wp_error( $end_date ) ) {
			return $end_date;
		}

		$args['end_date'] = $end_date;

		$search = $this->parse_search( $request );

		if ( is_wp_error( $search ) ) {
			return $search;
		}

		$args['s'] = $search;

		$args = array_filter( wp_parse_args( $args, $defaults ) );

		$data = array( 'events' => array() );

		$data['rest_url'] = $this->get_current_rest_url( $args );

		if ( ! isset( $args['posts_per_page'] ) ) {
			$args['posts_per_page'] = get_option( 'posts_per_page' );
		}

		$cap = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->edit_posts;
		if ( current_user_can( $cap ) ) {
			$args['post_status'] = 'any';
		}

		$events = tribe_get_events( $args );

		$page = $page ? $page : 1;

		if ( empty( $events ) && 1 !== $page ) {
			$message = $this->messages->get_message( 'event-archive-page-not-found' );

			return new WP_Error( 'event-archive-page-not-found', $message, array( 'status' => 404 ) );
		}

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

		$data['total'] = $total = $this->get_total( $args );
		$data['total_pages'] = $this->get_total_pages( $total, $per_page );

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

	protected function parse_page( WP_REST_Request $request ) {
		if ( ! isset( $request['page'] ) ) {
			return false;
		}

		if ( isset( $request['page'] ) && ! $this->is_positive_int_gte( $request['page'], 1 ) ) {
			$message = $this->messages->get_message( 'event-archive-bad-page' );

			return new WP_Error( 'event-archive-bad-page', $message, array( 'status' => 400 ) );
		}

		return intval( $request['page'] );
	}

	protected function parse_per_page( WP_REST_Request $request ) {
		if ( ! isset( $request['per_page'] ) ) {
			return false;
		}

		if ( isset( $request['per_page'] ) && ! $this->is_positive_int_gte( $request['per_page'], 1 ) ) {
			$message = $this->messages->get_message( 'event-archive-bad-per-page' );

			return new WP_Error( 'event-archive-bad-per-page', $message, array( 'status' => 400 ) );
		}

		if ( ! empty( $request['per_page'] ) ) {
			return min( $this->get_max_posts_per_page(), intval( $request['per_page'] ) );
		}

	}

	protected function parse_start_date( WP_REST_Request $request ) {
		if ( ! empty( $request['start_date'] ) ) {
			$start_date = strtotime( $request['start_date'] );
			// Unix timestamp is a thing...
			$start_date = $start_date ? $start_date : $request['start_date'];
			// at this point if it's legit it should be a number
			if ( ! is_numeric( $start_date ) ) {
				$message = $this->messages->get_message( 'event-archive-bad-start-date' );

				return new WP_Error( 'event-archive-bad-start-date', $message, array( 'status' => 400 ) );
			}
			try {
				return date( Tribe__Date_Utils::DBDATETIMEFORMAT, $start_date );
			} catch ( Exception $e ) {
				$message = $this->messages->get_message( 'event-archive-bad-start-date' );

				return new WP_Error( 'event-archive-bad-start-date', $message, array( 'status' => 400 ) );
			}
		}

		return false;
	}

	protected function parse_end_date( WP_REST_Request $request ) {
		if ( isset( $request['end_date'] ) ) {
			$end_date = strtotime( $request['end_date'] );
			// Unix timestamp is a thing...
			$end_date = $end_date ? $end_date : $request['end_date'];
			// at this point if it's legit it should be a number
			if ( ! is_numeric( $end_date ) ) {
				$message = $this->messages->get_message( 'event-archive-bad-end-date' );

				return new WP_Error( 'event-archive-bad-end-date', $message, array( 'status' => 400 ) );
			}
			try {
				return date( Tribe__Date_Utils::DBDATETIMEFORMAT, $end_date );
			} catch ( Exception $e ) {
				$message = $this->messages->get_message( 'event-archive-bad-end-date' );

				return new WP_Error( 'event-archive-bad-end-date', $message, array( 'status' => 400 ) );
			}
		}

		return false;
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	protected function get_current_rest_url( array $args = array() ) {
		$url = tribe_events_rest_url( 'events/' );

		$flipped = array_flip( $this->supported_query_vars );
		$values = array_intersect_key( $args, $flipped );
		$keys = array_intersect_key( $flipped, $values );

		if ( ! empty( $keys ) ) {
			$url = add_query_arg( array_combine( array_values( $keys ), array_values( $values ) ), $url );
		}

		return $url;
	}

	/**
	 * @param $args
	 * @param $page
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
		$next = tribe_get_events( array_merge( $args, $overrides ) );

		return ! empty( $next );
	}

	protected function get_next_rest_url( $rest_url, $page ) {
		return add_query_arg( array( 'page' => $page + 1 ), remove_query_arg( 'page', $rest_url ) );
	}

	/**
	 * @param $page
	 * @param $args
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
		$previous = tribe_get_events( array_merge( $args, $overrides ) );

		return 1 !== $page && ! empty( $previous );
	}

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

			return new WP_Error( 'event-archive-bad-search-string', $message, array( 'status' => 400 ) );
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
	 * @param int $total
	 * @param int $per_page
	 *
	 * @return int
	 */
	protected function get_total_pages( $total, $per_page = null ) {
		$per_page = $per_page ? $per_page : get_option( 'posts_per_page' );
		$total_pages = intval( ceil( $total / $per_page ) );

		return $total_pages;
	}
}