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
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

/**
 * Class End_Date_UTC
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class End_Date_UTC extends Validator {
	/**
	 * A Dates validator instance.
	 *
	 * @since 6.0.0
	 *
	 * @var Valid_Date
	 */
	private $date_validator;

	/**
	 * A Dates Range validator instance.
	 *
	 * @since 6.0.0
	 *
	 * @var Range_Dates
	 */
	private $range_dates;

	/**
	 * End_Date_UTC constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param  Valid_Date   $date_validator  A Date validator instance.
	 * @param  Range_Dates  $range_dates     A Dates Range validator instance.
	 */
	public function __construct( Valid_Date $date_validator, Range_Dates $range_dates ) {
		$this->date_validator = $date_validator;
		$this->range_dates    = $range_dates;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		// No end date is set, an empty value is considered valid.
		if ( empty( $model->end_date_utc ) ) {
			return true;
		}

		if ( empty( $model->start_date_utc ) && $model->has_single_validation( 'end_date_utc' ) && $this->date_validator->validate( $model, 'end_date_utc', $model->end_date_utc ) ) {
			return true;
		}

		if (
			( empty( $model->start_date_utc ) && ! $model->has_single_validation( 'end_date_utc' ) )
			|| ! $this->date_validator->validate( $model, 'start_date_utc', $model->start_date_utc )
		) {
			$this->add_error_message( 'The start_date_utc should be a valid date.' );

			return false;
		}

		// The end date is not a valid date.
		if ( ! $this->date_validator->validate( $model, 'end_date_utc', $model->end_date_utc ) ) {
			$this->add_error_message( 'The end_date_utc should be a valid date.' );

			return false;
		}

		if ( $model->timezone && $model->end_date ) {
			// If the End Date and Timezone are provided, the value should check out with those.
			$timezone            = Timezones::build_timezone_object( $model->timezone );
			$utc                 = Timezones::build_timezone_object( 'UTC' );
			$end_date_utc_object = Dates::build_date_object( $value, $utc );
			$end_date_object     = Dates::build_date_object( $model->end_date, $timezone );
			$end_date_object->setTimezone( Timezones::build_timezone_object( 'UTC' ) );

			if ( $end_date_object->format( Dates::DBDATETIMEFORMAT ) === $end_date_utc_object->format( Dates::DBDATETIMEFORMAT ) ) {
				return true;
			}

			$this->add_error_message( 'The end_date_utc does not match the value of end_date with the timezone.' );

			return false;
		}

		if ( ! $model->start_date_utc || $this->range_dates->compare( $model->start_date_utc, $value ) ) {
			return true;
		}

		$this->add_error_message( 'The end_date_utc should happen after the start_date_utc' );

		return false;
	}
}
