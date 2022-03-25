<?php

namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

use TEC\Events\Custom_Tables\V1\Tables\Custom_Field_Interface;
use TEC\Events\Custom_Tables\V1\Tables\Custom_Table_Interface;
use WP_CLI;

class Schema_Builder {

	public function get_table_schemas_that_need_updates() {
		$handlers = $this->get_registered_table_schemas();
// @todo
		return $handlers;
	}

	public function get_field_schemas_that_need_updates() {
		$handlers = $this->get_registered_field_schemas();
// @todo
		return $handlers;
	}

	public function is_schema_current() {
		// Grab versions from all handlers, and check against our stored version.
		$handlers = array_merge($this->get_registered_table_schemas(), $this->get_registered_field_schemas());
		$versions = get_option( self::SCHEMA_VERSIONS_KEY );
		// No versions registered yet.
		if(empty($versions) || !is_array($versions)) {

			return false;
		}
		foreach ($handlers as $table) {
			// Not even stored yet?
			if(!isset($versions[$table])) {

				return false;
			}
			// Compare our stored versions.
			if(!version_compare( $table::VERSION, $versions[$table], '==' )) {

				return false;
			}
		}

		return true;
	}


	public static function get_table_schemas() {}

	public static function get_fields_schemas() {}

	/**
	 * @return array<Table_Schema_Interface>
	 */
	public function get_registered_table_schemas() {
		return apply_filters('tec_events_custom_tables_v1_table_schemas', []);
	}

	/**
	 * @return array<Field_Schema_Interface>
	 */
	public function get_registered_field_schemas() {
		return apply_filters('tec_events_custom_tables_v1_field_schemas',[]);
	}
////// @todo

