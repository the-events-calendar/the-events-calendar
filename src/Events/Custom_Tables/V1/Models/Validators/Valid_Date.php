<?php
/**
 * Validates an Date input.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Model;
use Tribe__Date_Utils as Dates;

/**
 * Class Valid_Date
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Valid_Date extends Validator {
	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {

		if ( empty( $value ) ) {
			return true;
		}

		// The value is already a date time object.
		if ( $value instanceof \DateTimeInterface ) {
			return true;
		}

		if ( ! is_string( $value ) ) {
			$this->add_error_message( "If the value is not a \DateTimeInterface it must be a string." );

			return false;
		}

		if ( Dates::is_valid_date( $value ) ) {
			return true;
		}

		$this->add_error_message( "The provided value is not a valid date." );

		return false;
	}
}
