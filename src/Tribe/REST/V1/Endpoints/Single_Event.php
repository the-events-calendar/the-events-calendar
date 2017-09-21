<?php


class Tribe__Events__REST__V1__Endpoints__Single_Event
	extends Tribe__Events__REST__V1__Endpoints__Base
	implements Tribe__REST__Endpoints__Endpoint_Interface, Tribe__Documentation__Swagger__Provider_Interface {

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

		$cap = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->read_post;
		if ( ! ( 'publish' === $event->post_status || current_user_can( $cap, $id ) ) ) {
			$message = $this->messages->get_message( 'event-not-accessible' );

			return new WP_Error( 'event-not-accessible', $message, array( 'status' => 403 ) );
		}

		$data = $this->post_repository->get_event_data( $id );

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
		return array(
			'get' => array(
				'parameters' => array(
					array(
						'name'        => 'id',
						'in'          => 'path',
						'description' => __( 'the event post ID', 'the-events-calendar' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Returns the data of the event with the specified post ID', 'the-event-calendar' ),
						'schema'      => array(
							'$ref' => '#/definitions/Event',
						),
					),
					'400' => array(
						'description' => __( 'The event post ID is missing.', 'the-events-calendar' )
					),
					'403' => array(
						'description' => __( 'The event with the specified ID is not accesible.', 'the-events-calendar' )
					),
					'404' => array(
						'description' => __( 'An event with the specified event does not exist.', 'the-events-calendar' )
					),
				),
			),
		);
	}
}
