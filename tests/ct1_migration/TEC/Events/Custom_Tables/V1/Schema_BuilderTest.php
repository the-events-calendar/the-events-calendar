<?php

namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Field;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Table;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Field_Schema_Interface;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Table_Schema_Interface;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use TEC\Events\Custom_Tables\V1\Provider as TableProvider;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;


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
	 * @before each test to make sure our tables are registered.
	 */
	public function setup_table_provider() {
		tribe()->register( TableProvider::class );
	}

	/**
	 * Should tables create/destroy properly.
	 *
	 * @test
	 */
	public function should_up_down_table_schema() {
		$schema_builder = tribe( Schema_Builder::class );

		// Activate.
		$schema_builder->up( true );

		// Validate expected state.
		$tables = $this->get_tables();
		$this->assertContains( EventsSchema::table_name( true ), $tables );
		$this->assertContains( OccurrencesSchema::table_name( true ), $tables );
		$this->assertTrue( $schema_builder->all_tables_exist() );

		$schema_builder->down();

		// Validate expected state.
		$tables = $this->get_tables();
		$this->assertNotContains( EventsSchema::table_name( true ), $tables );
		$this->assertNotContains( OccurrencesSchema::table_name( true ), $tables );
		$this->assertFalse( $schema_builder->all_tables_exist() );
	}


	/**
	 * Should fields create/destroy properly.
	 *
	 * @test
	 */
	public function should_up_down_field_schema() {
		$schema_builder = tribe( Schema_Builder::class );
		$field_schema   = $this->custom_field_schema();
		$this->given_a_field_schema_exists( $field_schema );
		// Activate.
		$schema_builder->up( true );

		// Validate expected state.
		$rows = $this->get_table_fields( $field_schema->table_schema()::table_name( true ) );

		foreach ( $field_schema->fields() as $field ) {
			$this->assertContains( $field, $rows );
		}

		// Keep our table.
		add_filter( 'tec_events_custom_tables_v1_table_schemas', function ( $fields ) {
			return [];
		}, 999 );

		// Activate.
		$schema_builder->down();

		// Validate expected state.
		$rows = $this->get_table_fields( $field_schema->table_schema()::table_name( true ) );

		foreach ( $field_schema->fields() as $field ) {
			$this->assertNotContains( $field, $rows );
		}
	}

	/**
	 * Tests the `exists` function finds the fields properly.
	 *
	 * @test
	 */
	public function should_field_exists() {
		$schema_builder = tribe( Schema_Builder::class );
		$field_schema   = $this->custom_field_schema();
		$this->given_a_field_schema_exists( $field_schema );
		// Keep our table - validate the field changes.
		add_filter( 'tec_events_custom_tables_v1_table_schemas', function ( $fields ) {
			return [];
		}, 999 );
		$schema_builder->up( true );

		$this->assertTrue( $field_schema->exists() );
		$schema_builder->down();

		$this->assertFalse( $field_schema->exists() );
	}

	/**
	 * The state of the stored version should be stored and removed when we up/down the schema.
	 *
	 * @test
	 */
	public function should_sync_version() {
		$field_schema = $this->custom_field_schema();
		$this->given_a_field_schema_exists( $field_schema );
		$schema_builder = tribe( Schema_Builder::class );
		$schema_builder->up();

		// Is version there?
		$occurrence_version = get_option( OccurrencesSchema::SCHEMA_VERSION_OPTION );
		$field_version      = get_option( $field_schema::SCHEMA_VERSION_OPTION );
		$this->assertEquals( OccurrencesSchema::SCHEMA_VERSION, $occurrence_version );
		$this->assertEquals( $field_schema::SCHEMA_VERSION, $field_version );

		// Is version gone?
		$schema_builder->down();
		$occurrence_version = get_option( OccurrencesSchema::SCHEMA_VERSION_OPTION );
		$field_version      = get_option( $field_schema::SCHEMA_VERSION_OPTION );
		$this->assertNotEquals( OccurrencesSchema::SCHEMA_VERSION, $occurrence_version );
		$this->assertNotEquals( $field_schema::SCHEMA_VERSION, $field_version );
	}

	/**
	 * Tests tables with constraints can be created, destroyed and detected.
	 *
	 * @test
	 */
	public function should_detect_constraints() {
		$schema_builder = tribe( Schema_Builder::class );
		$schemas        = $this->get_custom_table_schemas_with_constraint();
		$this->given_a_table_schema_exists( $schemas );
		// The order matters. "Child" has a FK that targets the "parent".
		[ $parent_schema, $child_schema ] = $schemas;

		$schema_builder->up( true );

		// Confirm they were built with constraints in place
		$this->assertTrue( $parent_schema->exists() );
		$this->assertTrue( $child_schema->exists() );

		// Test constraints
		$this->assertTrue( $child_schema->has_constraint( $parent_schema::uid_column(), $child_schema::table_name( true ) ) );
		$this->assertIsObject( $child_schema->get_schema_constraint( $parent_schema::uid_column(), $child_schema::table_name( true ) ) );
		$this->assertFalse( $child_schema->has_constraint( 'notarealfield', $child_schema::table_name( true ) ) );
		$this->assertNull( $child_schema->get_schema_constraint( 'notarealfield', $child_schema::table_name( true ) ) );

		$schema_builder->down();

		$this->assertFalse( $parent_schema->exists() );
		$this->assertFalse( $child_schema->exists() );
		$this->assertFalse( $child_schema->has_constraint( $parent_schema::uid_column(), $child_schema::table_name( true ) ) );
		$this->assertNull( $child_schema->get_schema_constraint( $parent_schema::uid_column(), $child_schema::table_name( true ) ) );
	}

	/**
	 * Add this schema to the registered list.
	 *
	 * @param array<Table_Schema_Interface> $table_schemas
	 */
	public function given_a_table_schema_exists( $table_schemas = [] ) {
		add_filter( 'tec_events_custom_tables_v1_table_schemas', function ( $fields ) use ( $table_schemas ) {
			return $table_schemas;
		} );
	}

	/**
	 * Add this schema to the registered list.
	 *
	 * @param Field_Schema_Interface $field_schema
	 */
	public function given_a_field_schema_exists( $field_schema ) {
		add_filter( 'tec_events_custom_tables_v1_field_schemas', function ( $fields ) use ( $field_schema ) {
			return array_merge( $fields, [ $field_schema ] );
		} );
	}

	/**
	 * @param string $table Table name.
	 *
	 * @return array<string> List of fields for this table.
	 */
	public function get_table_fields( $table ) {
		global $wpdb;
		$q    = 'select `column_name` from information_schema.columns
					where table_schema = database()
					and `table_name`= %s';
		$rows = $wpdb->get_results( $wpdb->prepare( $q, $table ) );

		return array_map( function ( $row ) {
			return $row->column_name;
		}, $rows );
	}

	/**
	 * @return array List of tables in this database.
	 */
	public function get_tables() {
		global $wpdb;
		$q = 'show tables';

		return $wpdb->get_col( $q );
	}

	/**
	 * @return array<Abstract_Custom_Table>
	 */
	public function get_custom_table_schemas_with_constraint() {
		return [
			new class extends Abstract_Custom_Table {
				const SCHEMA_VERSION = '1.0.0';
				const SCHEMA_VERSION_OPTION = 'tec_ct1_custom_tbl_parent_version_key';

				public static function uid_column() {
					return 'parent_id';
				}

				public static function base_table_name() {
					return 'faux_parent';
				}

				public function get_update_sql() {
					$table_name = self::table_name( true );
					$uid_field  = self::uid_column();

					return "CREATE TABLE `{$table_name}` (
				`{$uid_field}` INT NOT NULL,
				PRIMARY KEY ($uid_field)
				) ENGINE=INNODB;";
				}
			},
			new class extends Abstract_Custom_Table {
				const SCHEMA_VERSION = '1.0.0';
				const SCHEMA_VERSION_OPTION = 'tec_ct1_custom_tbl_child_version_key';

				public static function uid_column() {
					return 'child_id';
				}

				public static function base_table_name() {
					return 'faux_child';
				}

				public function get_update_sql() {
					global $wpdb;
					$table_name       = self::table_name( true );
					$uid_field        = self::uid_column();
					$parent_uid_field = 'parent_id';;
					$parent_table_name = $wpdb->prefix . 'faux_parent';

					return "CREATE TABLE `{$table_name}` (
				`{$uid_field}` INT,
				`{$parent_uid_field}` INT,
				INDEX par_ind (`{$parent_uid_field}`),
				FOREIGN KEY (`{$parent_uid_field}`)
				REFERENCES {$parent_table_name}(`{$parent_uid_field}`)
				ON DELETE CASCADE
				) ENGINE=INNODB;";
				}
			}
		];
	}

	/**
	 * @return Abstract_Custom_Field
	 */
	public function custom_field_schema() {
		return new class extends Abstract_Custom_Field {
			const SCHEMA_VERSION = '1.0.0';
			const SCHEMA_VERSION_OPTION = 'tec_ct1_custom_field_version_key';

			public function fields() {
				return [ 'bob', 'frank' ];
			}

			public function table_schema() {
				return tribe( EventsSchema::class );
			}

			public function get_update_sql() {
				global $wpdb;
				$table_name      = $this->table_schema()::table_name( true );
				$charset_collate = $wpdb->get_charset_collate();

				return "CREATE TABLE `{$table_name}` (
			`bob` LONGTEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			`frank` TINYINT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			) {$charset_collate};";
			}
		};
	}

	/**
	 * It should support group when checking for all tables existence
	 *
	 * @test
	 */
	public function should_support_group_when_checking_for_all_tables_existence() {
		add_filter( 'query', static function ( $query ) {
			if ( $query !== 'SHOW TABLES' ) {
				return $query;
			}

			return 'SELECT "fodz" UNION ALL SELECT "klutz" UNION ALL SELECT "zorps"';
		} );
		$fodz_table  = new class implements Table_Schema_Interface {
			public static function uid_column() {
			}

			public function empty_table() {
			}

			public function drop() {
			}

			public function update() {
			}

			public static function table_name() {
				return 'fodz';
			}

			public static function base_table_name() {
			}

			public function is_schema_current() {
			}

			public static function group_name() {
				return 'one';
			}

			public function exists() {
				return false;
			}
		};
		$klutz_table = new class implements Table_Schema_Interface {
			public static function uid_column() {
			}

			public function empty_table() {
			}

			public function drop() {
			}

			public function update() {
			}

			public static function table_name() {
				return 'klutz';
			}

			public static function base_table_name() {
			}

			public function is_schema_current() {
			}

			public static function group_name() {
				return 'one';
			}

			public function exists() {
				return true;
			}
		};
		$zorps_table = new class implements Table_Schema_Interface {
			public static function uid_column() {
			}

			public function empty_table() {
			}

			public function drop() {
			}

			public function update() {
			}

			public static function table_name() {
				return 'zorps';
			}

			public static function base_table_name() {
			}

			public function is_schema_current() {
			}

			public static function group_name() {
				return 'two';
			}

			public function exists() {
				return true;
			}
		};
		$tables      = [ $fodz_table, $klutz_table, $zorps_table ];

		add_filter( 'tec_events_custom_tables_v1_table_schemas', static function () use ( $tables ) {
			return $tables;
		} );
		// Mock the values returned by the query to fetch the tables.
		// @see TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder::all_tables_exist()
		add_filter( 'query', static function ( $query ) {
			if ( stripos( $query, 'table_name IN ( "fodz", "klutz", "zorps" )' ) !== false ) {
				return 'SELECT "fodz" UNION ALL SELECT "klutz" UNION ALL SELECT "zorps"';
			}

			// Same for fodz and klutz, group one.
			if ( stripos( $query, 'table_name IN ( "fodz", "klutz" )' ) !== false ) {
				return 'SELECT "fodz" UNION ALL SELECT "klutz"';
			}

			// Same for zorps, group two.
			if ( stripos( $query, 'table_name IN ( "zorps" )' ) !== false ) {
				return 'SELECT "zorps"';
			}

			// No need to mock anything for group 3.

			return $query;
		}, 100 );

		$schema_builder = new Schema_Builder;

		$this->assertTrue( $schema_builder->all_tables_exist() );
		$this->assertTrue( $schema_builder->all_tables_exist( 'one' ) );
		$this->assertTrue( $schema_builder->all_tables_exist( 'two' ) );
		$this->assertFalse( $schema_builder->all_tables_exist( 'three' ) );
	}
}
