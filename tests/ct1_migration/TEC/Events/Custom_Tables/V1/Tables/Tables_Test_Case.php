<?php

namespace TEC\Events\Custom_Tables\V1\Tables;

use stdClass;
use PDO;

abstract class Tables_Test_Case extends \CT1_Migration_Test_Case {
	abstract protected function get_insert_query( stdClass $data ): string;

	public function date_field_provider(): array {
		return [
			'start_date'     => [ 'start_date' ],
			'end_date'       => [ 'end_date' ],
			'start_date_utc' => [ 'start_date_utc' ],
			'end_date_utc'   => [ 'end_date_utc' ],
		];
	}

	/**
	 * Test that an illegal DATETIME operation when sql_mode is set to no 'NO_ZERO_IN_DATE,NO_ZERO_DATE'
	 * will not trigger a MySQL warning.
	 *
	 * Depending on the MySQL version and configuration, treating DATETIME fields like strings will
	 * trigger a warning, which will cause the query to fail.
	 * Here the warning is triggered on an INSERT query, but it could happen on a SELECT query too.
	 * This test ensures that the warning is not triggered and that the date fields of the Occurrences table
	 * will keep being VARCHAR fields.
	 *
	 * This test uses a PDO connection as the WordPress DB class does will set the sql_mode before this test
	 * runs. The default sql_mode would remove the `NO_ZERO_DATE` flag, but it's filterable.
	 *
	 * @dataProvider date_field_provider
	 */
	public function test_partial_match_on_date_fields_does_not_generate_warnings( string $field ): void {

		// Using the DB_ constants, open a PDO connection to the same database as WordPress.
		$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
		$pdo = new PDO( $dsn, DB_USER, DB_PASSWORD );
		// Cast warnings to exceptions.
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		// Add  SQL modes that will cause issues with the dates.
		$sql_mode = $pdo->query( 'SELECT @@SESSION.sql_mode' )->fetchColumn();
		// Debug the current sql_mode before the update.
		codecept_debug( 'sql_mode before update:' . $sql_mode );
		foreach (
			[
				'NO_ZERO_IN_DATE',
				'NO_ZERO_DATE',
			] as $add_sql_mode
		) {
			if ( strpos( $sql_mode, $add_sql_mode ) === false ) {
				$sql_mode .= ',' . $add_sql_mode;
			}
		}
		// Set the new sql_mode.
		if ( $pdo->query( 'SET @@SESSION.sql_mode="' . $sql_mode . '"' ) === false ) {
			$this->fail( 'Could not set PDO sql_mode to ' . $sql_mode );
		}
		// Debug the current sql_mode.
		$sql_mode = $pdo->query( 'SELECT @@SESSION.sql_mode' )->fetchColumn();
		codecept_debug( 'sql_mode after update: ' . $sql_mode );

		// Insert a record in the Occurrences table using PDO, this should not fail.
		$data         = (object) array_merge( [
			'start_date'     => '2019-01-01 09:00:00',
			'end_date'       => '2019-01-01 11:00:00',
			'start_date_utc' => '2019-01-01 08:00:00',
			'end_date_utc'   => '2019-01-01 10:00:00',
		], [ $field => '0000-00-00 00:00:00' ] );
		$insert_query = $this->get_insert_query( $data );
		$inserted     = $pdo->query( $insert_query );

		$this->assertNotFalse( $inserted );
		$warnings = $pdo->query( 'SHOW WARNINGS' )->fetchAll( PDO::FETCH_ASSOC );
		$this->assertEmpty( $warnings );
	}
}