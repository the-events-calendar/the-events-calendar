<?php
/**
 * Groups the base methods and functions used by all custom field implementations.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */

namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

/**
 * Class Abstract_Custom_Field
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
abstract class Abstract_Custom_Field implements Field_Schema_Interface {
	const SCHEMA_VERSION_OPTION = null;
	const SCHEMA_VERSION = null;

	/**
	 * {@inheritdoc}
	 */
	public function update() {
		$this->before_update();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$query =  $this->get_update_sql();
		$this->validate_for_db_delta($query);
		$results = (array) dbDelta($query );
		$this->sync_stored_version();
		$results = $this->after_update( $results );

		return $results;
	}

	/**
	 * Inspects query strings being passed to dbDelta, and logs an error if not ideal.
	 *
	 * @since 6.0.14
	 *
	 * @see https://developer.wordpress.org/reference/functions/dbdelta/
	 *
	 * @param string $query Query string to inspect for case sensitivity before using in dbDelta
	 */
	public function validate_for_db_delta( string $query ) {
		if ( preg_match( '/`.*?` [A-Z]/', $query ) ) {
			do_action( 'tribe_log', 'error', __METHOD__, [ 'schema_builder_error' => "Failed dbDelta field validation: $query" ] );
		}
	}

	/**
	 * Returns the table creation SQL for the fields being created in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since 6.0.0
	 *
	 * @return string The table creation SQL for the fields being created, in the format supported
	 *                by the `dbDelta` function.
	 */
	abstract protected function get_update_sql();

	/**
	 * Allows extending classes that require it to run some methods
	 * immediately before the field creation or update.
	 *
	 * @since 6.0.6
	 */
	protected function before_update() :void {
	}

	/**
	 * Allows extending classes that require it to run some methods
	 * immediately after the table creation or update.
	 *
	 * @since 6.0.0
	 *
	 * @param array<string,string> $results A map of results in the format
	 *                                      returned by the `dbDelta` function.
	 *
	 * @return array<string,string> A map of results in the format returned by
	 *                              the `dbDelta` function.
	 */
	protected function after_update( array $results ) {
		// No-op by default.
		return $results;
	}

	/**
	 * Returns whether a fields' schema definition exists in the table or not.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether a set of fields exists in the database or not.
	 */
	public function exists() {
		global $wpdb;
		$table_name = $this->table_schema()::table_name( true );
		$q          = 'select `column_name` from information_schema.columns
					where table_schema = database()
					and `table_name` = %s';
		$rows       = $wpdb->get_results( $wpdb->prepare( $q, $table_name ) );
		$fields     = $this->fields();
		$rows       = array_map( function ( $row ) {
			return $row->column_name;
		}, $rows );

		foreach ( $fields as $field ) {
			if ( ! in_array( $field, $rows, true ) ) {

				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function drop() {
		$this->clear_stored_version();
		if ( ! $this->exists() ) {

			return false;
		}

		global $wpdb;
		$this_table   = $this->table_schema()::table_name( true );
		$drop_columns = 'DROP COLUMN `' . implode( '`, DROP COLUMN `', $this->fields() ) . '`';

		return $wpdb->query( sprintf( "ALTER TABLE %s %s", $this_table, $drop_columns ) );
	}

	/**
	 * @since 6.0.0
	 *
	 * @return array<string>
	 */
	abstract public function fields();


	/**
	 * Update our stored version with what we have defined.
	 */
	protected function sync_stored_version() {
		if ( ! add_option( static::SCHEMA_VERSION_OPTION, static::SCHEMA_VERSION ) ) {
			update_option( static::SCHEMA_VERSION_OPTION, static::SCHEMA_VERSION );
		}
	}

	/**
	 * Clear our stored version.
	 */
	protected function clear_stored_version() {
		delete_option( static::SCHEMA_VERSION_OPTION );
	}

	/**
	 * @inheritDoc
	 */
	public function is_schema_current() {
		if ( ! static::SCHEMA_VERSION || ! static::SCHEMA_VERSION_OPTION ) {
			// @todo Error?
		}
		$version_applied = get_option( static::SCHEMA_VERSION_OPTION );
		$current_version = static::SCHEMA_VERSION;

		return version_compare( $version_applied, $current_version, '==' );
	}
}
