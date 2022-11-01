<?php
/**
 * API to define how the formatters should be defined.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters;
 */
namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

/**
 * Interface Formatter
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
interface Formatter {
	/**
	 * Format a column into a suitable format for processing.
	 *
	 * @since 6.0.0
	 *
	 * @param $value
	 *
	 * @return mixed The result of the formatting.
	 */
	public function format( $value );

	/**
	 * Format used to prepare this value before is saved into the database like `%s` for strings.
	 *
	 * @since 6.0.0
	 *
	 * @return mixed How the data should be prepared (sanitized) before is saved into the DB.
	 */
	public function prepare();
}
