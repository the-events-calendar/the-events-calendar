<?php
// Don't load directly
defined( 'WPINC' ) or die;

abstract class Tribe__Events__Aggregator__API__Abstract {
	public $service;
	public $cache_group = 'tribe_aggregator';

	public function __construct() {
		$this->service = Tribe__Events__Aggregator__Service::instance();
	}
}
