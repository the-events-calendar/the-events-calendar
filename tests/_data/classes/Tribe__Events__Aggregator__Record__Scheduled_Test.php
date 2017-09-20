<?php

class Tribe__Events__Aggregator__Record__Scheduled_Test extends Tribe__Events__Aggregator__Record__Abstract {

	/**
	 * @var bool
	 */
	public $is_schedule = true;

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return 'test';
	}
}