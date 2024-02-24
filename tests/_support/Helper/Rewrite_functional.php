<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module\WPBrowser;
use Codeception\Module\WPDb;
use Codeception\Module\WPFilesystem;

class Rewrite_functional extends \Codeception\Module {

	/**
	 * Asserts the current response content equals the expected value.
	 *
	 * @param mixed $expected The expected value.
	 *
	 * @return void
	 * @throws \Codeception\Exception\ModuleException On failure.
	 */
	public function seeResponseIs( $expected ): void {
		/** @var WPBrowser $module */
		$module = $this->getModule( 'WPBrowser' );
		$this->assertEquals( $expected, $module->_getResponseContent() );
	}

	public function update_plugin_option( string $option_name, $option_value = null ): void {
		$options_name = 'tribe_events_calendar_options';
		/** @var WPDb $db */
		$db                      = $this->getModule( 'WPDb' );
		$options                 = (array) $db->grabOptionFromDatabase( $options_name );
		$options[ $option_name ] = $option_value;
		$db->haveOptionInDatabase( $options_name, $options );
	}
}
