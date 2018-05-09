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
	 * Whether teh current import ID exists and is for a record that needs data.
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
		$args    = array( 'post_status' => Tribe__Events__Aggregator__Records::$status->pending );
		$record  = $records->get_by_import_id( $import_id, $args );

		$this->current_record = $record;

		return $record instanceof Tribe__Events__Aggregator__Record__Abstract;
	}

	/**
	 * Whether the batch hash is the expected one or not.
	 *
	 * @since 4.6.15
	 *
	 * @param string $batch_hash
	 *
	 * @return bool
	 */
	public function is_expected_batch_hash( $batch_hash ) {
		if ( ! $this->current_record instanceof Tribe__Events__Aggregator__Record__Abstract ) {
			return false;
		}

		return $this->current_record->meta['next_batch_hash'] === $batch_hash;
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
		return is_numeric( $interval ) && $interval >= $this->interval_min && $interval <= $this->interval_max;
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