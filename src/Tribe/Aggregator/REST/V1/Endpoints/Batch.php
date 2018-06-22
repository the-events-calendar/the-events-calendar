<?php

/**
 * Class Tribe__Events__Aggregator__REST__V1__Endpoints__Batch
 *
 * @since 4.6.15
 *
 * An endpoint dedicated to processing events in batches.
 */
class Tribe__Events__Aggregator__REST__V1__Endpoints__Batch
	extends
	Tribe__Events__Aggregator__REST__V1__Endpoints__Base
	implements Tribe__REST__Endpoints__CREATE_Endpoint_Interface {

	/**
	 * @var Tribe__Events__Aggregator__Record__Abstract
	 */
	protected $current_record;

	/**
	 * The minimum interval, in seconds.
	 *
	 * @var int
	 */
	protected $interval_min = 10;

	/**
	 * The maximum interval, in seconds.
	 *
	 * @var
	 */
	protected $interval_max = 600;

	/**
	 * Handles a batch processing request sent by the server.
	 *
	 * @since 4.6.15
	 *
	 * @param WP_REST_Request $request
	 * @param bool $return_id
	 *
	 * @return int|WP_Error|WP_REST_Response
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

		$is_last_batch = (int) $request['status']['batch']['total'] === ( (int) $request['status']['batch']['done'] + 1 );

		/** @var Tribe__Events__Aggregator__Record__Activity $activity */
		$activity = $record->insert_posts( $items );

		$is_success  = $activity->get_last_status() === Tribe__Events__Aggregator__Record__Activity::STATUS_SUCCESS;
		$is_finished = ( $is_last_batch && $is_success );
		$new_status  = $is_finished ? 'success' : 'pending';

		if ( $is_success ) {
			$record->update_meta( 'percentage_complete', $request['percentage_complete'] );
		}

		/**
		 * Allows filtering the interval between a finished batch process and the closest push of the next one
		 * from the server.
		 *
		 * @since 4.6.15
		 *
		 * @param int $interval A value in seconds; defaults to 10.
		 * @param WP_REST_Request The current batch import request.
		 */
		$interval = apply_filters( 'event_aggregator_event_batch_interval', $this->interval_min, $request );

		$interval = is_numeric( $interval ) && (int) $interval > $this->interval_min
			? min( $this->interval_max, (int) $interval )
			: $this->interval_min;

		$response_data = array(
			'status' => 'success',
			'activity' => $activity->get_items(),
			'interval' => $interval,
		);

		if ( $is_finished ) {
			$record->delete_meta( 'next_batch_hash' );
		} else {
			$next_batch_hash = $record->generate_next_batch_hash();
			$record->update_meta( 'next_batch_hash', $next_batch_hash );
			$response_data['next_batch_hash'] = $next_batch_hash;
		}

		$record->set_status( $new_status );

		return new WP_REST_Response( $response_data, 200 );
	}

	/**
	 * {@inheritdoc}
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
			'percentage_complete' => array(
				'required' => true,
				'type' => 'integer',
				'validate_callback' => array( $this, 'is_percentage' ),
				'description' => __( 'The percentage of import completed.', 'the-events-calendar' ),
			),
			'interval' => array(
				'required' => false,
				'type' => 'integer',
				'validate_callback' => array( $this, 'is_valid_interval' ),
				'description' => __( 'The current interval, in seconds, between the end of a batch process and the start of the next; as set on the server.', 'the-events-calendar' ),
			),
		);
	}

	/**
	 * Whether batch imports are supported or not.
	 */
	public function can_create() {
		/**
		 * Whether batch imports are allowed or not.
		 *
		 * @since 4.6.15
		 *
		 * @param bool $can_create
		 */
		return apply_filters( 'tribe_aggregator_batch_data_processing_enabled', true );
	}


	/**
	 * Validates the status information sent by the server.
	 *
	 * @since 4.6.15
	 *
	 * @param object $status
	 *
	 * @return bool
	 */
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
