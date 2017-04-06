<?php

/**
 * Test that things autoload properly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class Tribe_Autoloader_Test extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var array
	 */
	protected $expected_deprecated_file = [];

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
		$this->expected_deprecated_file[] = dirname( dirname( dirname( __FILE__ ) ) ) . '/src/deprecated/' . $class . '.php';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}

	/**
	 * Test if a class exists that is autoloaded through the common plugin and deprecated.
	 */
	public function test_deprecated_common_class_exists() {
		$class = 'Tribe__Events__App_Shop';
		$this->expected_deprecated_file[] = dirname( dirname( dirname( __FILE__ ) ) ) . '/common/src/deprecated/' . $class . '.php';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}
}