	/**
	 * Trigger actions to drop the custom tables.
	 *
	 * @since TBD
	 */
	public function down() {
		/**
		 * Runs before the custom tables are dropped by The Events Calendar.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_custom_tables_v1_pre_drop_tables' );

		$table_classes = $this->get_registered_table_schemas( );

		/**
		 * Filters the tables to be dropped.
		 *
		 * @since TBD
		 *
		 * @param array<Custom_Table_Interface> $table_classes A list of Custom_Table_Interface objects that will have their tables dropped.
		 */
		$table_classes = apply_filters( 'tec_events_custom_tables_v1_tables_to_drop', $table_classes );

		foreach ( $table_classes as $table_class ) {
			$table_class ->drop_table();
		}

		/**
		 * Runs after the custom tables have been dropped by The Events Calendar.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_custom_tables_v1_post_drop_tables' );

		/**
		 * Runs before the custom fields are dropped by The Events Calendar.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_custom_tables_v1_pre_drop_fields' );

		$field_classes = $this->get_registered_field_schemas( );

		/**
		 * Filters the fields to be dropped.
		 *
		 * @since TBD
		 *
		 * @param array<Custom_Field_Interface> $field_classes A list of Custom_Field_Interface objects that will have their fields dropped.
		 */
		$field_classes = apply_filters( 'tec_events_custom_tables_v1_fields_to_drop', $field_classes );

		foreach ( $field_classes as $field_class ) {
			$field_class ->drop_field();
		}

		/**
		 * Runs after the custom tables have been dropped by The Events Calendar.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_custom_tables_v1_post_drop_fields' );
	}

	/**
	 * Removes the table option from the database on deactivation.
	 *
	 * @since TBD
	 *
	 * @todo ? Name mismatch?
	 */
	public function clean() {
		delete_option( self::VERSION_OPTION );
	}

	/**
	 * Filters the list of tables for a blog adding the ones created by the plugin.
	 *
	 * @since TBD
	 *
	 * @param array $tables An array of table names for the blog.
	 *
	 * @return array<string> A filtered array of table names, including prefix.
	 */
	public function filter_tables_list( $tables ) {
		$schemas = $this->get_registered_table_schemas();
		foreach ( $schemas as $class ) {
			$table_name            = $class::table_name(true);
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
	 * @since TBD
	 *
	 * @return array<mixed> A list of each creation or update result.
	 */
	public function update_blog_tables() {
		return $this->up( false );
	}

	/**
	 * Creates or updates the custom tables the plugin will use.
	 *
	 * @since TBD
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

		$results = [];
		$table_schemas =  $force ? $this->get_registered_table_schemas() : $this->get_table_schemas_that_need_updates();

		// Get all registered table classes.
		foreach ( $table_schemas as $table_schema ) {
			/** @var Table_Schema_Interface $table_schema */
			$results[ $table_schema::table_name() ] = $table_schema->update();
		}

		$field_schemas =  $force ? $this->get_registered_field_schemas() : $this->get_field_schemas_that_need_updates();
		// Get all registered table classes.
		foreach ( $field_schemas as $field_schema ) {
			/** @var Field_Schema_Interface $field_schema */
			$results[ $field_schema::table_name() ] = $field_schema->update();
		}

// @todo Results...? What do we use this for?
		return array_merge( ...array_values( $results ) );
	}

	/**
	 * Registers the custom table names as properties on the `wpdb` global.
	 *
	 * @since TBD
	 */
	public function register_custom_tables_names() {
		global $wpdb;
		$schemas = $this->get_registered_table_schemas();

		foreach ( $schemas as $class ) {
			$no_prefix_table_name = $class::table_name(false);
			$prefixed_tale_name   = $class::table_name(true);
			$wpdb->{$no_prefix_table_name} = $prefixed_tale_name;
			if ( ! in_array( $wpdb->{$no_prefix_table_name}, $wpdb->tables, true ) ) {
				$wpdb->tables[] = $no_prefix_table_name;
			}
		}
	}

	/**
	 * Empties the plugin custom tables.
	 *
	 * @since TBD
	 */
	public function empty_custom_tables() {
		$schemas = $this->get_registered_table_schemas();
		foreach ( $schemas as $custom_table ) {
			/** @var Table_Schema_Interface $custom_table */
			WP_CLI::debug( 'Emptying table ' . $custom_table::table_name(), 'TEC' );
			$custom_table->empty_table();
		}
	}

	/**
	 * Registers the service provider functions.
	 *
	 * @since TBD
	 */
	public function register() {
// @todo Move this to the Provider?
		$this->register_custom_tables_names();
		$this->register_wpcli_support();

		if ( is_multisite() ) {
			$this->register_multisite_actions();
		}
	}

	/**
	 * Ensures the tables exist for a blog on activation or switch.
	 *
	 * @since TBD
	 */
	private function register_multisite_actions() {
		add_action( 'activate_blog', [ $this, 'update_blog_tables' ] );
		add_action( 'activate_blog', [ $this, 'register_custom_tables_names' ] );
		add_action( 'switch_blog', [ $this, 'update_blog_tables' ] );
		add_action( 'switch_blog', [ $this, 'register_custom_tables_names' ] );
		add_filter( 'wpmu_drop_tables', [ $this, 'filter_tables_list' ] );
	}

	/**
	 * Hooks into wp-cli actions to perform operations on custom tables.
	 *
	 * @since TBD
	 */
	private function register_wpcli_support() {
		if ( defined( 'WP_CLI' ) && method_exists( '\\WP_CLI', 'add_hook' ) ) {
			WP_CLI::add_hook( 'after_invoke:site empty', [ $this, 'empty_custom_tables' ] );
		}
	}

	/**
	 * Whether all the custom tables exist or not.
	 *
	 * Note: the method will return `false` if even one table is missing.
	 *
	 * @since TBD
	 *
	 * @return bool Whether all custom tables exist or not.
	 */
	public function exist() {
		global $wpdb;
		$table_classes = $this->get_registered_table_schemas();
		$result        = $wpdb->get_col( 'SHOW TABLES' );
		foreach ( $table_classes as $class ) {
			if ( ! in_array( $class::table_name(), $result, true ) ) {

				return false;
			}
		}

		return true;
	}
}
