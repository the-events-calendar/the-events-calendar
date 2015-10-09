<?php
/**
 * Test that things are deprecated properly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class Tribe_Deprecated_Test extends Tribe__Events__WP_UnitTestCase {

	public function test_main_set_option() {
		$this->expected_deprecated[] = 'Tribe__Events__Main::setOption';
		Tribe__Events__Main::instance()->setOption( 'schema-version', 0 );
	}
}
