<?php
/**
 * The API implemented by each custom table.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */

namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

/**
 * Interface Table_Schema_Interface
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
interface Table_Schema_Interface {
	/**
	 * Returns the name of the column that is guaranteed to uniquely identify an
	 * entry across updates.
	 *
	 * @since TBD
	 *
	 * @return string The name of the column that is guaranteed to uniquely identify an
	 *                entry across updates.
	 */
	public static function uid_column();

	/**
	 * Empties the custom table.
	 *
	 * @since TBD
	 *
	 * @return int|false The number of removed rows, or `false` to indicate a failure.
	 */
	public function empty_table();

	/**
	 * Drop the custom table.
	 *
	 * @since TBD
	 *
	 * @return boolean `true` if successful operation, `false` to indicate a failure.
	 */
	public function drop_table();

	/**
	 * Creates, or updates, the custom table.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the creation, or update, was successful or not.
	 */
	public function update();

	/**
	 * Returns the custom table name.
	 *
	 * @since TBD
	 *
	 * @param  bool  $with_prefix  Whether to include the current `wpdb` prefix or not.
	 *
	 * @return string The custom table name, prefixed by the current `wpdb` prefix,
	 *                if required.
	 */
	public static function table_name( $with_prefix = false );


	/**
	 * Returns the custom table name.
	 *
	 * @since TBD
	 *
	 * @return string The base custom table name.
	 */
	public static function base_table_name();
}
