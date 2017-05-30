<?php


class Tribe__Events__REST__V1__Endpoints__Single_Venue
	extends Tribe__Events__REST__V1__Endpoints__Base
	implements Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface {

	/**
	 * @var Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $post_repository;

	/**
	 * @var Tribe__Events__REST__V1__Validator__Interface
	 */
	protected $validator;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Single_Venue constructor.
	 *
	 * @param Tribe__REST__Messages_Interface                  $messages
	 * @param Tribe__Events__REST__Interfaces__Post_Repository $post_repository
	 * @param Tribe__Events__REST__V1__Validator__Interface    $validator
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $post_repository,
		Tribe__Events__REST__V1__Validator__Interface $validator
	) {
		parent::__construct( $messages );
		$this->post_repository = $post_repository;
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
		// TODO: Implement get() method.
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function GET_args() {
		// TODO: Implement GET_args() method.
	}

	/**
	 * Inserts a post of the linked post type.
	 *
	 * @param int|array $data Either an existing linked post ID or the linked post data.
	 *
	 * @return false|array|WP_Error `false` if the linked post data is empty, the linked post ID (in an array as requested by the
	 *                              linked posts engine) or a `WP_Error` if the linked post insertion failed.
	 */
	public function insert( $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		if ( tribe_is_venue( $data ) ) {
			return array( 'VenueID' => $data );
		}

		$data_request = new WP_REST_Request();
		$data_request->set_param( 'args', $this->POST_args() );

		$body_params = (array) $data;
		foreach ( $body_params as $key => $value ) {
			$data_request->set_param( $key, $value );
		}

		$venue_id = $this->post( $data_request, true );

		if ( $venue_id instanceof WP_Error ) {
			return $venue_id;
		}

		return array( 'VenueID' => $venue_id );
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
			'author'        => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_user_id' ) ),
			'date'          => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'date_utc'      => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_time' ) ),
			'venue'         => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_string' ), ),
			'description'   => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'status'        => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_post_status' ) ),
			// Venue meta fields
			'show_map'      => array( 'required' => false ),
			'show_map_link' => array( 'required' => false ),
			'address'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'city'          => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'country'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'province'      => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'state'         => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'zip'           => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'phone'         => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'stateprovince' => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_string' ) ),
			'website'       => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_url' ) ),
		);
	}

	/**
	 * Handles POST requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 * @param bool            $return_id Whether the created post ID should be returned or the full response object.
	 *
	 * @return WP_Error|WP_REST_Response|int An array containing the data on success or a WP_Error instance on failure.
	 */
	public function post( WP_REST_Request $request, $return_id = false ) {
		$postarr = array(
			'VenueID'       => $request['id'],
			'post_author'   => $request['author'],
			'post_date'     => Tribe__Date_Utils::reformat( $request['date'], 'Y-m-d H:i:s' ),
			'post_date_gmt' => Tribe__Timezones::localize_date( 'Y-m-d H:i:s', $request['date_utc'], 'UTC' ),
			'post_status'   => $this->scale_back_post_status( $request['status'], Tribe__Events__Main::POSTTYPE ),
			'Venue'         => $request['venue'],
			'Description'   => $request['description'],
			'Address'       => $request['address'],
			'City'          => $request['city'],
			'Country'       => $request['country'],
			'Province'      => $request['province'],
			'State'         => $request['state'],
			'Zip'           => $request['zip'],
			'Phone'         => $request['phone'],
			'ShowMap'       => tribe_is_truthy( $request['show_map'] ),
			'ShowMapLink'   => tribe_is_truthy( $request['show_map_link'] ),
		);

		$id = Tribe__Events__Venue::instance()->create( array_filter( $postarr ) );

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'could-not-create-venue' );

			return new WP_Error( 'could-not-create-venue', $message, array( 'status' => 400 ) );
		}

		return $return_id ? $id : $this->post_repository->get_venue_data( $id );
	}
}