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
 * Class Positive_Integer
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Integer_Key extends Validator {
	/**
	 * Variable reference to the positive integer validator.
	 *
	 * @since 6.0.0
	 *
	 * @var Positive_Integer positive_integer
	 */
	private $positive_integer;
	/**
	 * Validator to check if an integer key is provided.
	 *
	 * @since 6.0.0
	 *
	 * @var Present present
	 */
	private $present;

	/**
	 * Positive_Integer constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param  Present           $present  The present validator.
	 * @param  Positive_Integer  $positive_integer
	 */
	public function __construct( Present $present, Positive_Integer $positive_integer ) {
		$this->positive_integer = $positive_integer;
		$this->present          = $present;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {

		// Move forward this model does not have the key yet, so an insert should take place.
		if ( ! $this->present->validate( $model, $name, $value ) ) {
			return true;
		}

		// If the value is present but it was set as null it's also a valid value.
		if ( $value === null ) {
			return true;
		}

		$positive_integer = $this->positive_integer->validate( $model, $name, $value );
		if ( $positive_integer ) {
			return true;
		}

		$this->add_error_message( 'The provided value was not a valid positive integer.' );

		return false;
	}
}
