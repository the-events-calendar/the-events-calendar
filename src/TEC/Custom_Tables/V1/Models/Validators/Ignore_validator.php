<?php
/**
 *
 *
 *
 * @since TBD
 */

namespace TEC\Custom_Tables\V1\Models\Validators;


use TEC\Custom_Tables\V1\Models\Model;

class Ignore_validator extends Validation {
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
