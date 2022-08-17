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
abstract class Validator implements ValidatorInterface {
	/**
	 * The error message if the validation fails.
	 *
	 * @since TBD
	 *
	 * @var array The error message if the validation fails.
	 */
	protected $error_messages = [];

	/**
	 * Get the validation error message.
	 *
	 * @since TBD
	 *
	 * @return array The validation error messages.
	 */
	public function get_error_messages() {
		return $this->error_messages;
	}

	/**
	 * Adds an error message to the list of errors.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return $this
	 */
	public function clear_error_messages() {
		$this->error_messages = [];

		return $this;
	}
}
