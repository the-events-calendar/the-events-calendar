<?php
/**
 * Validates Occurrence Duration input.
 *
 * @since 6.0.1
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Model;

/**
 * Class Occurrence_Duration
 *
 * @since 6.0.1
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Occurrence_Duration extends Duration {

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		// Duration cannot be negative
		if ( ! $this->whole_number_validator->validate( $model, 'duration', $value ) ) {
			$this->add_error_message( 'Duration must be a positive integer' );

			return false;
		}

		$duration = (int) $value;

		if ( $model->start_date && $model->end_date ) {
			// If possible, validate against the entry Start and End Dates.
			if ( ! $this->check_against_dates( $model->start_date, $model->end_date, $duration, $this->get_occurrence_timezone( $model ) ) ) {
				$this->add_error_message( "The Occurrence duration ({$duration}) is greater than the one calculated using dates." );

				return false;
			}
		}

		if ( $model->start_date_utc && $model->end_date_utc ) {
			// If possible, validate against the entry Start and End Dates.
			if ( ! $this->check_against_dates( $model->start_date_utc, $model->end_date_utc, $duration, 'UTC' ) ) {
				$this->add_error_message( "The Occurrence duration ({$duration}) is greater than the one calculated using UTC dates." );

				return false;
			}
		}

		return true;
	}

	/**
	 * Fetch the timezone that relates to this occurrence.
	 *
	 * @since 6.0.1
	 *
	 * @param Occurrence $model The occurrence to fetch it's timezone for.
	 *
	 * @return string|null
	 */
	protected function get_occurrence_timezone( Occurrence $model ): ?string {
		$event = Event::find( $model->event_id );
		if ( $event instanceof Event ) {

			return $event->timezone;
		}

		return null;
	}
}
