<?php

/**
 * @group updates
 */
class Tribe__Events__Updater_Test extends \Codeception\TestCase\WPTestCase {

	public function test_update_required() {
		$current_version = Tribe__Events__Main::VERSION;
		$updater = Tribe__Events__Main::instance()->updater();

		// set the existing version to be "old"
		$updater->update_version_option( '3.12' );

		$update_required = $updater->update_required();
		$this->assertTrue( $update_required, "Checking that 3.12 is less than $current_version" );

		// set the existing version to be current
		$updater->update_version_option( $current_version );

		$update_required = $updater->update_required();
		$this->assertFalse( $update_required, 'Checking that no upgrade is required when the versions match' );
	}

	public function test_get_version_from_db() {
		$version_from_settings_manager = Tribe__Settings_Manager::get_option( 'schema-version' );

		$updater = Tribe__Events__Main::instance()->updater();
		$version_from_updater = $updater->get_version_from_db();

		$this->assertEquals( $version_from_updater, $version_from_settings_manager, 'checking that the version from Settings Manager matches the version from Updater' );
	}

	public function test_update_version_option() {
		$current_version = Tribe__Events__Main::VERSION;
		$updater = Tribe__Events__Main::instance()->updater();
		$updater->update_version_option( $current_version );

		$version_in_db = $updater->get_version_from_db();

		$this->assertEquals( $version_in_db, $current_version, "checking that the version in the database was set to $current_version" );

		$updater->reset();

		$version_in_db = $updater->get_version_from_db();
		$this->assertEquals( $version_in_db, 3.9, 'checking that the version in the database was set to 3.9' );
	}

	public function test_get_update_callbacks() {
		$current_version = Tribe__Events__Main::VERSION;
		$updater = Tribe__Events__Main::instance()->updater();

		$updates = $updater->get_update_callbacks();
		foreach ( $updates as $version => $update_callable ) {
			$this->assertTrue( is_callable( $update_callable ), "checking defined update function is callable ($version)" );
		}
	}

	public function test_get_constant_update_callbacks() {
		$current_version = Tribe__Events__Main::VERSION;
		$updater = Tribe__Events__Main::instance()->updater();

		$contant_updates = $updater->get_constant_update_callbacks();
		foreach ( $contant_updates as $contant_update_callable ) {
			$this->assertTrue( is_callable( $contant_update_callable ), 'checking constant update function is callable' );
		}
	}

	public function test_constant_updates_applied() {
		$settings = Tribe__Settings_Manager::instance();
		$settings::set_option( 'schema-version', 0 );
		// it was probably added during wp bootstrap
		remove_action( 'wp_loaded', 'flush_rewrite_rules' );
		$this->assertFalse( has_action( 'wp_loaded', 'flush_rewrite_rules' ) );
		$updater = new Tribe__Events__Updater( '3.8' );
		$updater->do_updates();
		$this->assertNotEmpty( has_action( 'wp_loaded', 'flush_rewrite_rules' ) );
		remove_action( 'wp_loaded', 'flush_rewrite_rules' );
	}

	public function test_update_only_runs_once() {
		$settings = Tribe__Settings_Manager::instance();
		$settings::set_option( 'schema-version', 0 );
		remove_action( 'wp_loaded', 'flush_rewrite_rules' );
		$this->assertFalse( has_action( 'wp_loaded', 'flush_rewrite_rules' ) );
		$updater = new Tribe__Events__Updater( '3.10a0' );
		$updater->do_updates();
		$this->assertNotEmpty( has_action( 'wp_loaded', 'flush_rewrite_rules' ) );
		remove_action( 'wp_loaded', 'flush_rewrite_rules' );
		if ( $updater->update_required() ) {
			$updater->do_updates();
		}
		$this->assertFalse( has_action( 'wp_loaded', 'flush_rewrite_rules' ) );
	}
}
