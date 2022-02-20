<?php
/**
 * Validates an End Date UTC input.
 *
 * @since   TBD
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
class String_Validator extends Validator {
	/**
	 * Variable with a reference to the present validator.
	 *
	 * @since TBD
	 *
	 * @var Present present
	 */
	private $present;

	/**
	 * String_Validation constructor.
	 *
	 * @since TBD
	 *
	 * @param  Present  $present
	 */
	public function __construct( Present $present ) {
		$this->present = $present;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {

		if ( ! $this->present->validate( $model, $name, $value ) ) {
			$this->add_error_message( $this->present->message() );

			return false;
		}

		if ( is_string( $value ) ) {
			return true;
		}

		$this->add_error_message( "The key '{$name}' must be a string." );

		return false;
	}
}
