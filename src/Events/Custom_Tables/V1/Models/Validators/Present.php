<?php
/**
 * Validates an End Date UTC input.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Model;

/**
 * Class Present
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Present extends Validator {
	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {

		if ( isset( $model->{$name} ) ) {
			return true;
		}

		$this->add_error_message( "Make sure {$name} is part of the original entry." );

		return false;
	}
}
