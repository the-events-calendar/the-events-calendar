<?php

namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Field;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;
class Test_Schema_Field extends Abstract_Custom_Field {

	public function fields() {
		return ['rset'];
	}
	public function table_schema() {
		return tribe(EventsSchema::class);
	}

	public function get_update_sql() {
		global $wpdb;
		$table_name = $this->table_schema()::table_name(true);
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE `{$table_name}` (
			`rset` LONGTEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			) {$charset_collate};";
	}
}

class Schema_BuilderTest extends \CT1_Migration_Test_Case {
	use CT1_Fixtures;

	/**
	 * @after each test make sure the custom tables will be there for the following ones.
	 */
	public function recreate_custom_tables() {
		$events_updated = ( new EventsSchema )->update();
		if ( ! $events_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
		$occurrences_updated = ( new OccurrencesSchema() )->update();
		if ( ! $occurrences_updated ) {
			throw new \RuntimeException( 'Failed to create Events custom table.' );
		}
	}


	/**
	 *
	 *
	 * @test
	 */
	public function should_up_schema() {
		global $wpdb;
		$schema_builder = tribe(Schema_Builder::class);

		// Activate.
		$up = $schema_builder->up();

		// Validate expected state.
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertContains( EventsSchema::table_name( true ), $tables );
		//$this->assertNotEmpty($up);
	}

	/**
	 * @test
	 */
	public function should_down_schema() {
		global $wpdb;
		$schema_builder = tribe(Schema_Builder::class);

		// Activate.
		$schema_builder->down();

		// Validate expected state.
		$q      = 'show tables';
		$tables = $wpdb->get_col( $q );
		$this->assertNotContains( EventsSchema::table_name( true ), $tables );
	}

	/**
	 * @test
	 */
	public function should_up_field_schema() {
		global $wpdb;
		$schema_builder = tribe(Schema_Builder::class);
		$field_schema = tribe(Test_Schema_Field::class);
		add_filter('tec_events_custom_tables_v1_field_schemas', function($fields) use ($field_schema) {
			return array_merge($fields, [$field_schema]);
		});
		// Activate.
		$schema_builder->up();

		// Validate expected state.
		$q      = 'show create table '.$field_schema->table_schema()::table_name(true);
		$table = $wpdb->get_row( $q );
		$table_def = $table->{'Create Table'};
		foreach ($field_schema->fields() as $field) {
			$this->assertContains( $field, $table_def );
		}
	}

}
