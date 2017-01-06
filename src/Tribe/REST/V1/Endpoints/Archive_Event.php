<?php


class Tribe__Events__REST__V1__Endpoints__Archive_Event extends Tribe__Events__REST__V1__Endpoints__Base implements
	Tribe__REST__Endpoints__Endpoint_Interface {

	/**
	 * @var Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

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

		$page = 1;
		if ( ! empty( $request['page'] ) && is_numeric( $request['page'] ) && intval( $request['page'] ) >= 1 ) {
			$message = $this->messages->get_message( 'event-archive-bad-page' );

			return new WP_Error( 'event-archive-bad-page', $message, array( 'status' => 400 ) );
		}

		if ( ! empty( $request['page'] ) ) {
			$args['paged'] = $page = intval( $request['page'] );
		}

		$per_page = get_option( 'posts_per_page' );

		if ( ! empty( $request['per_page'] ) && ! ( is_numeric( $request['per_page'] ) && intval( $request['per_page'] ) >= 1 ) ) {
			$message = $this->messages->get_message( 'event-archive-bad-per-page' );

			return new WP_Error( 'event-archive-bad-per-page', $message, array( 'status' => 400 ) );
		}

		if ( ! empty( $request['per_page'] ) ) {
			$args['posts_per_page'] = min( 50, intval( $request['per_page'] ) );
		} else {
			$args['posts_per_page'] = $per_page;
		}

		if ( ! empty( $request['start_date'] ) && is_string( $request['start_date'] ) ) {
			$start_date = strtotime( $request['start_date'] );
			// Unix timestamp is a thing...
			$start_date = $start_date ? $start_date : $request['start_date'];
			// at this point if it's legit it should be a number
			if ( ! is_numeric( $start_date ) ) {
				$message = $this->messages->get_message( 'event-archive-bad-start-date' );

				return new WP_Error( 'event-archive-bad-start-date', $message, array( 'status' => 400 ) );
			}
			try {
				$args['start_date'] = date( Tribe__Date_Utils::DBDATETIMEFORMAT, $start_date );
			} catch ( Exception $e ) {
				$message = $this->messages->get_message( 'event-archive-bad-start-date' );

				return new WP_Error( 'event-archive-bad-start-date', $message, array( 'status' => 400 ) );
			}
		}

		if ( ! empty( $request['end_date'] ) && is_string( $request['end_date'] ) ) {
			$end_date = strtotime( $request['end_date'] );
			// Unix timestamp is a thing...
			$end_date = $end_date ? $end_date : $request['end_date'];
			// at this point if it's legit it should be a number
			if ( ! is_numeric( $end_date ) ) {
				$message = $this->messages->get_message( 'event-archive-bad-end-date' );

				return new WP_Error( 'event-archive-bad-end-date', $message, array( 'status' => 400 ) );
			}
			try {
				$args['end_date'] = date( Tribe__Date_Utils::DBDATETIMEFORMAT, $end_date );
			} catch ( Exception $e ) {
				$message = $this->messages->get_message( 'event-archive-bad-end-date' );

				return new WP_Error( 'event-archive-bad-end-date', $message, array( 'status' => 400 ) );
			}
		}

		$args = wp_parse_args( $args, $defaults );

		$events = tribe_get_events( $args );

		if ( empty( $events ) && $page !== 1 ) {
			$message = $this->messages->get_message( 'event-archive-page-not-found' );

			return new WP_Error( 'event-archive-page-not-found', $message, array( 'status' => 404 ) );
		}

		$data = array( 'events' => array() );

		$data['rest_url'] = tribe_events_rest_url( 'events/' );

		$has_next = ! empty( tribe_get_events( array_merge( $args, array( 'paged' => $page + 1 ) ) ) );
		if ( $has_next ) {
			$data['next_rest_url'] = tribe_events_rest_url( 'events/?page=' . ( $page + 1 ) );
		}

		$has_previous = $page !== 1 && ! empty( tribe_get_events( array_merge( $args, array( 'paged' => $page - 1 ) ) ) );
		if ( $has_previous ) {
			$path = $page === 2 ? 'events/' : 'events/?page=' . ( $page - 1 );
			$data['previous_rest_url'] = tribe_events_rest_url( $path );
		}

		foreach ( $events as $event_id ) {
			$data['events'][] = $this->repository->get_event_data( $event_id );
		}

		return new WP_REST_Response( $data );
	}
}