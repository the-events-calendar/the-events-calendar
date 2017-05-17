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
	protected $post_repository;

	/**
	 * @var Tribe__REST__Validator_Interface
	 */
	protected $validator;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Single_Event constructor.
	 *
	 * @param Tribe__REST__Main $main
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $post_repository,
		Tribe__REST__Validator_Interface $validator
	) {
		parent::__construct( $messages );
		$this->post_repository = $post_repository;
		$this->validator = $validator;
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

	public function post( WP_REST_Request $request ) {
		$this->serving = $request;
		$postarr = array(
			'post_author'    => $request->get_param( 'author' ),
			'post_date'      => $this->localize_date( 'Y-m-d H:i:s', $request->get_param( 'date' ) ),
			'post_date_gmt'  => $this->localize_date( 'Y-m-d H:i:s', $request->get_param( 'date_utc' ) ),
			'post_title'     => $request->get_param( 'title' ),
			'post_content'   => $request->get_param( 'description' ),
			'EventStartDate' => $this->localize_date( 'Y-m-d', $request->get_param( 'start_date' ) ),
			'EventStartTime' => $this->localize_date( 'H:i:s', $request->get_param( 'start_date' ) ),
			'EventEndDate'   => $this->localize_date( 'Y-m-d', $request->get_param( 'end_date' ) ),
			'EventEndTime'   => $this->localize_date( 'H:i:s', $request->get_param( 'end_date' ) ),
		);

		$id = Tribe__Events__API::createEvent( array_filter( $postarr ) );

		$data = $this->post_repository->get_event_data( $id );

		$response = new WP_REST_Response( $data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Provides the content of the `args` array to register the endpoint support for POST requests.
	 *
	 * @return array
	 */
	public function get_post_args() {
		return array(
			'author'      => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_user_id' ) ),
			'date'        => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'date_utc'    => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'title'       => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_string' ), ),
			'description' => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'start_date'  => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'end_date'    => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_time' ) ),
		);
	}

	/**
	 * Localizes a date or timestamp using WordPress timezone and returns it in the specified format.
	 *
	 * @param string $format
	 * @param mixed $date
	 *
	 * @return string
	 */
	protected function localize_date( $format, $date ) {
		$timezone = Tribe__Timezones::generate_timezone_string_from_utc_offset( Tribe__Timezones::wp_timezone_string() );
		if ( Tribe__Date_Utils::is_timestamp( $date ) ) {
			$date = new DateTime();
			$date->setTimestamp( $date );
			$date->setTimezone( new DateTimeZone( $timezone ) );
		} else {
			$date = new DateTime( $date, new DateTimeZone( $timezone ) );
		}

		return $date->format( $format );
	}
}