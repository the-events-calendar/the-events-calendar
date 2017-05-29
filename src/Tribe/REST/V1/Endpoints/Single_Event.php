<?php


class Tribe__Events__REST__V1__Endpoints__Single_Event
	extends Tribe__Events__REST__V1__Endpoints__Base
	implements Tribe__REST__Endpoints__GET_Endpoint_Interface, Tribe__REST__Endpoints__POST_Endpoint_Interface,Tribe__Documentation__Swagger__Provider_Interface {

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
	 * @var Tribe__Events__Validator__Interface
	 */
	protected $validator;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Single_Event constructor.
	 *
	 * @param Tribe__REST__Messages_Interface                  $messages
	 * @param Tribe__Events__REST__Interfaces__Post_Repository $post_repository
	 * @param Tribe__Validator__Interface                      $validator
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $post_repository,
		Tribe__Validator__Interface $validator
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

		$event = get_post( $request['id'] );

		$cap = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->read_post;
		if ( ! ( 'publish' === $event->post_status || current_user_can( $cap, $request['id'] ) ) ) {
			$message = $this->messages->get_message( 'event-not-accessible' );
			return new WP_Error( 'event-not-accessible', $message, array( 'status' => 403 ) );
		}

		$data = $this->post_repository->get_event_data( $request['id'] );

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

	/**
	 * Handles POST requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function post( WP_REST_Request $request ) {
		$this->serving = $request;

		$postarr = array(
			// Post fields
			'post_author'           => $request['author'],
			'post_date'             => Tribe__Date_Utils::reformat( $request['date'], 'Y-m-d H:i:s' ),
			'post_date_gmt'         => Tribe__Timezones::localize_date( 'Y-m-d H:i:s', $request['date_utc'], 'UTC' ),
			'post_title'            => $request['title'],
			'post_content'          => $request['description'],
			'post_excerpt'          => $request['excerpt'],
			// Event data
			'EventTimezone'         => $request['timezone'],
			'EventAllDay'           => tribe_is_truthy( $request['all_day'] ),
			'EventStartDate'        => Tribe__Date_Utils::reformat( $request['start_date'], 'Y-m-d' ),
			'EventStartTime'        => Tribe__Date_Utils::reformat( $request['start_date'], 'H:i:s' ),
			'EventEndDate'          => Tribe__Date_Utils::reformat( $request['end_date'], 'Y-m-d' ),
			'EventEndTime'          => Tribe__Date_Utils::reformat( $request['end_date'], 'H:i:s' ),
			'FeaturedImage'         => $request['image'],
			'EventCost'             => $request['cost'],
			'EventCurrencyPosition' => tribe( 'cost-utils' )->parse_currency_position( $request['cost'] ),
			'EventCurrencySymbol'   => tribe( 'cost-utils' )->parse_currency_symbol( $request['cost'] ),
		);

		$id = Tribe__Events__API::createEvent( array_filter( $postarr ) );

		$data = $this->post_repository->get_event_data( $id );

		$response = new WP_REST_Response( $data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Provides the content of the `args` array to register the endpoint support for GET requests.
	 *
	 * @return array
	 */
	public function GET_args(  ) {
		return array(
			'id' => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_event_id' ) ),
		);
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function POST_args() {
		return array(
			// Post fields
			'author'      => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_user_id' ) ),
			'date'        => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'date_utc'    => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'title'       => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_string' ), ),
			'description' => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'excerpt'     => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			// Event data
			'timezone'    => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_timezone' ) ),
			'all_day'     => array( 'required' => false, 'default' => false ),
			'start_date'  => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'end_date'    => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'image'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_image' ) ),
			'cost'        => array( 'required' => false ),
		);
	}
}
