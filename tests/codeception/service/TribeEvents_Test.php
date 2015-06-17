<?php

/**
 * An example test checking if the Tribe__Events__Main class exists after initialization.
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class TribeEvents_Test extends Tribe__Events__WP_UnitTestCase {

	/**
	 * Test if the Tribe__Events__Main class exists.
	 *
	 */
	function test_events_class_exists() {
		$class = 'Tribe__Events__Main';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );
	}

}