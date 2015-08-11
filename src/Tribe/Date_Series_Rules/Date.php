<?php

/**
 * Rules for specific date recurrences
 */
class Tribe__Events__Pro__Date_Series_Rules__Date implements Tribe__Events__Pro__Date_Series_Rules__Rules_Interface {

	private $date_timestamp;

	/**
	 * The class constructor.
	 *
	 * @param int $date_timestamp A date timestamp
	 */
	public function __construct( $date_timestamp ) {
		$this->date_timestamp = $date_timestamp;
	}

	/**
	 * Gets the timestamp of the next date of recurrence.
	 *
	 * @param int $curdate The current date's timestamp.
	 *
	 * @return int The next date's timestamp.
	 */
	public function getNextDate( $curdate ) {
		if ( $curdate < $this->date_timestamp ) {
			return $this->date_timestamp;
		}

		return null;
	}
}
