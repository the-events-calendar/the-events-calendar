<?php
/**
 * Groups the base methods and functions used by all custom table implementations.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */

namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

use stdClass;

/**
 * Class Base_Custom_Table
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
abstract class Abstract_Custom_Table implements Table_Schema_Interface {
	/**
	 * @var string The option key used to store the SCHEMA_VERSION.
	 */
	const SCHEMA_VERSION_OPTION = null;

	/**
	 * @var string The version number for this schema definition.
	 */
	const SCHEMA_VERSION = null;

	/**
	 * {@inheritdoc}
	 */
	public function empty_table() {
		if ( ! $this->exists() ) {
			// There is really nothing to empty here.
			return 0;
		}

		$this_table = static::table_name( true );

		global $wpdb;

		$wpdb->query( "SET foreign_key_checks = 0" );
		$result = $wpdb->query( "TRUNCATE {$this_table}" );
		$wpdb->query( "SET foreign_key_checks = 1" );

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function update() {
		$this->before_update();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$results = (array) dbDelta( $this->get_update_sql() );
		$this->sync_stored_version();
		$results = $this->after_update( $results );

		return $results;
	}

	/**
	 * Returns the table creation SQL in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since 6.0.0
	 *
	 * @return string The table creation SQL, in the format supported
	 *                by the `dbDelta` function.
	 */
	abstract protected function get_update_sql();

	/**
	 * Allows extending classes that require it to run some methods
	 * immediately before the table creation or update.
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
	 * Returns the table name, with prefix if required.
	 *
	 * @since 6.0.0
	 *
	 * @return string The table name.
	 */
	public static function table_name( $with_prefix = true ) {
		$table_name = static::base_table_name();

		if ( $with_prefix ) {
			global $wpdb;
			$table_name = $wpdb->prefix . $table_name;
		}

		return $table_name;
	}

	/**
	 * Checks if an index already exists on the table.
	 *
	 * @since 6.0.0
	 *
	 * @param string      $index      The name of the index to check for.
	 * @param string|null $table_name The table name to search the index for, or `null`
	 *                                to use this table name.
	 *
	 * @return bool Whether the table already has an index or not.
	 */
	protected function has_index( $index, $table_name = null ) {
		$table_name = $table_name ?: static::table_name( true );
		global $wpdb;

		return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM information_schema.statistics WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = %s",
					$table_name,
					$index
				)
			) >= 1;
	}

	/**
	 * Checks if a constraint exists for a particular field.
	 *
	 * @since 6.0.6
	 *
	 * @param string $this_field The field of the table that has the foreign key (not the target of the constraint).
	 * @param string $this_table The table that has the foreign key (not the target of the constraint).
	 *
	 * @return bool Whether this constraint exists.
	 */
	public function has_constraint( $this_field, $this_table ): bool {
		return ! empty( $this->get_schema_constraint( $this_field, $this_table ) );
	}

	/**
	 * Fetches the constraint for a particular field.
	 *
	 * @since 6.0.6
	 *
	 * @param string $this_field The field of the table that has the foreign key (not the target of the constraint).
	 * @param string $this_table The table that has the foreign key (not the target of the constraint).
	 *
	 * @return stdClass|null A stdClass with the INFORMATION_SCHEMA.key_column_usage or null if none found.
	 */
	public function get_schema_constraint( $this_field, $this_table ): ?stdClass {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT *
		FROM INFORMATION_SCHEMA.key_column_usage
		WHERE referenced_table_schema = DATABASE()
			AND referenced_table_name IS NOT NULL
			AND COLUMN_NAME= %s
			AND TABLE_NAME = %s",
			$this_field,
			$this_table
		);

		$results = $wpdb->get_results( $query );

		return ! empty( $results ) ? array_pop( $results ) : null;
	}

	/**
	 * Returns whether a table exists in the database or not.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether a table exists in the database or not.
	 */
	public function exists() {
		global $wpdb;

		$table_name = static::table_name( true );

		return count( $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function drop() {
		$this->clear_stored_version();
		if ( ! $this->exists() ) {

			return false;
		}

		$this_table = static::table_name( true );

		global $wpdb;
		// Disable foreign key checks so we can drop without issues.
		$key_check = $wpdb->get_row( "SHOW VARIABLES LIKE 'foreign_key_checks'" );
		if ( strtolower( $key_check->Value ) === 'on' ) {
			$wpdb->query( "SET foreign_key_checks = 'OFF'" );
		}
		$result = $wpdb->query( "DROP TABLE `{$this_table}`" );
		// Put setting back to original value.
		$wpdb->query( $wpdb->prepare( "SET foreign_key_checks = %s", $key_check->Value ) );

		return $result;
	}

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

	/**
	 * Returns the name of the group the table belongs to.
	 *
	 * @since 6.0.0
	 *
	 * @return string The name of the group the table belongs to.
	 */
	public static function group_name() {
		return '';
	}
}
