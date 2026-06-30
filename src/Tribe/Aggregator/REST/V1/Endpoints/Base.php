<?php

/**
 * Class Tribe__Events__Aggregator__REST__V1__Endpoints__Base
 *
 * @since 4.6.15
 *
 * The base for the Aggregator endpoints.
 */
abstract class Tribe__Events__Aggregator__REST__V1__Endpoints__Base {
	/**
	 * Whether the current import ID exists and is for a record that needs data.
	 *
	 * @since 4.6.15
	 *
	 * @param string $import_id
	 *
	 * @return bool
	 */
	public function is_valid_import_id( $import_id ) {
		/** @var Tribe__Events__Aggregator__Records $records */
		$records = tribe( 'events-aggregator.records' );
		$args    = [ 'post_status' => Tribe__Events__Aggregator__Records::$status->pending ];
		$record  = $records->get_by_import_id( $import_id, $args );

		$this->current_record = $record;

		return $record instanceof Tribe__Events__Aggregator__Record__Abstract;
	}

	/**
	 * Whether the batch hash is the expected one or not.
	 *
	 * The batch hash is the per-record shared secret that identifies a request as originating
	 * from the Event Aggregator service. It is generated locally when the import is queued and
	 * shared with the service, and is validated for every request to the endpoint.
	 *
	 * @since 4.6.15
	 * @since TBD Harden the batch hash validation and fail closed when no hash is present on the record.
	 *
	 * @param string          $batch_hash The batch hash provided by the request.
	 * @param WP_REST_Request $request    The current request.
	 *
	 * @return bool Whether the provided batch hash matches the one expected for the record.
	 */
	public function is_expected_batch_hash( $batch_hash, WP_REST_Request $request ) {
		if ( ! $this->current_record instanceof Tribe__Events__Aggregator__Record__Abstract ) {
			return false;
		}

		$expected_hash = $this->current_record->meta['next_batch_hash'] ?? '';

		// Fail closed: without a stored hash there is nothing to validate the request against.
		if ( ! is_string( $expected_hash ) || '' === $expected_hash ) {
			return false;
		}

		return is_string( $batch_hash ) && hash_equals( $expected_hash, $batch_hash );
	}

	/**
	 * Whether the interval is an acceptable one or not.
	 *
	 * @since 4.6.15
	 *
	 * @param int $interval
	 *
	 * @return bool
	 */
	public function is_valid_interval( $interval ) {
		return is_numeric( $interval );
	}

	/**
	 * Whether the specified percentage is legit or not.
	 *
	 * @since 4.6.15
	 *
	 * @param int $percentage
	 *
	 * @return bool
	 */
	public function is_percentage( $percentage ) {
		return is_numeric( $percentage )
		       && (int) $percentage >= 0
		       && (int) $percentage <= 100;
	}
}
