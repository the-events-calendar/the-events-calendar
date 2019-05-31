<?php

/**
 * Class Tribe__Events__REST__V1__Settings
 *
 * Adds and manages the TEC REST API settings.
 */
class Tribe__Events__REST__V1__Settings {

	/**
	 * @var Tribe__Events__REST__V1__System
	 */
	protected $system;

	/**
	 * Tribe__Events__REST__V1__Settings constructor.
	 *
	 * @param Tribe__Events__REST__V1__System $system
	 */
	public function __construct( Tribe__Events__REST__V1__System $system ) {
		$this->system = $system;
	}

	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	public function filter_tribe_addons_tab_fields( array $fields = array() ) {
		if ( ! $this->system->supports_wp_rest_api() ) {
			return $fields;
		}

		if ( ! $this->system->supports_tec_rest_api() ) {
			return $fields;
		}

		return $this->add_fields( $fields );
	}

	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	protected function add_fields( array $fields = array() ) {
		return $fields;
	}
}
