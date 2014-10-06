<?php


class TribeNullRecurrence extends TribeRecurrence {
	public function __construct() {
	}

	public function getDates() {
		return array();
	}
} 