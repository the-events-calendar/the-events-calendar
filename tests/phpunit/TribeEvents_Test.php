<?php

/**
 * An example test checking if the TribeEvents class exists after initialization.
 *
 * @group core
 *
 * @package TribeEvents
 * @since 2.0.5
 */
class TribeEvents_Test extends WP_UnitTestCase {
	
	/**
	 * Test if the TribeEvents class exists.
	 *
 	 * @since 2.0.5
	 */
	function test_events_class_exists() {
		$class = 'TribeEvents';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' ); 
	}
	
}