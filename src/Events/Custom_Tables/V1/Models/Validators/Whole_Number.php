<?php

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Model;

/**
 * Model validator for whole numbers.
 */
class Whole_Number extends Validator {

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {

		$valid = is_numeric( $value )
		         && $value == (int) $value
		         && abs( $value ) === (int) $value
		         && $value >= 0;

		if ( $valid ) {
			return true;
		}

		$this->add_error_message( 'The provided value is not a whole number.' );

		return false;
	}
}