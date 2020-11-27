<?php


class Tribe__Events__REST__V1__Endpoints__Single_Venue_Slug
	extends Tribe__Events__REST__V1__Endpoints__Single_Venue {

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$venue = get_page_by_path( $request['slug'], OBJECT, Tribe__Events__Venue::POSTTYPE );

		$venue_id = 0;

		if ( $venue ) {
			$venue_id = $venue->ID;
		}

		$request->set_param( 'id', $venue_id );

		return parent::get( $request );
	}

	/**
	 * Provides the content of the `args` array to register the endpoint support for GET requests.
	 *
	 * @return array
	 */
	public function READ_args() {
		return [
			'slug' => [
				'in'                => 'path',
				'type'              => 'string',
				'description'       => __( 'the venue post name', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_venue_slug' ],
			],
		];
	}

	/**
	 * Handles DELETE requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the trashed post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function delete( WP_REST_Request $request ) {
		$venue = get_page_by_path( $request['slug'], OBJECT, Tribe__Events__Venue::POSTTYPE );

		$venue_id = 0;

		if ( $venue ) {
			$venue_id = $venue->ID;
		}

		$request->set_param( 'id', $venue_id );

		return parent::delete( $request );
	}

	/**
	 * Handles UPDATE requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the updated post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function update( WP_REST_Request $request ) {
		$venue = get_page_by_path( $request['slug'], OBJECT, Tribe__Events__Venue::POSTTYPE );

		$venue_id = 0;

		if ( $venue ) {
			$venue_id = $venue->ID;
		}

		$request->set_param( 'id', $venue_id );

		return parent::update( $request );
	}
}
