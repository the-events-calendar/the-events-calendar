<?php
/**
 * The API implemented by each custom field. Used in cases where only a portion of an existing table must be modified.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */

namespace TEC\Events\Custom_Tables\V1\Tables;

/**
 * Interface Custom_Field_Interface
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */
interface Custom_Field_Interface {
	/**
	 * Drop the custom fields.
	 *
	 * @since TBD
	 *
	 * @return boolean `true` if successful operation, `false` to indicate a failure.
	 */
	public function drop();

	/**
	 * Creates, or updates, the custom fields.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the creation, or update, was successful or not.
	 */
	public function update();

	/**
	 * @todo ...
	 */
	public function get_table_name( $with_prefix = false );
}
