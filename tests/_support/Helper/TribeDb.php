<?php

namespace Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module\WPDb;

class TribeDb extends \Codeception\Module {

	/**
	 * @var WPDb
	 */
	protected $db;

	public function _initialize() {
		$this->db = $this->getModule( 'WPDb' );
	}

	public function getTribeOptionFromDatabase( $key, $default = '' ) {
		$options = $this->db->grabOptionFromDatabase( 'tribe_events_calendar_options' );
		if ( empty( $options ) ) {
			return $default;
		}

		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	public function setTribeOption( $key, $value ) {
		$option_name = 'tribe_events_calendar_options';
		$options     = $this->db->grabOptionFromDatabase( $option_name );
		if ( empty( $options ) ) {
			$this->db->haveOptionInDatabase( $option_name, [ $key => $value ] );
		} else {
			$this->db->haveOptionInDatabase( $option_name, array_merge( $options, [ $key => $value ] ) );
		}
	}
}