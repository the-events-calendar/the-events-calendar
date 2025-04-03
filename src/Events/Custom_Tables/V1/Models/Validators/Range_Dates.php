<?php
/**
 * Validates an End Date UTC input.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Events\Custom_Tables\V1\Models\Validators;

use DateTimeInterface;
use Tribe__Date_Utils as Dates;

/**
 * Class End_Date_UTC
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Validators
 */
class Range_Dates {

	/**
	 * Compares the Start Date and End Date of an event to make sure the End Date is equal to or after the Start Date.
	 *
	 * @since 6.0.0
	 *
	 * @param string|int|DateTimeInterface $start The Start Date to compare.
	 * @param string|int|DateTimeInterface $end   The End Date to compare.
	 *
	 * @return bool Whether the End Date is after the Start Date or not.
	 */
	public function compare( $start, $end ) {
		$start = Dates::build_date_object( $start );
		$end   = Dates::build_date_object( $end );

		return $end >= $start;
	}
}
