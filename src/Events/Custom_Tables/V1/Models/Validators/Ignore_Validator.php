<?php
/**
 * A validator that will always validate.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Model;

/**
 * Class Ignore_Validator
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Ignore_Validator extends Validator {
	/**
	 * Consider any input as valid.
	 *
	 * @since TBD
	 *
	 * @param  Model   $model
	 * @param  string  $name
	 * @param  mixed   $value
	 *
	 * @return bool
	 */
	public function validate( Model $model, $name, $value ) {
		return true;
	}
}
