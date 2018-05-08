<?php

class Tribe__Events__Aggregator__REST__V1__Endpoints__State
	extends Tribe__Events__Aggregator__REST__V1__Endpoints__Base
	implements Tribe__REST__Endpoints__CREATE_Endpoint_Interface {

	/**
	 * Handles POST requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 * @param bool $return_id Whether the created post ID should be returned or the full response object.
	 *
	 * @return WP_Error|WP_REST_Response|int An array containing the data on success or a WP_Error instance on failure.
	 */
	public function create( WP_REST_Request $request, $return_id = false ) {
		/** @var Tribe__Events__Aggregator__Records $records */
		$records = tribe( 'events-aggregator.records' );

		/** @var Tribe__Events__Aggregator__Record__Abstract $record */
		$record = $records->get_by_import_id( $request['import_id'], array( 'post_status' => 'any' ) );

		$status = $request['status'];

		$updated = $record->set_status( $status );

		if ( empty( $updated ) ) {
			$updated = new WP_Error( "Could not update the status of import {$record->id} to {$status}; current record status is {$record->post->post_status}" );
		}

		if ( $updated instanceof WP_Error ) {
			// the REST API will cast it to an error
			return $updated;
		}

		$record->update_meta( 'percentage_complete', $request['percentage_complete'] );

		if ( $status !== 'pending' ) {
			$record->delete_meta( 'next_batch_hash' );
		}

		return new WP_REST_Response( array( 'status' => 'success' ), 200 );
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function CREATE_args() {
		return array(
			'import_id' => array(
				'required' => true,
				'type' => 'string',
				'validate_callback' => array( $this, 'is_valid_import_id' ),
				'description' => __( 'The import unique ID as provided by Event Aggregator service', 'the-events-calendar' ),
			),
			'batch_hash' => array(
				'required' => true,
				'type' => 'string',
				'validate_callback' => array( $this, 'is_expected_batch_hash' ),
				'description' => __( 'The hash of the next expected batch, as previously provided by the client', 'the-events-calendar' ),
			),
			'status' => array(
				'required' => true,
				'type' => 'string',
				'enum' => array( 'success', 'failed', 'pending' ),
				'description' => __( 'The new status of the import.', 'the-events-calendar' ),
			),
			'message' => array(
				'required' => true,
				'type' => 'string',
				'description' => __( 'The new status message for the user, not localized.', 'the-events-calendar' ),
			),
			'message_slug' => array(
				'required' => true,
				'type' => 'string',
				'description' => __( 'The new status message slug, to allow for localized messages.', 'the-events-calendar' ),
			),
			'percentage_complete' => array(
				'required' => true,
				'type' => 'integer',
				'validate_callback' => array( $this, 'is_percentage' ),
				'description' => __( 'The percentage of import completed.', 'the-events-calendar' ),
			),
		);
	}

	/**
	 * Whether the current user can create content of the specified type or not.
	 *
	 * @return bool Whether the current user can post or not.
	 */
	public function can_create() {
		/**
		 * Whether remotely setting a status for records is allowed or not.
		 *
		 * @since 4.6.15
		 *
		 * @param bool $can_create
		 */
		return apply_filters( 'tribe_aggregator_remote_status_enabled', true );
	}
}