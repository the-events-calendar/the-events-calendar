<?php

/**
 * Given a start date, series end (end date or number of occurrences), and rules engine; find me all the dates in a recurrence
 */
class Tribe__Events__Pro__Recurrence {
	const NO_END = -1;
	private $start_date;
	private $end;
	/** @var Tribe__Events__Pro__Date_Series_Rules__Rules_Interface */
	private $series_rules;
	private $by_occurrence_count;
	private $event;
	private $minDate = 0;
	private $maxDate = 2147483647; // Y2K38, an arbitrary limit. TODO: revisit this in twenty years
	private $last_request_constrained = false;

	public function  __construct( $start_date, $end, $series_rules, $by_occurrence_count = false, $event = null, $start_time = null, $end_time = null ) {
		$this->start_date          = $start_date;
		$this->end                 = $end;
		$this->series_rules        = $series_rules;
		$this->by_occurrence_count = $by_occurrence_count;
		$this->event               = $event;
		$this->start_time          = $start_time;
		$this->end_time            = $end_time;
	}

	/**
	 * Adjusts the start time of a date
	 *
	 * @param timestamp $date Date timestamp to adjust
	 */
	public function adjust_start_time( $date ) {
		if ( ! $this->start_time ) {
			return $date;
		}

		$date = date( 'Y-m-d', $date ) . ' ' . $this->start_time;
		$date = strtotime( $date );

		return $date;
	}

	public function setMinDate( $timestamp ) {
		$this->minDate = (int) $timestamp;
	}

	public function setMaxDate( $timestamp ) {
		$this->maxDate = (int) $timestamp;
	}

	/**
	 * Using the rules engine, find all dates in the series
	 *
	 * @param bool  $all_events      Return ALL instances?
	 * @param array $old_start_dates The old start dates for an event.
	 *
	 * @return array An array of all dates in the series
	 */
	public function getDates() {
		$this->last_request_constrained = false;
		if ( $this->series_rules ) {
			$dates    = array();
			$cur_date = $this->start_date;

			$i = 0;
			while ( $cur_date = $this->getNextDate( $cur_date ) ) {
				$i ++;
				if ( $cur_date > $this->maxDate ) {
					$this->last_request_constrained = $cur_date;
					break; // no more dates will be in range. stop here
				}
				if ( $cur_date < $this->minDate ) {
					continue; // move forward until we find a date within range
				}
				if ( $this->afterSeries( $this->by_occurrence_count ? $i : $cur_date ) ) {
					break; // end of the series
				}

				$dates[] = $this->adjust_start_time( $cur_date );
			}

			return $dates;
		}

		return array();
	}

	/**
	 * Flag indicating if the last getDates() request was constrained
	 * by the max date setting
	 * @return bool
	 */
	public function constrainedByMaxDate() {
		return $this->last_request_constrained;
	}

	/**
	 * Get the next date in the series
	 *
	 * @param int $current_date
	 *
	 * @return bool|int The date, as a timestamp, or FALSE if it exceeds the system's max int
	 */
	private function getNextDate( $current_date ) {
		$next_date = $this->series_rules->getNextDate( $current_date );
		if ( intval( $next_date ) < $current_date ) { // bit overflow
			return false;
		}
		// Makes sure to assign the proper hours to the date.
		$next_date = mktime( date( 'H', $this->start_date ), date( 'i', $this->start_date ), date( 's', $this->start_date ), date( 'n', $next_date ), date( 'j', $next_date ), date( 'Y', $next_date ) );

		return $next_date;
	}

	private function afterSeries( $instance ) {
		if ( $this->end == self::NO_END ) {
			return false;
		}

		return $instance > $this->end;
	}
}
