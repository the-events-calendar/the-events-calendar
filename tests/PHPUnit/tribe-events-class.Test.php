<?php
/* -- tribe-events-class.Test.php -- */

class WP_Test_TribeEventsClass extends Tribe_WP_UnitTestCase {
	
	function test_events_class_exists() {
		$class = 'TribeEvents';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' ); 
	}
	
}