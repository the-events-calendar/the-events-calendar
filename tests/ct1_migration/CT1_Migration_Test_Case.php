<?php

use tad\WPBrowser\Module\WPLoader\FactoryStore;

class CT1_Migration_Test_Case extends \Codeception\Test\Unit {
	protected $backupGlobals = false;

	public static function setUpBeforeClass() {
		// This will load all the factories.
		$factories = new FactoryStore();
		if ( ! class_exists( '\\WP_UnitTest_Factory_For_Thing' ) ) {
			$factories->getThingFactory( 'post' );
		}
	}

	/**
	 * @before
	 */
	public function set_user_to_admin() {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$admin_users = get_users( [ 'role' => 'administrator', 'limit' => 1, 'fields' => 'ids' ] );
		if ( ! count( $admin_users ) ) {
			throw new RuntimeException( 'No administrator user found!' );
		}
		wp_set_current_user( reset( $admin_users ) );
	}
}
