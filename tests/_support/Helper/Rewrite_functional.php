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

	/**
	 * @throws \InvalidArgumentException If the path to the `.po` file is invalid.
	 */
	public function have_translation_file_for_plugin( string $source_mo_file, string $dest_mo_file_relpath ): void {
		if ( ! ( is_file( $source_mo_file ) && is_readable( $source_mo_file ) ) ) {
			throw new \InvalidArgumentException( "File not found: $source_mo_file" );
		}

		/** @var WPFilesystem $fs */
		$fs                = $this->getModule( 'WPFilesystem' );
		$plugin_mo_abspath = $fs->_getConfig( 'plugins' ) . rtrim( $dest_mo_file_relpath, '\\/' );

		if ( is_file( $plugin_mo_abspath ) && ! unlink( $plugin_mo_abspath ) ) {
			throw new \RuntimeException( "Failed to delete existing .mo file: $plugin_mo_abspath" );
		}

		if ( ! copy( $source_mo_file, $plugin_mo_abspath ) ) {
			throw new \RuntimeException( "Failed to copy .mo file: $source_mo_file to $plugin_mo_abspath" );
		}
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
