<?php
/**
 * Validates a Start Date UTC input.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Custom_Tables\V1\Models\Validators;

use TEC\Custom_Tables\V1\Models\Model;

/**
 * Class Start_Date_UTC
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */
class End_Date extends Validation {

	/**
	 * A Date Validator instance.
	 *
	 * @since TBD
	 *
	 * @var Valid_Date
	 */
	private $date_validator;

	/**
	 * A Dates Range validator instance.
	 *
	 * @since TBD
	 *
	 * @var Range_Dates
	 */
	private $range_dates;

	/**
	 * End_Date constructor.
	 *
	 * @since TBD
	 *
	 * @param  Valid_Date   $date_validator         A Date validator instance.
	 * @param  Range_Dates  $range_dates_validator  A Dates Range validator instance.
	 */
	public function __construct( Valid_Date $date_validator, Range_Dates $range_dates_validator ) {
		$this->date_validator = $date_validator;
		$this->range_dates    = $range_dates_validator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		$this->error_message = '';

		if (
			( empty ( $model->start_date ) && ! $model->has_single_validation( 'end_date' ) )
			|| ! $this->date_validator->validate( $model, 'start_date', $value )
		) {
			$this->error_message = 'The start_date should be a valid date.';

			return false;
		}

		// There's no end date this can be considered valid.
		if ( empty( $model->end_date ) ) {
			return true;
		}

		if ( empty ( $model->start_date ) && $model->has_single_validation( 'end_date' ) && $this->date_validator->validate( $model, 'end_date', $model->end_date ) ) {
			return true;
		}

		// The end date exists but is not valid.
		if ( ! $this->date_validator->validate( $model, 'end_date', $model->end_date ) ) {
			$this->error_message = 'The end_date should be a valid date.';

			return false;
		}

		if ( $this->range_dates->compare( $model->start_date, $model->end_date ) ) {
			return true;
		}

		$this->error_message = 'The end_date should happen after the start_date';

		return false;
	}
}
