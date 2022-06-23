<?php


class Tribe__Events__Plugin_Register extends Tribe__Abstract_Plugin_Register {

	protected $main_class   = 'Tribe__Events__Main';
	protected $dependencies = [
		'addon-dependencies' => [
			'Tribe__Events__Pro__Main'                 => '6.0.0-beta3.2-dev',
			'Tribe__Events__Filterbar__View'           => '5.5.0-beta3.2-dev',
			'Tribe__Events__Community__Main'           => '4.10.0-beta-3.2-dev',
			'Tribe__Events__Community__Tickets__Main'  => '4.7.2-dev',
			// @todo @moraleida update the version below to the actual version of ET that will be released to support RBE
			'Tribe__Tickets__Main'                     => '5.6.0-beta3.2-dev',
			'Tribe__Tickets_Plus__Main'                => '5.7.0-beta3.2-dev',
			'Tribe__Events__Tickets__Eventbrite__Main' => '4.6.11-dev',
		],
	];

	public function __construct() {
		$this->base_dir = TRIBE_EVENTS_FILE;
		$this->version  = Tribe__Events__Main::VERSION;

		$this->register_plugin();
	}
}
