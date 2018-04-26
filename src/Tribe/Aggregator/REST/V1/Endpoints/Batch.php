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

		if ( $request['force_import_status'] ) {
			$activity            = $record->activity();
			$force_import_status = $request['force_import_status'];
			$is_finished         = 'pending' !== $force_import_status;
			$new_status          = $force_import_status;
		} else {
			if ( empty( $record->meta['post_status'] ) ) {
				$record->update_meta( 'post_status', tribe( 'events-aggregator.settings' )->default_post_status( $record->meta['origin'] ) );
			}

			// let's make sure it's a nested object
			$items = json_decode( json_encode( $request['events'] ) );

			$is_last_batch = (int) $request['status']['batch']['total'] === ( (int) $request['status']['batch']['done'] + 1 );

			/** @var Tribe__Events__Aggregator__Record__Activity $activity */
			$activity = $record->insert_posts( $items );

			$is_finished = ( $is_last_batch && $activity->get_last_status() === Tribe__Events__Aggregator__Record__Activity::STATUS_SUCCESS );
			$new_status  = $is_finished ? 'success' : 'pending';
		}

		$default_interval = 10;
		$max_interval = 600;

		/**
		 * Allows filtering the interval between a finished batch process and the closest push of the next one
		 * from the server.
		 *
		 * @since TBD
		 *
		 * @param int $interval A value in seconds; defaults to 10.
		 * @param WP_REST_Request The current batch import request.
		 */
		$interval = apply_filters( 'event_aggregator_event_batch_interval', $default_interval, $request );

		$interval = is_numeric( $interval ) && (int) $interval > $default_interval
			? min( $max_interval, (int) $interval )
			: $default_interval;

		$response_data = array(
			'status' => 'success',
			'activity' => $activity->get_items(),
			'interval' => $interval,
		);

		if ( $is_finished ) {
			$record->delete_meta( 'next_batch_hash' );
		} else {
			$next_batch_hash = md5( uniqid( '', true ) );
			$record->update_meta( 'next_batch_hash', $next_batch_hash );
			$response_data['next_batch_hash'] = $next_batch_hash;
		}

		$record->set_status( $new_status );

		return new WP_REST_Response( $response_data, 200 );
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
				'type' => 'object',
				'validate_callback' => array( $this, 'is_valid_status_information' ),
				'description' => __( 'The current status of the import.', 'the-events-calendar' ),
			),
			'interval' => array(
				'required' => false,
				'type' => 'integer',
				'description' => __( 'The current interval, in seconds, between the end of a batch process and the start of the next; as set on the server.', 'the-events-calendar' ),
			),
			'force_import_status' => array(
				'required' => false,
				'type' => 'string',
				'enum' => array( 'success', 'failed', 'pending' ),
				'description' => __( 'A forcefully set by the server new status for the import.', 'the-events-calendar' ),
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

	public function is_valid_status_information( $status ) {
		return is_array( $status )
		       && isset( $status['data']['total'] ) && is_numeric( $status['data']['total'] )
		       && isset( $status['data']['done'] ) && is_numeric( $status['data']['done'] )
		       && (int) $status['data']['done'] <= (int) $status['data']['total']
		       && isset( $status['batch']['total'] ) && is_numeric( $status['batch']['total'] )
		       && isset( $status['batch']['done'] ) && is_numeric( $status['batch']['done'] )
		       && (int) $status['batch']['done'] <= (int) $status['batch']['total'];
	}
}
