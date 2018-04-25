<?php

namespace Tribe\Events\Test\Traits\Aggregator;


use Tribe__Events__Aggregator__Record__gCal as Record;

trait RecordMaker {
	protected function make_record( string $import_id, array $meta_overrides = [], string $status = 'pending' ): Record {
		$meta = array_merge( [
			'import_id' => $import_id,
			'preview' => false,
			'origin' => 'gcal',
			'source_name' => 'Test Calendar',
			'source' => 'http://some-gcal.com/ical',
		], $meta_overrides );

		$record = new Record();
		$record->create( 'manual', [], $meta );
		$record->set_status( $status );

		return $record;
	}
}