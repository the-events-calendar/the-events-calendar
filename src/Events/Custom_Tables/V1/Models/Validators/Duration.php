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
	private $whole_number_validator;

	/**
	 * Duration constructor.
	 *
	 * @param  Valid_Date        $date_validator              A reference to the Date validator.
	 * @param  Range_Dates       $range_dates_validator       A reference to the Dates Range validator.
	 * @param  Positive_Integer  $whole_number_validator  A reference to the Positive Integer validator.
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
			if ( ! $this->check_against_dates( $model->start_date, $model->end_date, $duration ) ) {
				$this->add_error_message( "The {$duration} is greater than the duration of the event" );

				return false;
			}
		}

		if ( $model->start_date_utc && $model->end_date_utc ) {
			// If possible, validate against the entry Start and End Dates.
			if ( ! $this->check_against_dates( $model->start_date_utc, $model->end_date_utc, $duration ) ) {
				$this->add_error_message( "The {$duration} is greater than the duration of the event" );

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
	 * @param  string|int|\DateTimeInterface  $start_date  The Start Date.
	 * @param  string|int|\DateTimeInterface  $end_date    The End Date.
	 * @param  int                            $duration    The Duration value.
	 *
	 * @return bool Whether the Duration value is valid when validated in the context of the Dates or not.
	 */
	private function check_against_dates( $start_date, $end_date, $duration ) {
		$start_date_object = Dates::build_date_object( $start_date );
		$end_date_object   = Dates::build_date_object( $end_date );
		$date_duration     = $end_date_object->getTimestamp() - $start_date_object->getTimestamp();

		return $date_duration >= $duration;
	}
}
