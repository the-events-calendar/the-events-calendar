<?php
/**
 * The API implemented by each custom field. Used in cases where only a portion of an existing table must be modified.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */

namespace TEC\Events\Custom_Tables\V1\Schema_Builder;

/**
 * Interface Custom_Field_Interface
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */
interface Field_Schema_Interface {
	/**
	 * Drop the custom fields.
	 *
	 * @since TBD
	 *
	 * @return boolean `true` if successful operation, `false` to indicate a failure.
	 */
	public function drop_fields();

	/**
	 * Creates, or updates, the custom fields.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the creation, or update, was successful or not.
	 */
	public function update();

}
