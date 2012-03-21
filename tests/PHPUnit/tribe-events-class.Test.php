<?php

/**
 * An example test checking if the TribeEvents class exists after initialization.
 *
 * @package TribeEvents
 * @since 2.0.5
 * @author Paul Hughes
 */
class WP_Test_TribeEventsClass extends Tribe_WP_UnitTestCase {
	
	/**
	 * Test if the TribeEvents class exists.
	 *
 	 * @since 2.0.5
	 * @author Paul Hughes
	 */
	function test_events_class_exists() {
		$class = 'TribeEvents';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' ); 
	}
	
}