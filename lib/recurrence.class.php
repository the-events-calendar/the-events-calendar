<?php
class Recurrence {
	private $start_date;
	private $end_date;
	private $series_rules;

	public function  __construct($start_date, $end_date, $series_rules) {
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		$this->series_rules = $series_rules;
	}

	public function getDates() {
		$dates = array();
		$cur_date = $this->start_date;

		while($cur_date <= $this->end_date) {
			$cur_date = $this->series_rules->getNextDate($cur_date);

			if($cur_date <= $this->end_date)
				$dates[] = $cur_date;
		}

		return $dates;
	}
}