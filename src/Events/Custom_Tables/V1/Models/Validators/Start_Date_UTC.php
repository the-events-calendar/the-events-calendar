<?php
/**
 * Validates a Start Date UTC input.
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
 * Class Start_Date_UTC
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Start_Date_UTC extends Validator {

	/**
	 * An instance of the Date validator.
	 *
	 * @since 6.0.0
	 *
	 * @var Valid_Date
	 */
	private $date_validator;
	/**
	 * An instance of the Date Ranges validator.
	 *
	 * @since 6.0.0
	 *
	 * @var Range_Dates
	 */
	private $range_dates;

	/**
	 * Start_Date_UTC constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param  Valid_Date   $date_validator  An instance of the Date validator.
	 * @param  Range_Dates  $range_dates     An instance of the Date Ranges validator.
	 */
	public function __construct( Valid_Date $date_validator, Range_Dates $range_dates ) {
		$this->date_validator = $date_validator;
		$this->range_dates    = $range_dates;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {

		if ( empty( $model->start_date_utc ) ) {
			$this->add_error_message( 'The start_date_utc requires a value.' );

			return false;
		}

		if ( ! $this->date_validator->validate( $model, 'start_date_utc', $value ) ) {
			$this->add_error_message( 'The value of start_date_utc is not a valid date.' );

			return false;
		}

		if ( $model->timezone && $model->start_date ) {
			// If the Start Date and Timezone are provided, the value should check out with those.
			$timezone              = Timezones::build_timezone_object( $model->timezone );
			$utc                   = Timezones::build_timezone_object( 'UTC' );
			$start_date_utc_object = Dates::immutable( $model->start_date_utc, $utc );
			$start_date_object     = Dates::immutable( $model->start_date, $timezone );

			if ( $start_date_object->format( 'U' ) === $start_date_utc_object->format( 'U' ) ) {
				return true;
			}

			$this->add_error_message( 'The start_date and start_date_utc has a conflict when using the timezone of the event.' );

			return false;
		}

		if ( ! $model->end_date_utc || $this->range_dates->compare( $model->start_date_utc, $model->end_date_utc ) ) {
			return true;
		}

		$this->add_error_message( 'The start_date_utc should before the end_date_utc' );

		return false;
	}
}
