<?php


class Tribe__Events__REST__V1__Endpoints__Single_Venue
	extends Tribe__Events__REST__V1__Endpoints__Linked_Post_Base
	implements Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface {
	/**
	 * @var string
	 */
	protected $post_type = Tribe__Events__Main::VENUE_POST_TYPE;

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		// @todo Implement get() method.
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
			'venue'         => array( 'required' => true, 'validate_callback' => array( $this->validator, 'is_string' ) ),
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
			'image'         => array( 'required' => false, 'validate_callback' => array( $this->validator, 'is_image' ) ),
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
			$this->get_id_index() => $request['id'],
			'post_author'         => $request['author'],
			'post_date'           => Tribe__Date_Utils::reformat( $request['date'], 'Y-m-d H:i:s' ),
			'post_date_gmt'       => Tribe__Timezones::localize_date( 'Y-m-d H:i:s', $request['date_utc'], 'UTC' ),
			'post_status'         => $this->scale_back_post_status( $request['status'], Tribe__Events__Main::POSTTYPE ),
			'Venue'               => $request['venue'],
			'Description'         => $request['description'],
			'Address'             => $request['address'],
			'City'                => $request['city'],
			'Country'             => $request['country'],
			'Province'            => $request['province'],
			'State'               => $request['state'],
			'Zip'                 => $request['zip'],
			'Phone'               => $request['phone'],
			'ShowMap'             => tribe_is_truthy( $request['show_map'] ),
			'ShowMapLink'         => tribe_is_truthy( $request['show_map_link'] ),
			'FeaturedImage'       => tribe_upload_image( $request['image'] ),
		);

		$id = Tribe__Events__Venue::instance()->create( array_filter( $postarr ) );

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'could-not-create-venue' );

			return new WP_Error( 'could-not-create-venue', $message, array( 'status' => 400 ) );
		}

		return $return_id ? $id : $this->post_repository->get_venue_data( $id );
	}

	/**
	 * Returns the post type handled by this linked post endpoint.
	 *
	 * @return string
	 */
	protected function get_post_type() {
		return Tribe__Events__Main::VENUE_POST_TYPE;
	}

	/**
	 * Whether the data represents a valid post type ID.
	 *
	 * @param mixed $data
	 *
	 * @return bool
	 */
	protected function is_post_type( $data ) {
		return tribe_is_venue( $data );
	}
}
