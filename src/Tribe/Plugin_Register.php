<?php


class Tribe__Events__Plugin_Register extends Tribe__Abstract_Plugin_Register {

	protected $main_class   = 'Tribe__Events__Main';
	protected $dependencies = [
		'addon-dependencies' => [
			'Tribe__Events__Pro__Main'                 => '6.0.6-dev',
			'Tribe__Events__Filterbar__View'           => '5.4.0-dev',
			'Tribe__Events__Community__Main'           => '4.10.0-dev',
			'Tribe__Events__Community__Tickets__Main'  => '4.8.0-dev',
			'Tribe__Tickets__Main'                     => '5.5.3-dev',
			'Tribe__Tickets_Plus__Main'                => '5.6.2-dev',
			'Tribe__Events__Tickets__Eventbrite__Main' => '4.6.11-dev',
		],
	];

	public function __construct() {
		$this->base_dir = TRIBE_EVENTS_FILE;
		$this->version  = Tribe__Events__Main::VERSION;

		$this->register_plugin();
	}
}
