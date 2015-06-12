<?php


	/**
	 * Rules for yearly recurrences
	 */
	class Tribe__Events__Pro__Date_Series_Rules__Year implements Tribe__Events__Pro__Date_Series_Rules__Rules_Interface {

		private $years_between;
		private $months_of_year;
		private $week_of_month;
		private $day_of_week;

		/**
		 * The class constructor.
		 *
		 * @param int   $years_between  The number of years between recurrences.
		 * @param array $months_of_year The months in which recurrences occur.
		 * @param int   $week_of_month  The week of the month on which occurences occur.
		 * @param int   $day_of_week    The day of the week on which occurrences occur.
		 */
		public function __construct( $years_between = 1, $months_of_year = array(), $week_of_month = null, $day_of_week = null ) {
			$this->years_between = $years_between;
			$this->months_of_year = $months_of_year;
			$this->week_of_month = $week_of_month;
			$this->day_of_week = $day_of_week;

			sort( $this->months_of_year );
		}

		/**
		 * Get next date of occurence.
		 *
		 * @param int $curdate The timestamp of the current recurrence.
		 *
		 * @return int The timestamp of the next occurence.
		 */
		public function getNextDate( $curdate ) {
			$next_month_of_year = date( 'n', $curdate );
			$day_of_month = date( 'j', $curdate );

			if ( count( $this->months_of_year ) > 0 ) {
				$next_month_of_year = $this->getNextMonthOfYear( $next_month_of_year );
			}

			if ( $this->week_of_month && $this->day_of_week ) {
				// 4th wednesday of next month
				return $this->getNthDayOfMonth( $curdate, $this->day_of_week, $this->week_of_month, $next_month_of_year );
			} else // normal date based recurrence
			{
				$nextdate = $this->advanceDate( $curdate, $next_month_of_year );

				// TODO: TEST AHEAD FOR INVALID RECURSIONS (ie every February 29 or September 31 which will result in an infinite loop)
				while ( date( 'j', $curdate ) != date( 'j', $nextdate ) ) { // date wrapped
					$nextdate = strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $nextdate ) . ' - 1 months' ); // back it up a month to get to the correct one
					$next_month_of_year = $this->getNextMonthOfYear( date( 'n', $nextdate ) ); // get the next month in the series
					$nextdate = $this->advanceDate( $curdate, $next_month_of_year );
				}

				return mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), date( 'n', $nextdate ), date( 'j', $nextdate ), date( 'Y', $nextdate ) );
			}
		}

		/**
		 * Advance to the next recurrence date.
		 *
		 * @param int $curdate            The timestamp of the current recurrence.
		 * @param int $next_month_of_year The index of the next month of the year.
		 * @param int $day_of_month       The index of the day of month.
		 *
		 * @return int The timestamp of the next date.
		 */
		private function advanceDate( $curdate, $next_month_of_year, $day_of_month = null ) {
			if ( $next_month_of_year > date( 'n', $curdate ) ) { // is curdate correct here?
				$nextdate = mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), $next_month_of_year, $day_of_month ? $day_of_month : date( 'j', $curdate ), date( 'Y', $curdate ) );
			} else {
				$nextdate = mktime( 0, 0, 0, $next_month_of_year, $day_of_month ? $day_of_month : date( 'j', $curdate ), date( 'Y', $curdate ) + $this->years_between );
			}

			return $nextdate;
		}

		/**
		 * Get the timestamp of the Nth day of month.
		 *
		 * @param int $curdate            The current occurrence's timestamp.
		 * @param int $day_of_week        The index of the day-of-week.
		 * @param int $week_of_month      The index of the week of the month.
		 * @param int $next_month_of_year The index of the next month of the year.
		 *
		 * @return int The timestamp of the next occurrence on the nth day of the month.
		 */
		private function getNthDayOfMonth( $curdate, $day_of_week, $week_of_month, $next_month_of_year ) {
			$nextdate = $this->advanceDate( $curdate, $next_month_of_year, 1 ); // advance to correct month
			$nextdate = Tribe__Events__Date_Utils::get_first_day_of_week_in_month( $nextdate, $day_of_week );

			if ( $week_of_month == - 1 ) { // LAST WEEK
				$nextdate = Tribe__Events__Date_Utils::get_last_day_of_week_in_month( $nextdate, $day_of_week );

				return $nextdate;
			} else {
				$maybe_date = strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $nextdate ) . ' + ' . ( $week_of_month - 1 ) . ' weeks' );

				// if this doesn't exist, then try next month
				while ( date( 'n', $maybe_date ) != date( 'n', $nextdate ) ) {
					// advance again
					$next_month_of_year = $this->getNextMonthOfYear( date( 'n', $nextdate ) );
					$nextdate = $this->advanceDate( $nextdate, $next_month_of_year );
					$nextdate = Tribe__Events__Date_Utils::get_first_day_of_week_in_month( $curdate, $day_of_week );
					$maybe_date = strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $nextdate ) . ' + ' . ( $week_of_month - 1 ) . ' weeks' );
				}

				return $maybe_date;
			}
		}

		/**
		 * Get the index of the next month of the year on which an occurrence occurs.
		 *
		 * @param int $curMonth The index of the current month.
		 *
		 * @return int The index of the next month bearing a recurrence.
		 */
		private function getNextMonthOfYear( $curMonth ) {
			foreach ( $this->months_of_year as $month ) {
				if ( $month > $curMonth ) {
					return $month;
				}
			}

			return $this->months_of_year[0];
		}
	}

