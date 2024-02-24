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
use Tribe__Timezones as Timezones;

/**
 * Class Valid_Timezone
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Valid_Timezone extends Validator {
	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		// The value is already a timezone object.
		if ( $value instanceof \DateTimeZone ) {
			return true;
		}

		$is_valid_timezone = Timezones::is_valid_timezone( $value );

		if ( ! $is_valid_timezone ) {
			$this->add_error_message( 'The provided timezone is not a valid timezone.' );
		}

		return $is_valid_timezone;
	}
}
