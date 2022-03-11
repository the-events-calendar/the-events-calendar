<?php
/**
 * Groups the base methods and functions used by all custom table implementations.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */

namespace TEC\Events\Custom_Tables\V1\Tables;

/**
 * Class Base_Custom_Table
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */
abstract class Base_Custom_Table implements Custom_Table_Interface {
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
		return $wpdb->query( "DELETE FROM {$this_table} WHERE 1=1" );
	}

	/**
	 * {@inheritdoc}
	 */
	public function update() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$results = (array) dbDelta( $this->get_update_sql() );

		$results = $this->after_update( $results );

		return $results;
	}

	/**
	 * Returns the table creation SQL in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since TBD
	 *
	 * @return string The table creation SQL, in the format supported
	 *                by the `dbDelta` function.
	 */
	abstract protected function get_update_sql();

	/**
	 * Allows extending classes that require it to run some methods
	 * immediately after the table creation or update.
	 *
	 * @since TBD
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
	 * {@inheritdoc}
	 */
	public function get_table_name( $with_prefix = false ) {
		return static::table_name($with_prefix);
	}

	/**
	 * Returns the table name, with prefix if required.
	 *
	 * @since TBD
	 *
	 * @return string The table name.
	 */
	public static function table_name( $with_prefix = true ) {
		$table_name = static::TABLE_NAME;

		if ( $with_prefix ) {
			global $wpdb;
			$table_name = $wpdb->prefix . $table_name;
		}

		return $table_name;
	}

	/**
	 * Checks if an index already exists on the table.
	 *
	 * @since TBD
	 *
	 * @param string $index The name of the index to check for.
	 * @param string|null $table_name The table name to search the index for, or `null`
	 *                                to use this table name.
	 *
	 * @return bool Whether the table already has an index or not.
	 */
	protected function has_index( $index, $table_name = null ) {
		$table_name = $table_name ?: $this->get_table_name(true);
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
	 * Returns whether a table exists in the database or not.
	 *
	 * @since TBD
	 *
	 * @param string|null $table_name The table name to check, or `null` to use this table name.
	 *
	 * @return bool Whether a table exists in the database or not.
	 */
	protected function exists( $table_name = null ) {
		global $wpdb;

		$table_name = $table_name ?: $this->get_table_name( true );

		return count( $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function drop_table() {
		if ( ! $this->exists() ) {

			return false;
		}

		$this_table = static::table_name( true );

		global $wpdb;

		return (bool) $wpdb->query( "DROP TABLE {$this_table}" );
	}
}
