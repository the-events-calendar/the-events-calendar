<?php
/**
 * Validates an End Date UTC input.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Custom_Tables\V1\Models\Validators;

use TEC\Custom_Tables\V1\Models\Model;

/**
 * Class Present
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */
class Present extends Validation {
	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		$this->error_message = '';

		if ( isset( $model->{$name} ) ) {
			return true;
		}

		$this->error_message = "Make sure {$name} is part of the original entry.";

		return false;
	}
}
