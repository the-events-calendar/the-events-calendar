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
class Positive_Integer extends Validator {
	/**
	 * @var ValidatorInterface
	 */
	private $present;

	/**
	 * Positive_Integer constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param  Present  $present  The present validator.
	 */
	public function __construct( Present $present ) {
		$this->present = $present;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {

		$valid = $this->present->validate( $model, $name, $value )
		         && is_numeric( $value )
		         && $value == (int) $value
		         && abs( $value ) === (int) $value
		         && $value;

		if ( $valid ) {
			return true;
		}

		$this->add_error_message( 'The provided value was not a valid positive integer.' );

		return false;
	}
}
