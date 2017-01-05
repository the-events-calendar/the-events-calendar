<?php


class Tribe__Events__REST__V1__Endpoints__Single_Event extends Tribe__Events__REST__V1__Endpoints__Base implements
	Tribe__REST__Endpoints__Endpoint_Interface {

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
	private $post_repository;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Single_Event constructor.
	 *
	 * @param Tribe__REST__Main $main
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $post_repository
	) {
		parent::__construct( $messages );
		$this->post_repository = $post_repository;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$this->serving = $request;

		$id = $request['id'];

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'missing-event-id' );

			return new WP_Error( 'missing-event-id', $message, array( 'status' => 400 ) );
		}

		$event = get_post( $id );

		if ( empty( $event ) || ! tribe_is_event( $id ) ) {
			$message = $this->messages->get_message( 'event-not-found' );

			return new WP_Error( 'event-not-found', $message, array( 'status' => 404 ) );
		}

		if ( ! ( 'publish' === $event->post_status || current_user_can( 'edit_posts', $id ) ) ) {
			$message = $this->messages->get_message( 'event-not-accessible' );

			return new WP_Error( 'event-not-accessible', $message, array( 'status' => 403 ) );
		}

		$data = $this->post_repository->get_event_data( $id );

		return is_wp_error( $data ) ? $data : new WP_REST_Response( $data );
	}
}