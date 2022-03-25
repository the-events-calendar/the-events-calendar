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
		return ['bob', 'frank'];
	}
	public function table_schema() {
		return tribe(EventsSchema::class);
	}

	public function get_update_sql() {
		global $wpdb;
		$table_name = $this->table_schema()::table_name(true);
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE `{$table_name}` (
			`bob` LONGTEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			`frank` TINYINT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
	public function should_up_down_table_schema() {
		$schema_builder = tribe(Schema_Builder::class);

		// Activate.
		$schema_builder->up();

		// Validate expected state.
		$tables = $this->get_tables();
		$this->assertContains( EventsSchema::table_name( true ), $tables );

		$schema_builder->down();

		// Validate expected state.
		$tables = $this->get_tables();
		$this->assertNotContains( EventsSchema::table_name( true ), $tables );
	}


	/**
	 * @test
	 */
	public function should_up_down_field_schema() {
		$schema_builder = tribe( Schema_Builder::class );
		$field_schema   = tribe( Test_Schema_Field::class );
		$this->given_a_field_schema_exists($field_schema);
		// Activate.
		$schema_builder->up();

		// Validate expected state.
		$rows = $this->get_table_fields( $field_schema->table_schema()::table_name( true ) );

		foreach ( $field_schema->fields() as $field ) {
			$this->assertContains( $field, $rows );
		}

		// Keep our table.
		add_filter( 'tec_events_custom_tables_v1_table_schemas', function ( $fields )  {
			return [];
		} ,999);

		// Activate.
		$schema_builder->down();

		// Validate expected state.
		$rows = $this->get_table_fields( $field_schema->table_schema()::table_name( true ) );

		foreach ( $field_schema->fields() as $field ) {
			$this->assertNotContains( $field, $rows );
		}
	}

	/**
	 * @test
	 */
	public function should_field_exists() {
		$schema_builder = tribe( Schema_Builder::class );
		$field_schema   = tribe( Test_Schema_Field::class );
		$this->given_a_field_schema_exists($field_schema);
		// Keep our table - validate the field changes.
		add_filter( 'tec_events_custom_tables_v1_table_schemas', function ( $fields )  {
			return [];
		} ,999);
		$schema_builder->up();

		$this->assertTrue($field_schema->exists());
		$schema_builder->down();

		$this->assertFalse($field_schema->exists());
	}

	public function given_a_field_schema_exists( $field_schema ) {
		add_filter( 'tec_events_custom_tables_v1_field_schemas', function ( $fields ) use ( $field_schema ) {
			return array_merge( $fields, [ $field_schema ] );
		} );
	}

	/**
	 * @param $table
	 *
	 * @return array
	 */
	public function get_table_fields( $table ) {
		global $wpdb;
		$q    = 'select `column_name` from information_schema.columns
					where table_schema = database()
					and `table_name`=%s';
		$rows = $wpdb->get_results( $wpdb->prepare( $q, $table ) );

		return array_map( function ( $row ) {
			return $row->column_name;
		}, $rows );
	}

	public function get_tables() {
		global $wpdb;
		$q      = 'show tables';

		return $wpdb->get_col( $q );
	}

}
