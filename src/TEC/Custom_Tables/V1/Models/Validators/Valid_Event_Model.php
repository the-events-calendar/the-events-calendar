<?php
/**
 * Validates an input Event ID (from the Events table) input.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Custom_Tables\V1\Models\Validators;

use TEC\Custom_Tables\V1\Models\Event;
use TEC\Custom_Tables\V1\Models\Model;

/**
 * Class Valid_Event_Model
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */
class Valid_Event_Model extends Validation{

	/**
	 * {@inheritdoc}
	 */
	public function validate( Model $model, $name, $value ) {
		$this->error_message = '';

		if ( empty( $value ) ) {
			return false;
		}

		$abs_value = absint( $value );

		if ( $abs_value !== (int) $value ) {
			return false;
		}

		$event = Event::find( $abs_value, 'event_id' );

		if ( ! $event instanceof Event ) {
			$this->error_message = 'The provided value is not a valid Event ID.';

			return false;
		}

		return true;
	}
}
