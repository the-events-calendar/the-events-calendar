<?php


	/**
	 * Rules for daily recurrences
	 */
	class Tribe__Events__Pro__Date_Series_Rules__Day implements Tribe__Events__Pro__Date_Series_Rules__Rules_Interface {

		private $days_between;

		/**
		 * The class constructor.
		 *
		 * @param int $days_between The days between occurrences.
		 */
		public function __construct( $days_between = 1 ) {
			$this->days_between = $days_between;
		}

		/**
		 * Gets the timestamp of the next date of recurrence.
		 *
		 * @param int $curdate The current date's timestamp.
		 *
		 * @return int The next date's timestamp.
		 */
		public function getNextDate( $curdate ) {
			return strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $curdate ) . ' + ' . $this->days_between . ' days' );
		}
	}
