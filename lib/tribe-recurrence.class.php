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

	public function  __construct($start_date, $end, $series_rules, $by_occurrence_count = false) {
		$this->start_date = $start_date;
		$this->end = $end;
		$this->series_rules = $series_rules;
		$this->by_occurrence_count = $by_occurrence_count;
	}

	/**
	 * Using the rules engine, find all dates in the series 
	 *
	 * @return An array of all dates in the series
	 */
	public function getDates() {
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

			return $dates;
		}
	}
}