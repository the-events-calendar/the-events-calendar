<?php


namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

/**
 * Class Abstract_Custom_Field
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
abstract class Abstract_Custom_Field implements Field_Schema_Interface {

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
	 * Returns whether a fields' schema definition exists in the table or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether a set of fields exists in the database or not.
	 */
	protected function exists( ) {
		global $wpdb;

		$table_name =  $this->table_schema()::table_name(true);

		return count( $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function drop_fields() {
		if ( ! $this->exists() ) {

			return false;
		}
		global $wpdb;
/*ALTER TABLE table_name
DROP COLUMN column_name_1,
DROP COLUMN column_name_2,		*/

		$this_table = $this->table_schema()::table_name(true);
		// @todo
		$drop_columns = rtrim('DROP COLUMN `', 'DROP COLUMN `'.implode('`, DROP COLUMN `', $this->fields()));
		dd($drop_columns);


		return  $wpdb->query( $wpdb->prepare("ALTER TABLE %s %s" , $this_table, $drop_columns));
	}

	/**
	 * @since TBD
	 *
	 * @return Table_Schema_Interface
	 */
	abstract public function table_schema();

	/**
	 * @since TBD
	 *
	 * @return array<string>
	 */
	abstract public function fields();
}
