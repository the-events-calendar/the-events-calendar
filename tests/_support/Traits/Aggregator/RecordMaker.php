<?php

namespace Tribe\Events\Test\Traits\Aggregator;

use Tribe__Events__Aggregator__Record__gCal as Record;

trait RecordMaker {
	/**
	 * Creates an Event Aggregator post for a manual record.
	 *
	 * @since 4.6.26
	 *
	 * @param string $import_id      The record import ID, should be a unique string.
	 * @param array  $meta_overrides An array of overrides of the default meta.
	 * @param string $status         The record status, e.g. pending, failed, success.
	 *
	 * @return Record The built record object.
	 */
	protected function make_manual_record( string $import_id, array $meta_overrides = [], string $status = 'pending' ): Record {
		$meta = array_merge(
			[
				'import_id'   => $import_id,
				'preview'     => false,
				'origin'      => 'gcal',
				'source_name' => 'Test Calendar',
				'source'      => 'http://some-gcal.com/ical',
			], $meta_overrides
		);

		$record = new Record();
		$record->create( 'manual', [], $meta );
		$record->set_status( $status );

		return $record;
	}

	/**
	 * Creates an Event Aggregator post for a schedule record.
	 *
	 * @since 4.6.26
	 *
	 * @param string $import_id      The record import ID, should be a unique string.
	 * @param array  $meta_overrides An array of overrides of the default meta.
	 *
	 * @return Record The built record object.
	 */
	protected function make_schedule_record( string $import_id, array $meta_overrides = [] ): Record {
		$meta = array_merge(
			[
				'import_id'   => $import_id,
				'preview'     => false,
				'origin'      => 'gcal',
				'source_name' => 'Test Calendar',
				'source'      => 'http://example.com/calendar',
				'frequency'   => 'every30mins',
				'type'        => 'schedule',
			],
			$meta_overrides
		);

		$record = new Record();
		$record->create( 'schedule', [], $meta );
		$record->set_status( 'schedule' );

		return $record;
	}
}
