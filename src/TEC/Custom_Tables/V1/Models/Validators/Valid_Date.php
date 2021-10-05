<?php
/**
 * Validates an Date input.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Custom_Tables\V1\Models\Validators;

use TEC\Custom_Tables\V1\Models\Model;
use Tribe__Date_Utils as Dates;

/**
 * Class Valid_Date
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */
class Valid_Date extends Validation {
	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		$this->error_message = '';

		if ( empty( $value ) ) {
			return true;
		}

		// The value is already a date time object.
		if ( $value instanceof \DateTimeInterface ) {
			return true;
		}

		if ( ! is_string( $value ) ) {
			$this->error_message = "If the value is not a \DateTimeInterface it must be a string.";

			return false;
		}

		if ( Dates::is_valid_date( $value ) ) {
			return true;
		}

		$this->error_message = "The provided value is not a valid date.";

		return false;
	}
}
