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

/**
 * Class Start_Date_UTC
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class End_Date extends Validator {

	/**
	 * A Date Validator instance.
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
	 * End_Date constructor.
	 *
	 * @since 6.0.0
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
		if (
			( empty ( $model->start_date ) && ! $model->has_single_validation( 'end_date' ) )
			|| ! $this->date_validator->validate( $model, 'start_date', $value )
		) {
			$this->add_error_message( 'The start_date should be a valid date.' );

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
			$this->add_error_message( 'The end_date should be a valid date.' );

			return false;
		}

		if ( $this->range_dates->compare( $model->start_date, $model->end_date ) ) {
			return true;
		}

		$this->add_error_message( 'The end_date should happen after the start_date' );

		return false;
	}
}
