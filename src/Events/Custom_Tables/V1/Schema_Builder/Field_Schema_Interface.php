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
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
interface Field_Schema_Interface {
	/**
	 * Drop the custom fields.
	 *
	 * @since TBD
	 *
	 * @return bool `true` if successful operation, `false` to indicate a failure.
	 */
	public function drop();

	/**
	 * Creates, or updates, the custom fields.
	 *
	 * @since TBD
	 *
	 * @return bool `true` if successful operation, `false` to indicate a failure.
	 */
	public function update();

	/**
	 * References our stored version versus the version defined in the class.
	 *
	 * @since TBD
	 *
	 * @return bool Whether our latest schema has been applied.
	 */
	public function is_schema_current();

	/**
	 * A reference to the table definition we are modifying with new fields.
	 *
	 * @since TBD
	 *
	 * @return Abstract_Custom_Table
	 */
	public function table_schema();
}
