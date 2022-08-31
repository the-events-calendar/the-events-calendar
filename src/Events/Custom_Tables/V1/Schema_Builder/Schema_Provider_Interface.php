<?php
/**
 * Interface for registering the Table and Field handlers.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
namespace TEC\Events\Custom_Tables\V1\Schema_Builder;
/**
 * Interface Schema_Provider_Interface
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Schema_Builder
 */
interface Schema_Provider_Interface {
	/**
	 * Handles registering this providers table and field Schema_Builder hooks.
	 *
	 * @since 6.0.0
	 */
	public function register();

	/**
	 * Return our set of table schema definitions.
	 *
	 * @since 6.0.0
	 *
	 * @return array<Field_Schema_Interface> A list of table schemas.
	 */
	public static function get_table_schemas();


	/**
	 * Return our set of field schema definitions.
	 *
	 * @since 6.0.0
	 *
	 * @return array<Field_Schema_Interface> A list of field schemas.
	 */
	public static function get_field_schemas();

	/**
	 * @since 6.0.0
	 *
	 * @param array<Table_Schema_Interface> $schemas
	 *
	 * @return array<Field_Schema_Interface> A list of table schemas.
	 */
	public function filter_table_schemas( $schemas );

	/**
	 * @since 6.0.0
	 *
	 * @param array<Field_Schema_Interface> $schemas
	 *
	 * @return array<Field_Schema_Interface> A list of field schemas.
	 */
	public function filter_field_schemas( $schemas );
}