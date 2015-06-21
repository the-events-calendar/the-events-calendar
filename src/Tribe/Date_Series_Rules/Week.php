<?php


	/**
	 * Rules for weekly recurrences
	 */
	class Tribe__Events__Pro__Date_Series_Rules__Week implements Tribe__Events__Pro__Date_Series_Rules__Rules_Interface {

		private $weeks_between;
		private $days;

		/**
		 * The class constructor.
		 *
		 * @param int   $weeks_between The number of weeks between recurrences.
		 * @param array $days          The days on which an event recurs.
		 */
		public function __construct( $weeks_between = 1, $days = array() ) {
			$this->weeks_between = $weeks_between;
			$this->days = $days; // days are integers representing days
			sort( $this->days );
		}

		/**
		 * Get the next date of a recurrence.
		 *
		 * @param int $curdate The timestamp of the current instance of event.
		 *
		 * @return int The timestamp of the next recurrence.
		 */
		public function getNextDate( $curdate ) {
			$nextdate = $curdate;

			if ( count( $this->days ) > 0 ) {
				// get current day of week
				$curDayOfWeek = date( 'N', $curdate );

				// find the selected day that is equal or higher to the current day
				$nextDayOfWeek = $this->getNextDayOfWeek( $curDayOfWeek );

				while ( date( 'N', $nextdate ) != $nextDayOfWeek ) {
					$nextdate = strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $nextdate ) . ' + 1 day' );
				}

				if ( $nextDayOfWeek > $curDayOfWeek ) {
					return strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $nextdate ) );
				} elseif ( $nextDayOfWeek < $curDayOfWeek ) {
					return strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $nextdate ) . ' + ' . ( $this->weeks_between - 1 ) . ' weeks' );
				}
			}

			return strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $nextdate ) . ' + ' . $this->weeks_between . ' weeks' );
		}

		/**
		 * Get the next day-of-the-week that the event occurs on.
		 *
		 * @param int $curDayOfWeek The index of the current day-of-week.
		 *
		 * @return int The index of the next date of recurrence.
		 */
		private function getNextDayOfWeek( $curDayOfWeek ) {
			foreach ( $this->days as $day ) {
				if ( $day > $curDayOfWeek ) {
					return $day;
				}
			}

			return $this->days[0];
		}
	}
