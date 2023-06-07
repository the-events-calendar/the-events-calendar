<?php


class Tribe__Events__Plugin_Register extends Tribe__Abstract_Plugin_Register {

	protected $main_class   = 'Tribe__Events__Main';
	protected $dependencies = [
		'addon-dependencies' => [
			'Tribe__Events__Pro__Main'                 => '6.0.9-dev',
			'Tribe__Events__Filterbar__View'           => '5.4.3-dev',
			'Tribe__Events__Community__Main'           => '4.10.4-dev',
			'Tribe__Events__Community__Tickets__Main'  => '4.8.5-dev',
			'Tribe__Tickets__Main'                     => '5.5.12-dev',
			'Tribe__Tickets_Plus__Main'                => '5.6.7-dev',
			'Tribe__Events__Tickets__Eventbrite__Main' => '4.6.13-dev',
			'TEC\Event_Automator'                      => '1.2.1-dev',
		],
	];

	public function __construct() {
		$this->base_dir = TRIBE_EVENTS_FILE;
		$this->version  = Tribe__Events__Main::VERSION;

		$this->register_plugin();
	}
}
