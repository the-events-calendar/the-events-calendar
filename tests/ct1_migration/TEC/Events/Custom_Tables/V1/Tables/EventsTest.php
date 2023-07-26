<?php

namespace TEC\Events\Custom_Tables\V1\Tables;

require_once __DIR__ . '/Tables_Test_Case.php';

class EventsTest extends Tables_Test_Case {
	protected function get_insert_query( object $data ): string {
		$table_name = Events::table_name( true );

		return "INSERT INTO $table_name (
			event_id, post_id, start_date, start_date_utc, end_date, end_date_utc, timezone, duration, hash
			) VALUES (
			1,
			1,
			'$data->start_date',
			'$data->start_date_utc',
			'$data->end_date',
			'$data->end_date_utc',
			'America/New_York',
			7200,
			'random_hash'
			)";
	}
}