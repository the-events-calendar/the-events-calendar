<?php
/**
 * The API provided by each Validator implementation.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

/**
 * Class Validation
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
abstract class Validation implements Validator {
	/**
	 * The error message if the validation fails.
	 *
	 * @since TBD
	 *
	 * @var string The error message if the validation fails.
	 */
	protected $error_message = '';

	/**
	 * Get the validation error message.
	 *
	 * @since TBD
	 *
	 * @return string The validation error message.
	 */
	public function message() {
		return $this->error_message;
	}
}
