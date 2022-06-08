<?php


class Tribe__Events__Plugin_Register extends Tribe__Abstract_Plugin_Register {

	protected $main_class   = 'Tribe__Events__Main';
	protected $dependencies = [
		'addon-dependencies' => [
			'Tribe__Events__Pro__Main'                 => '6.0.0-dev',
			'Tribe__Events__Filterbar__View'           => '5.3.0-dev',
			'Tribe__Events__Community__Main'           => '4.9.0-dev',
			'Tribe__Events__Community__Tickets__Main'  => '4.7.2-dev',
			// @todo @moraleida update the version below to the actual version of ET that will be released to support RBE
			'Tribe__Tickets__Main'                     => '5.3.1-dev',
			'Tribe__Events__Tickets__Eventbrite__Main' => '4.6.11-dev',
		],
	];

	public function __construct() {
		$this->base_dir = TRIBE_EVENTS_FILE;
		$this->version  = Tribe__Events__Main::VERSION;

		$this->register_plugin();
	}
}
