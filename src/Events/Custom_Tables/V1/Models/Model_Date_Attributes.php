<?php
/**
 * Provides common methods for Models that return start and end date attributes
 * in string format.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */

namespace TEC\Events\Custom_Tables\V1\Models;

use DateTimeInterface;
use Tribe__Date_Utils as Dates;

/**
 * Trait Model_Date_Attributes
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */
trait Model_Date_Attributes {
	/**
	 * Returns the Model instance `start_date` attribute in string format.
	 *
	 * This method will be internally called when trying to access the `start_date`
	 * property of the Model instance.
	 *
	 * @since 6.0.0
	 *
	 * @return string The Model instance `start_date` attribute in string format.
	 */
	public function get_start_date_attribute() {
		return $this->data['start_date'] instanceof DateTimeInterface ?
			$this->data['start_date']->format( Dates::DBDATETIMEFORMAT )
			: $this->data['start_date'];
	}

	/**
	 * Returns the Model instance `start_date_utc` attribute in string format.
	 *
	 * This method will be internally called when trying to access the `start_date_utc`
	 * property of the Model instance.
	 *
	 * @since 6.0.0
	 *
	 * @return string The Model instance `start_date_utc` attribute in string format.
	 */
	public function get_start_date_utc_attribute() {
		return $this->data['start_date_utc'] instanceof DateTimeInterface ?
			$this->data['start_date_utc']->format( Dates::DBDATETIMEFORMAT )
			: $this->data['start_date_utc'];
	}

	/**
	 * Returns the Model instance `end_date` attribute in string format.
	 *
	 * This method will be internally called when trying to access the `end_date`
	 * property of the Model instance.
	 *
	 * @since 6.0.0
	 *
	 * @return string The Model instance `end_date` attribute in string format.
	 */
	public function get_end_date_attribute() {
		return $this->data['end_date'] instanceof DateTimeInterface ?
			$this->data['end_date']->format( Dates::DBDATETIMEFORMAT )
			: $this->data['end_date'];
	}

	/**
	 * Returns the Model instance `end_date_utc` attribute in string format.
	 *
	 * This method will be internally called when trying to access the `end_date_utc`
	 * property of the Model instance.
	 *
	 * @since 6.0.0
	 *
	 * @return string The Model instance `end_date_utc` attribute in string format.
	 */
	public function get_end_date_utc_attribute() {
		return $this->data['end_date_utc'] instanceof DateTimeInterface ?
			$this->data['end_date_utc']->format( Dates::DBDATETIMEFORMAT )
			: $this->data['end_date_utc'];
	}
}
