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
 * Class Valid_Event
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Valid_Event extends Validator {
	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {

		$is_valid_event = tribe_is_event( $value );

		if ( ! $is_valid_event ) {
			$this->add_error_message( 'The provided input is not a valid Event type.' );
		}

		return $is_valid_event;
	}
}
