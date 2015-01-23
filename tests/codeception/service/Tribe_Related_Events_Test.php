<?php

/**
 * Class Tribe_Related_Events_Test
 *
 * @group pro
 * @group related
 */
class Tribe_Related_Events_Test extends WP_UnitTestCase {
	public function test_enabled_by_default() {
		$ecp = Tribe__Events__Pro__Events_Pro::instance();
		$this->assertTrue($ecp->show_related_events());
	}

	public function test_disable_related_events() {
		$core = Tribe__Events__Events::instance();
		$ecp = Tribe__Events__Pro__Events_Pro::instance();
		$core->setOption('hideRelatedEvents', TRUE);

		$this->assertFalse($ecp->show_related_events());

	}
}
