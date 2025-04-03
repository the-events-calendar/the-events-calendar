<?php
/**
 * The API provided by each Validator implementation.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Model;

/**
 * Interface Validator
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
interface ValidatorInterface {
	/**
	 * Validates an input value with an optional context.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @return array The validation error messages.
	 */
	public function get_error_messages();

	/**
	 * Adds an error message to the list of errors retrieved by get_error_messages().
	 *
	 * @since 6.0.0
	 *
	 * @param string $message The error message to store.
	 *
	 * @return $this
	 */
	public function add_error_message( $message );

	/**
	 * Clears any stored error messages.
	 *
	 * @since 6.0.0
	 * @return $this
	 */
	public function clear_error_messages();
}
