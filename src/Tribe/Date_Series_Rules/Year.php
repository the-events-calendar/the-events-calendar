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
		 * Get the timestamp of the next upcoming Nth day of month.
		 *
		 * @param int       $curdate        The current occurrence's timestamp
		 * @param int|array $days_of_week    Index(-es) of the possible day(s)-of-week the next instance may land on
		 * @param int|array $weeks_of_month  Index(-es) of the possible week(s) of the month the next instance may land on
		 * @param int|array $months_of_year  Index(-es) of the possible month(s) of the year the next instance may land on
		 *
		 * @return int|false The timestamp of the next occurrence on the nth day of the month or false
		 */
		private function getNthDayOfMonth( $curdate, $days_of_week, $weeks_of_month, $months_of_year ) {
			// Cast to arrays for consistency
			$days_of_week   = (array) $days_of_week;
			$weeks_of_month = (array) $weeks_of_month;
			$months_of_year = (array) $months_of_year;

			// Obtain the hour, minute and second of $curdate for later comparison
			$cur_hour   = (int) date( 'G', $curdate );
			$cur_minute = (int) date( 'i', $curdate );
			$cur_second = (int) date( 's', $curdate );

			// Sort and rotate the arrays to give us a sensible starting point
			$this->sort_and_rotate_int_array( $days_of_week, (int) date( 'N', $curdate ) );
			$this->sort_and_rotate_int_array( $months_of_year, (int) date( 'n', $curdate ) );

			sort( $weeks_of_month );
			$weeks_of_month = array_map( 'intval', $weeks_of_month );

			// The next occurence must take place this year or the next applicable year
			$year  = (int) date( 'Y', $curdate );
			$years = array( $year, $year + $this->years_between );

			// Examine each possible year and month
			foreach ( $years as $year ) {
				foreach ( $months_of_year as $month ) {
					// If we are behind $curdate's month and year then keep advancing
					if ( $year <= date( 'Y', $curdate ) && $month < date( 'n', $curdate ) ) {
						continue;
					}

					foreach ( $weeks_of_month as $nth_week ) {
						foreach ( $days_of_week as $day ) {
							// Determine the date of the first of these days (ie, the date of the first Tuesday this month)
							$start_of_month = mktime( 0, 0, 0, $month, 1, $year );
							$first_date     = Tribe__Date_Utils::get_first_day_of_week_in_month( $start_of_month, $day );
							$day_of_month   = (int) date( 'j', $first_date );

							// Add the relevant number of weeks
							$week         = $nth_week > 0 ? $nth_week : abs( $nth_week );
							$direction    = $nth_week > 0 ? 1 : - 1;
							$day_of_month = date( 'j', Tribe__Date_Utils::get_weekday_timestamp( $day, $week, $month, $year, $direction ) );

							// Form a timestamp representing this day of the week in the appropriate week of the month
							$timestamp = mktime( $cur_hour, $cur_minute, $cur_second, $month, $day_of_month, $year );

							// If we got a valid timestamp that is ahead of $curdate, we have a winner
							if ( $timestamp && $timestamp > $curdate ) {
								return $timestamp;
							}
						}
					}
				}
			}

			// No match?
			return false;
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

		/**
		 * Given an array of integers, sorts them and then rotates them so the the first element
		 * is equal to or greater than $start_at.
		 *
		 * For example, given $intvals := [ 1, 2, 3, 4, 5 ] and $start_at := 4 the result would be:
		 *
		 *     [ 4, 5, 1, 2, 3 ]
		 *
		 * @param array &$intvals
		 * @param int   $start_at
		 */
		private function sort_and_rotate_int_array( array &$intvals, $start_at ) {
			sort( $intvals );
			$length = count( $intvals );

			// We can return $intvals right away when $start_at is either:
			// - lower than the lowest element
			// - higher than the highest element
			if ( $start_at > max( $intvals ) || min( $intvals ) > $start_at ) {
				return;
			}

			// Otherwise, let's rotate $intvals until the point where the first element is equal to or greater than $start_at
			for ( $i = 0; $i <= $length; $i++ ) {
				if ( $start_at > $intvals[ $i ] ) {
					$intvals[] = array_shift( $intvals );
				} else {
					break;
				}
			}

			return;
		}

		/**
		 * @return int
		 */
		public function get_years_between() {
			return $this->years_between;
		}

		/**
		 * @return array
		 */
		public function get_months_of_year() {
			return $this->months_of_year;
		}

		/**
		 * @return int|null
		 */
		public function get_week_of_month() {
			return $this->week_of_month;
		}

		/**
		 * @return int|null
		 */
		public function get_day_of_week() {
			return $this->day_of_week;
		}
	}

