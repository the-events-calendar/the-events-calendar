<?php
namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

/**
 * Class Integer_Key_Formatter
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
class Integer_Key_Formatter implements Formatter {
	/**
	 * Format a key if it was provided
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @return string
	 */
	public function prepare() {
		return '%d';
	}
}
