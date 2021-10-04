<?php
/**
 * The API provided by each Validator implementation.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Custom_Tables\V1\Models\Validators;

use TEC\Custom_Tables\V1\Models\Model;

/**
 * Interface Validator
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */
interface Validator {
	/**
	 * Validates an input value with an optional context.
	 *
	 * @since TBD
	 *
	 * @param  Model   $model  An optional context to use for the validation.
	 * @param  string  $name   The name of the parameter being modified.
	 * @param  mixed   $value  The actual value being saved.
	 *
	 * @return bool Whether the input value is valid or not.
	 */
	public function validate( Model $model, $name, $value );

	/**
	 * Get the validation error message.
	 *
	 * @return string The validation error message.
	 */
	public function message();
}
