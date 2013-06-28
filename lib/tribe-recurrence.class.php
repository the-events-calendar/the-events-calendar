<?php
/**
 * Given a start date, series end (end date or number of occurrences), and rules engine; find me all the dates in a recurrence
 * @author John Gadbois
 */
class TribeRecurrence {
	private $start_date;
	private $end;
	private $series_rules;
	private $by_occurrence_count;
	private $event;

	public function  __construct($start_date, $end, $series_rules, $by_occurrence_count = false, $event = null) {
		$this->start_date = $start_date;
		$this->end = $end;
		$this->series_rules = $series_rules;
		$this->by_occurrence_count = $by_occurrence_count;
		$this->event = $event;
	}

	/**
	 * Using the rules engine, find all dates in the series 
	 *
	 * @param bool $all_events Return ALL instances?
	 * @param array $old_start_dates The old start dates for an event.
	 * @return An array of all dates in the series
	 */
	public function getDates( $all_events = true, $old_start_dates = null ) {
		if( $this->series_rules ) {
			$dates = array();
			$cur_date = $this->start_date;

			if($this->by_occurrence_count) {
				// a set number of occurrences
				for( $i = 0; $i < $this->end; $i++ ) {
					$cur_date = $this->series_rules->getNextDate($cur_date);
					// Makes sure to assign the proper hours to the date.
					$cur_date = mktime (date("H", $this->start_date), date("i", $this->start_date), date("s", $this->start_date), date('n', $cur_date),  date('j', $cur_date), date('Y', $cur_date));
					$dates[] = $cur_date;
				}				
			} else {
				// date driven
				while($cur_date <= $this->end) {
					$cur_date = $this->series_rules->getNextDate($cur_date);
					// Makes sure to assign the proper hours to the date.
					$cur_date = mktime (date("H", $this->start_date), date("i", $this->start_date), date("s", $this->start_date), date('n', $cur_date),  date('j', $cur_date), date('Y', $cur_date));
					if($cur_date <= $this->end)
						$dates[] = $cur_date;
				}
			}

			if ( !$all_events && $old_start_dates && $this->event ) {
				$existing_dates = array();
				foreach ( $old_start_dates as $index => $date ) {
					$date_obj = date_create( $date );
					$existing_dates[] = $date_obj->format( 'U' );
				}
				foreach ( $dates as $index => $date ) {
					if ( !in_array( $date, $existing_dates ) ) {
						unset( $dates[$index] );
					}
				}
			}
			
			return $dates;
		}
	}
}