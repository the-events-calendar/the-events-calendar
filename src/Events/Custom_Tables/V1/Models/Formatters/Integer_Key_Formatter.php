<?php
namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

/**
 * Class Integer_Key_Formatter
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
class Integer_Key_Formatter implements Formatter {
	/**
	 * Format a key if it was provided
	 *
	 * @since TBD
	 *
	 * @param $value
	 *
	 * @return int|null
	 */
	public function format( $value ) {
		if ( $value === null ) {
			return null;
		}

		$key = (int) $value;

		return $key > 0 ? $key : null;
	}

	/**
	 * @inheritDoc
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function prepare() {
		return '%d';
	}
}
