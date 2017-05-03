<?php
/**
 * Test that things autoload properly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class Autoloader_Test extends \Codeception\TestCase\WPTestCase {
	/**
	 * Test if a class exists that is autoloaded through the core plugin.
	 */
	public function test_class_exists() {
		$class = 'Tribe__Events__Main';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}

	/**
	 * Test if a class exists that is autoloaded through the common plugin.
	 */
	public function test_common_class_exists() {
		$class = 'Tribe__Main';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}

	/**
	 * Test if a class exists that is autoloaded through the plugin and deprecated.
	 */
	public function test_deprecated_class_exists() {
		$class = 'Tribe_Events_Single_Event_Template';
		add_filter( 'deprecated_file_trigger_error', '__return_false' );
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}

	/**
	 * Test if a class exists that is autoloaded through the common plugin and deprecated.
	 */
	public function test_deprecated_common_class_exists() {
		$class = 'Tribe__Events__App_Shop';
		add_filter( 'deprecated_file_trigger_error', '__return_false' );
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}
}