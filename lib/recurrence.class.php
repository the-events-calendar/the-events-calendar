<?php
class Recurrence {
	private $start_date;
	private $end;
	private $series_rules;

	public function  __construct($start_date, $end, $series_rules) {
		$this->start_date = $start_date;
		$this->end = $end;
		$this->series_rules = $series_rules;
	}

	public function getDates() {
		if( $this->series_rules ) {
			$dates = array();
			$cur_date = $this->start_date;

			if(is_numeric($this->end)) {
				// a set number of occurrences
				for( $i = 0; $i < $this->end; $i++ ) {
					$cur_date = $this->series_rules->getNextDate($cur_date);
					$dates[] = $cur_date;
				}				
			} else {
				// date driven
				while($cur_date <= $this->end) {
					$cur_date = $this->series_rules->getNextDate($cur_date);

					if($cur_date <= $this->end)
						$dates[] = $cur_date;
				}
			}

			return $dates;
		}
	}
}