<?php
/* -- tribe-events-class.Test.php -- */

class WP_Test_TribeEventsClass extends WP_UnitTestCase {
	
	var $plugin_slug = 'the-events-calendar';
	
	function test_events_class_exists() {
		$class = 'TribeEvents';
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' ); 
	}
	
}