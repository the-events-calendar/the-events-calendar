<?php
/**
 * The API provided by each Validator implementation.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

/**
 * Class Validation
 *
 * @since 6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
abstract class Validator implements ValidatorInterface {
	/**
	 * The error message if the validation fails.
	 *
	 * @since 6.0.0
	 *
	 * @var array The error message if the validation fails.
	 */
	protected $error_messages = [];

	/**
	 * Get the validation error message.
	 *
	 * @since 6.0.0
	 *
	 * @return array The validation error messages.
	 */
	public function get_error_messages() {
		return $this->error_messages;
	}

	/**
	 * Adds an error message to the list of errors.
	 *
	 * @since 6.0.0
	 *
	 * @param string $message The error message to save.
	 *
	 * @return $this
	 */
	public function add_error_message( $message ) {
		$this->error_messages[] = $message;

		return $this;
	}

	/**
	 * Clears all the currently stored error messages.
	 *
	 * @since 6.0.0
	 *
	 * @return $this
	 */
	public function clear_error_messages() {
		$this->error_messages = [];

		return $this;
	}
}
