<?php
class Recurrence {
	private $start_date;
	private $end_date;
	private $recurrence_type;

	public function  __construct($start_date, $end_date, $recurrence_type) {
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		$this->recurrence_type = $recurrence_type;
	}

	public function getDates() {
		$dates = array();
		$cur_date = $this->start_date;

		while($cur_date <= $this->end_date) {
			$cur_date = $recurrence_type->getNextDate($cur_date);
			$dates[] = $cur_date;
		}

		return $dates;
	}
}