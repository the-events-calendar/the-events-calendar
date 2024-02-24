<?php
/**
 * Validates a Start Date UTC input.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use TEC\Events\Custom_Tables\V1\Models\Validators\Whole_Number;
use TEC\Events\Custom_Tables\V1\Models\Model;
use Tribe__Date_Utils as Dates;

/**
 * Class Start_Date_UTC
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Duration extends Validator {
	/**
	 * @var Whole_Number
	 */
	protected $whole_number_validator;

	/**
	 * Duration constructor.
	 *
	 * @param Whole_Number $whole_number_validator A reference to the Positive Integer validator.
	 */
	public function __construct( Whole_Number $whole_number_validator) {
		$this->whole_number_validator = $whole_number_validator;
	}

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
			if ( ! $this->check_against_dates( $model->start_date, $model->end_date, $duration, $model->timezone ) ) {
				$this->add_error_message( "The Event duration ({$duration}) is greater than the one calculated using dates." );

				return false;
			}
		}

		if ( $model->start_date_utc && $model->end_date_utc ) {
			// If possible, validate against the entry Start and End Dates.
			if ( ! $this->check_against_dates( $model->start_date_utc, $model->end_date_utc, $duration, 'UTC' ) ) {
				$this->add_error_message( "The Event duration ({$duration}) is greater than the one calculated using UTC dates." );

				return false;
			}
		}

		return true;
	}

	/**
	 * Checks the duration against a date couple.
	 *
	 * The Duration value represents the value of a single event occurrence.
	 * As such it should be less than, or equal for Single Events, to the difference between End and Start.
	 *
	 * @since 6.0.0
	 *
	 * @param string|int|\DateTimeInterface $start_date The Start Date.
	 * @param string|int|\DateTimeInterface $end_date   The End Date.
	 * @param int                           $duration   The Duration value.
	 * @param string|null                   $timezone   The timezone to use for the calculation.
	 *
	 * @return bool Whether the Duration value is valid when validated in the context of the Dates or not.
	 */
	protected function check_against_dates( $start_date, $end_date, int $duration, ?string $timezone = null ) {
		$start_date_object = Dates::build_date_object( $start_date, $timezone );
		$end_date_object = Dates::build_date_object( $end_date, $timezone );
		$date_duration     = $end_date_object->getTimestamp() - $start_date_object->getTimestamp();

		return $date_duration >= $duration;
	}
}
