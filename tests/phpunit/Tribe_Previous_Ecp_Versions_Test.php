<?php

/**
 * Tests the previous version option to make sure it get's set properly
 *
 * @group core
 *
 * @package TribeEvents
 */
class Tribe_Previous_Ecp_Versions_Test extends WP_UnitTestCase {

	/**
	 * Check to make sure that the 'previous_ecp_versions' option exists.
	 *
	 */
	function test_previous_ecp_versions_exists() {
		$this->assertTrue( count( tribe_get_option( 'previous_ecp_versions' ) ) > 0 );
	}

	/**
	 * Check to make sure that 'previous_ecp_versions' is saving correctly.
 	 *
	 */
	function test_previous_ecp_versions_saving() {
		$tribe_ecp = TribeEvents::instance();
		$tribe_ecp->init();
		$tribe_ecp->setOption('latest_ecp_version', '1.6.5');
		$tribe_ecp->init();
		$previous_ecp_versions = tribe_get_option( 'previous_ecp_versions' );
		$this->assertEquals( '0', $previous_ecp_versions[0] );
		$this->assertEquals( '1.6.5', $previous_ecp_versions[1]);
		$this->assertFalse( isset( $previous_ecp_versions[2] ) );
	}

}