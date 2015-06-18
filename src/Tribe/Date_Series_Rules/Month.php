<?php

	/**
	 * Rules for monthly recurrences
	 */
	class Tribe__Events__Pro__Date_Series_Rules__Month implements Tribe__Events__Pro__Date_Series_Rules__Rules_Interface {
		private $months_between;
		private $days_of_month;
		private $week_of_month;
		private $day_of_week;

		/**
		 * The class constructor.
		 *
		 * @param int   $months_between The number of months between recurrences.
		 * @param array $days_of_month  The days of the month on which recurrences occur.
		 * @param int   $week_of_month  The week of the month on which recurrences occur.
		 * @param int   $day_of_week    The index of the day of the week on which recurrences occur.
		 */
		public function __construct( $months_between = 1, $days_of_month = array(), $week_of_month = null, $day_of_week = null ) {
			$this->months_between = $months_between;
			$this->days_of_month  = (array) $days_of_month;
			$this->week_of_month  = $week_of_month;
			$this->day_of_week    = $day_of_week;

			sort( $this->days_of_month );
		}

		/**
		 * Get the timestamp of the next occurrence.
		 *
		 * @param int $curdate The current timestamp of a given occurrence.
		 *
		 * @return int The timestamp of the next occurrence.
		 */
		public function getNextDate( $curdate ) {
			$next_day_of_month = date( 'j', $curdate );

			if ( $this->week_of_month && $this->day_of_week ) {
				return $this->getNthDayOfWeek( $curdate, $this->day_of_week, $this->week_of_month );
			} else // normal date based recurrence
			{
				if ( count( $this->days_of_month ) > 0 ) {
					$next_day_of_month = $this->getNextDayOfMonth( $next_day_of_month );

					while ( Tribe__Events__Date_Utils::get_last_day_of_month( $curdate ) < $next_day_of_month ) {
						$next_day_of_month = $this->days_of_month[0];
						$curdate           = mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), date( 'n', $curdate ) + $this->months_between, 1, date( 'Y', $curdate ) );
					}
				}

				if ( $next_day_of_month > date( 'j', $curdate ) ) {
					// no need to jump ahead stay in current month
					return mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), date( 'n', $curdate ), $next_day_of_month, date( 'Y', $curdate ) );
				} else {
					$nextdate = mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), date( 'n', $curdate ) + $this->months_between, 1, date( 'Y', $curdate ) );

					while ( Tribe__Events__Date_Utils::get_last_day_of_month( $nextdate ) < $next_day_of_month ) {
						$nextdate = mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), date( 'n', $nextdate ) + $this->months_between, 1, date( 'Y', $nextdate ) );
					}

					return mktime( date( 'H', $curdate ), date( 'i', $curdate ), date( 's', $curdate ), date( 'n', $nextdate ), $next_day_of_month, date( 'Y', $nextdate ) );
				}
			}
		}

		/**
		 * Gets a given occurence on a given day-of-week.
		 *
		 * @param int $curdate       The current timestamp of an occurence.
		 * @param int $day_of_week   The index of a given day-of-week.
		 * @param int $week_of_month The index of a given week-of-month.
		 *
		 * @return int The timestamp of the requested occurrence.
		 */
		private function getNthDayOfWeek( $curdate, $day_of_week, $week_of_month ) {

			if ( $week_of_month == - 1 ) { // LAST WEEK
				$nextdate = Tribe__Events__Date_Utils::get_last_day_of_week_in_month( $curdate, $day_of_week );

				// If the date returned above is the same as the date we're starting from
				// move on to the next month by interval to consider.
				if ( $curdate == $nextdate ) {
					$curdate  = mktime( 0, 0, 0, date( 'n', $curdate ) + $this->months_between, 1, date( 'Y', $curdate ) );
					$nextdate = Tribe__Events__Date_Utils::get_last_day_of_week_in_month( $curdate, $day_of_week );
				}

				return $nextdate;
			} else {
				// get the first occurrence of the requested day of the week from the requested $curdate's month
				$first_occurring_day_of_week = Tribe__Events__Date_Utils::get_first_day_of_week_in_month( $curdate, $day_of_week );

				// get that day of the week in the requested nth week
				$maybe_date = strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $first_occurring_day_of_week ) . ' + ' . ( $week_of_month - 1 ) . ' weeks' );

				// if $maybe_date equals or is before the $curdate, then try next month
				// (this should only be true if $week_of_month is 1)
				if ( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_ONLY_FORMAT, $maybe_date ) <= date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_ONLY_FORMAT, $curdate ) ) {

					// get the first day of the next month according to $this->months_between
					$next_month = mktime( 0, 0, 0, date( 'n', $curdate ) + $this->months_between, 1, date( 'Y', $curdate ) );

					// Get the first occurrence of the requested day of the week from $next_month's month
					$first_occurring_day_of_week = Tribe__Events__Date_Utils::get_first_day_of_week_in_month( $next_month, $day_of_week );

					// Get that day of the week in the requested nth week
					$maybe_date = strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $first_occurring_day_of_week ) . ' + ' . ( $week_of_month - 1 ) . ' weeks' );
				}

				// if $maybe_date doesn't have the same month as $first_occurring_day_of_week, keep incrementing by $this->months_between
				// until they do, but don't infinitely loop past the 'recurrenceMaxMonthsAfter' setting
				$i = 0;
				while ( date( 'n', $maybe_date ) != date( 'n', $first_occurring_day_of_week ) && $i <= tribe_get_option( 'recurrenceMaxMonthsAfter', 24 ) ) {
					$next_month                  = mktime( 0, 0, 0, date( 'n', $first_occurring_day_of_week ) + $this->months_between, 1, date( 'Y', $first_occurring_day_of_week ) );
					$first_occurring_day_of_week = Tribe__Events__Date_Utils::get_first_day_of_week_in_month( $next_month, $day_of_week );
					$maybe_date                  = strtotime( date( Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, $first_occurring_day_of_week ) . ' + ' . ( $week_of_month - 1 ) . ' weeks' );
					$i += $this->months_between;
				}

				return $maybe_date;
			}
		}

		/**
		 * Gets the next day of the month on which an occurrence occurs.
		 *
		 * @param int $curDayOfMonth The index of the current day of the month.
		 *
		 * @return int The index of the next day of the month.
		 */
		private function getNextDayOfMonth( $curDayOfMonth ) {
			foreach ( $this->days_of_month as $day ) {
				if ( $day > $curDayOfMonth ) {
					return $day;
				}
			}

			return $this->days_of_month[0];
		}

		private function intToOrdinal( $number ) {
			switch ( $number ) {
				case 1:
					return 'First';
				case 2:
					return 'Second';
				case 3:
					return 'Third';
				case 4:
					return 'Fourth';
				case 5:
					return 'Fifth';
				case - 1:
					return 'Last';
				default:
					return null;
			}
		}
	}

