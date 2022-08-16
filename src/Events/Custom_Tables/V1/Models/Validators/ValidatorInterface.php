<?php
/**
 * The API provided by each Validator implementation.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Model;

/**
 * Interface Validator
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
interface ValidatorInterface {
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
	 * Get the validation error messages.
	 *
	 * @since TBD
	 *
	 * @return array The validation error messages.
	 */
	public function get_error_messages();

	/**
	 * Adds an error message to the list of errors retrieved by get_error_messages().
	 *
	 * @since TBD
	 *
	 * @param string $message The error message to store.
	 *
	 * @return $this
	 */
	public function add_error_message( $message );

	/**
	 * Clears any stored error messages.
	 *
	 * @since TBD
	 * @return $this
	 */
	public function clear_error_messages();
}
