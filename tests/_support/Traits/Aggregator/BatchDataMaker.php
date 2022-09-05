<?php


namespace Tribe\Events\Test\Traits\Aggregator;


use Tribe\Events\Test\Factories\Aggregator\V1\Import_Record;

trait BatchDataMaker {

	protected function make_batch_data( array $overrides = [], int $events_count = 10 ): array {
		$import_data = new Import_Record();

		if ( isset( $overrides['origin'] ) ) {
			$origin = $overrides['origin'];
			unset( $overrides['origin'] );
		} else {
			$origin = 'ical';
		}

		$events = [];
		for ( $i = 0; $i < $events_count; $i ++ ) {
			$events[] = $import_data->create_and_get_event_data( $origin );
		}

		$data = array_merge( [
			'batch_hash' => '2389',
			'events'     => array_map( static function ( $event ) {
				return (array) $event;
			}, $events ),
			'status' => [
				'data' => [
					'total' => 89,
					'done' => 20,
				],
				'batch' => [
					'per_batch' => $events_count,
					'total' => 9,
					'done' => 2,
				],
			],
			'percentage_complete' => 18,
			'interval' => 10,
		], $overrides );

		// Recursively cast data to array.
		return json_decode( json_encode( $data ), true );
	}
}