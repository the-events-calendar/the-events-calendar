<?php
/**
 * API to define how the formatters should be defined.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters;
 */
namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

/**
 * Interface Formatter
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
interface Formatter {
	/**
	 * Format a column into a suitable format for processing.
	 *
	 * @since TBD
	 *
	 * @param $value
	 *
	 * @return mixed The result of the formatting.
	 */
	public function format( $value );

	/**
	 * Format used to prepare this value before is saved into the database like `%s` for strings.
	 *
	 * @since TBD
	 *
	 * @return mixed How the data should be prepared (sanitized) before is saved into the DB.
	 */
	public function prepare();
}
