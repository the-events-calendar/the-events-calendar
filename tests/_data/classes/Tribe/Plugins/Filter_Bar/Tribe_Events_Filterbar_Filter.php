<?php

use Tribe\Events\Event_Status\Status_Labels;

/**
 * A mock class to test the compatibility with Fitler Bar
 */

class Tribe__Events__Filterbar__Filter {
	public $currentValue;

	public $joinClause = '';
	public $whereClause = '';

	public function __construct(){}

	public function get_title_field(){
		$status_labels = new Status_Labels();
		return $status_labels->get_event_status_label();
	}
}
