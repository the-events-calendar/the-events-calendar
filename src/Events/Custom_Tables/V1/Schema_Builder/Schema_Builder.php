<?php

namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

use WP_CLI;

class Schema_Builder {

	/**
	 * Helper to filter out the schema updates that are already current.
	 *
	 * @since 6.0.0
	 *
	 * @param array<Field_Schema_Interface|Table_Schema_Interface> $handlers
	 *
	 * @return array<Field_Schema_Interface|Table_Schema_Interface>
	 */
	protected function filter_for_version( $handlers ) {
		return array_filter( $handlers, function ( $handler ) {
			// Checks this handler version.
			return ! $handler->is_schema_current();
		} );
	}

	/**
	 * Get the registered table handlers that need updates.
	 *
	 * @since 6.0.0
	 *
	 * @return array<Table_Schema_Interface>
	 */
	public function get_table_schemas_that_need_updates() {

		return $this->filter_for_version( $this->get_registered_table_schemas() );
	}

	/**
	 * Get the registered field handlers that need updates.
	 *
	 * @since 6.0.0
	 *
	 * @return array<Field_Schema_Interface>
	 */
	public function get_field_schemas_that_need_updates() {

		return $this->filter_for_version( $this->get_registered_field_schemas() );
	}

	/**
	 * Get the md5 hash of all the registered schemas classes with their versions.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	public function get_registered_schemas_version_hash(): string {
		$schemas = array_merge( $this->get_registered_table_schemas(), $this->get_registered_field_schemas() );

		$versions = [];
		foreach( $schemas as $schema ) {
			// Skip if not an Interface of Table or Field.
			if ( ! $schema instanceof Table_Schema_Interface && ! $schema instanceof Field_Schema_Interface ) {
				continue;
			}

			$class_name = get_class( $schema );
			$constant_name = $class_name . '::SCHEMA_VERSION';

			// Skip if the version constant is not defined.
			if ( ! defined( $constant_name ) ) {
				continue;
			}

			$versions[ $class_name ] = constant( $constant_name );
		}

		// Sort to avoid hash changing due to order changes.
		ksort( $versions );

		return md5( json_encode( $versions ) );
	}

	/**
	 * Get the registered table handlers.
	 *
	 * @since 6.0.0
	 *
	 * @return array<Table_Schema_Interface>
	 */
	public function get_registered_table_schemas() {
		/**
		 * Filters the list of table schemas that will be used to build the database tables.
		 *
		 * @since 6.0.0
		 *
		 * @param array<Table_Schema_Interface> $table_schemas An array of table schema objects;
		 *                                                     empty by default.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_table_schemas', [] );
	}

	/**
	 * Get the registered field handlers.
	 *
	 * @since 6.0.0
	 *
	 * @return array<Field_Schema_Interface>
	 */
	public function get_registered_field_schemas() {
		/**
		 * Filters the list of field schemas that will be used to build the database tables.
		 *
		 * @since 6.0.0
		 *
		 * @param array<Field_Schema_Interface> $field_schemas An array of field schema objects;
		 *                                                     empty by default.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_field_schemas', [] );
	}


	/**
	 * Trigger actions to drop the custom tables.
	 *
	 * @since 6.0.0
	 */
	public function down() {
		/**
		 * Runs before the custom tables are dropped by The Events Calendar.
		 *
		 * @since 6.0.0
		 */
		do_action( 'tec_events_custom_tables_v1_pre_drop_tables' );

		$table_classes = $this->get_registered_table_schemas();

		/**
		 * Filters the tables to be dropped.
		 *
		 * @since 6.0.0
		 *
		 * @param array<Custom_Table_Interface> $table_classes A list of Custom_Table_Interface objects that will have their tables dropped.
		 */
		$table_classes = apply_filters( 'tec_events_custom_tables_v1_tables_to_drop', $table_classes );

		foreach ( $table_classes as $table_class ) {
			$table_class->drop();
		}

		/**
		 * Runs after the custom tables have been dropped by The Events Calendar.
		 *
		 * @since 6.0.0
		 */
		do_action( 'tec_events_custom_tables_v1_post_drop_tables' );

		/**
		 * Runs before the custom fields are dropped by The Events Calendar.
		 *
		 * @since 6.0.0
		 */
		do_action( 'tec_events_custom_tables_v1_pre_drop_fields' );

		$field_classes = $this->get_registered_field_schemas();

		/**
		 * Filters the fields to be dropped.
		 *
		 * @since 6.0.0
		 *
		 * @param array<Custom_Field_Interface> $field_classes A list of Custom_Field_Interface objects that will have their fields dropped.
		 */
		$field_classes = apply_filters( 'tec_events_custom_tables_v1_fields_to_drop', $field_classes );

		foreach ( $field_classes as $field_class ) {
			$field_class->drop();
		}

		/**
		 * Runs after the custom tables have been dropped by The Events Calendar.
		 *
		 * @since 6.0.0
		 */
		do_action( 'tec_events_custom_tables_v1_post_drop_fields' );
	}

	/**
	 * Filters the list of tables for a blog adding the ones created by the plugin.
	 *
	 * @since 6.0.0
	 *
	 * @param array $tables An array of table names for the blog.
	 *
	 * @return array<string> A filtered array of table names, including prefix.
	 */
	public function filter_tables_list( $tables ) {
		$schemas = $this->get_registered_table_schemas();
		foreach ( $schemas as $class ) {
			$table_name            = $class::table_name( true );
			$tables[ $table_name ] = $table_name;
		}

		return $tables;
	}

	/**
	 * A proxy method to update the tables without forcing
	 * them.
	 *
	 * If the `update_tables` was directly hooked to the blog
	 * switches, then the blog ID, a positive integer, would be
	 * cast to a truthy value and force the table updates when
	 * not really required to.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string,mixed> A list of each creation or update result; empty if
	 *                      the blog tables have already been updated in this request.
	 */
	public function update_blog_tables( int $blog_id ): array {
		if ( tribe_cache()[ 'ct1_schema_builder_update_blog_tables_' . $blog_id ] ) {
			// Already up for this site in this request.
			return [];
		}

		$result = $this->up( false );

		tribe_cache()[ 'ct1_schema_builder_update_blog_tables_' . $blog_id ] = true;

		return $result;
	}

	/**
	 * Creates or updates the custom tables the plugin will use.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $force Whether to force the creation or update of the tables or not.
	 *
	 * @return array<string,mixed> A list of each creation or update result.
	 */
	public function up( $force = false ) {
		global $wpdb;

		//phpcs:ignore
		$wpdb->get_results( "SELECT 1 FROM {$wpdb->posts} LIMIT 1" );
		$posts_table_exists = '' === $wpdb->last_error;
		// Let's not try to create the tables on a blog that's missing the basic ones.
		if ( ! $posts_table_exists ) {
			return [];
		}

		$results       = [];
		$table_schemas = $force ? $this->get_registered_table_schemas() : $this->get_table_schemas_that_need_updates();

		// Get all registered table classes.
		foreach ( $table_schemas as $table_schema ) {
			/** @var Table_Schema_Interface $table_schema */
			$results[ $table_schema::table_name() ] = $table_schema->update();
		}

		$field_schemas = $force ? $this->get_registered_field_schemas() : $this->get_field_schemas_that_need_updates();

		// Get all registered table classes.
		foreach ( $field_schemas as $field_schema ) {
			/** @var Field_Schema_Interface $field_schema */
			$custom_table                           = $field_schema->table_schema();
			$results[ $custom_table::table_name() ] = $field_schema->update();
		}

		/**
		 * Runs after the custom tables have been created or updated by The Events Calendar.
		 *
		 * @since 6.0.2
		 *
		 * @param array<string,bool> $results A map from each table name to whether it was created or updated correctly.
		 * @param bool               $force   Whether the tables were forced to be created or updated or not.
		 */
		do_action( 'tec_events_custom_tables_v1_schema_builder_after_up', $results, $force );

		return count( $results ) ? array_merge( ...array_values( $results ) ) : [];
	}

	/**
	 * Registers the custom table names as properties on the `wpdb` global.
	 *
	 * @since 6.0.0
	 */
	public function register_custom_tables_names() {
		global $wpdb;
		$schemas = $this->get_registered_table_schemas();

		foreach ( $schemas as $class ) {
			$no_prefix_table_name          = $class::table_name( false );
			$prefixed_tale_name            = $class::table_name( true );
			$wpdb->{$no_prefix_table_name} = $prefixed_tale_name;
			if ( ! in_array( $wpdb->{$no_prefix_table_name}, $wpdb->tables, true ) ) {
				$wpdb->tables[] = $no_prefix_table_name;
			}
		}
	}

	/**
	 * Empties the plugin custom tables.
	 *
	 * @since 6.0.0
	 */
	public function empty_custom_tables() {
		$schemas = $this->get_registered_table_schemas();
		foreach ( $schemas as $custom_table ) {
			/** @var Table_Schema_Interface $custom_table */
			if ( class_exists( 'WP_CLI' ) ) {
				WP_CLI::debug( 'Emptying table ' . $custom_table::table_name(), 'TEC' );
			}
			$custom_table->empty_table();
		}
	}

	/**
	 * Whether all the custom tables exist or not. Does not check custom fields.
	 *
	 * Note: the method will return `false` if even one table is missing.
	 *
	 * @since 6.0.0
	 *
	 * @param string|null $group An optional group name to restrict the check to.
	 *
	 * @return bool Whether all custom tables exist or not. Does not check custom fields.
	 */
	public function all_tables_exist( $group = null ) {
		global $wpdb;
		$table_classes = $this->get_registered_table_schemas();

		if ( null !== $group ) {
			$table_classes = array_filter( $table_classes, static function ( $class ) use ( $group ) {
				return $class::group_name() === $group;
			} );
		}

		if ( empty( $table_classes ) ) {
			// No table class was even found.
			return false;
		}

		$sql_in_statement = array_map( static function( $table_class ) {
			return $table_class::table_name();
		}, $table_classes );

		$sql_in_statement = '"' . implode( '", "', $sql_in_statement ) . '"';

		$result        = $wpdb->get_col( "SELECT DISTINCT table_name FROM information_schema.tables
                           WHERE table_schema = database() AND table_name IN ( {$sql_in_statement} )" );
		foreach ( $table_classes as $class ) {
			if ( ! in_array( $class::table_name(), $result, true ) ) {

				return false;
			}
		}

		return true;
	}
}
