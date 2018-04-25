<?php

class Tribe__Events__Aggregator__REST__V1__Endpoints__Batch
	implements Tribe__REST__Endpoints__CREATE_Endpoint_Interface {

	/**
	 * @var Tribe__Events__Aggregator__Record__Abstract
	 */
	protected $current_record;

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

		if ( empty( $record->meta['post_status'] ) ) {
			$record->update_meta( 'post_status', tribe( 'events-aggregator.settings' )->default_post_status( $record->meta['origin'] ) );
		}

		// let's make sure it's a nested object
		$items = json_decode( json_encode( $request['events'] ) );

		$activity = $record->insert_posts( $items );

		$interval = apply_filters( 'event_aggregator_event_batch_interval', 10, $request );

		$next_batch_hash = md5( uniqid( '', true ) );

		$record->update_meta( 'next_batch_hash', $next_batch_hash );

		return new WP_REST_Response( array(
			'status' => 'success',
			'activity' => $activity->get_items(),
			'next_batch_hash' => $next_batch_hash,
			'interval' => $interval,
		), 200 );
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
		);
	}

	public function can_create() {
		return apply_filters( 'tribe_aggregator_batch_data_processing_enabled', true );
	}

	public function is_valid_import_id( $import_id ) {
		/** @var Tribe__Events__Aggregator__Records $records */
		$records = tribe( 'events-aggregator.records' );
		$args    = array( 'post_status' => Tribe__Events__Aggregator__Records::$status->pending );
		$record  = $records->get_by_import_id( $import_id, $args );

		$this->current_record = $record;

		return $record instanceof Tribe__Events__Aggregator__Record__Abstract;
	}

	public function is_expected_batch_hash( $batch_hash ) {
		if ( ! $this->current_record instanceof Tribe__Events__Aggregator__Record__Abstract ) {
			return false;
		}

		return $this->current_record->meta['next_batch_hash'] === $batch_hash;
	}
}
