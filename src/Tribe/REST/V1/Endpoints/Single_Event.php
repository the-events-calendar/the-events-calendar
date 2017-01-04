<?php


class Tribe__Events__REST__V1__Endpoints__Single_Event extends Tribe__Events__REST__V1__Endpoints__Base implements
	Tribe__REST__Endpoints__Endpoint_Interface {

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$id = $request['id'];

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'missing-event-id' );

			return new WP_Error( 'missing-event-id', $message, array( 'status' => 400 ) );
		}

		if ( ! tribe_is_event( $id ) ) {
			$message = $this->messages->get_message( 'event-not-found' );

			return new WP_Error( 'event-not-found', $message, array( 'status' => 404 ) );
		}

		if ( ! current_user_can( 'read', $id ) ) {
			$message = $this->messages->get_message( 'event-not-accessible' );

			return new WP_Error( 'event-not-accessible', $message, array( 'status' => 403 ) );
		}

		return new WP_REST_Response();
	}
}