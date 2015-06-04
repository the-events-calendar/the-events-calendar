<?php


/**
 * @group updates
 */
class Tribe__Events__Updater_Test extends Tribe__Events__WP_UnitTestCase {
	public function test_constant_updates_applied() {
		Tribe__Events__Main::instance()->setOption( 'schema-version', 0 );
		// it was probably added during wp bootstrap
		remove_action( 'wp_loaded', 'flush_rewrite_rules' );
		$this->assertFalse( has_action( 'wp_loaded', 'flush_rewrite_rules' ) );
		$updater = new Tribe__Events__Updater( '3.8' );
		$updater->do_updates();
		$this->assertNotEmpty( has_action( 'wp_loaded', 'flush_rewrite_rules' ) );
		remove_action( 'wp_loaded', 'flush_rewrite_rules' );
	}

	public function test_update_only_runs_once() {
		Tribe__Events__Main::instance()->setOption( 'schema-version', 0 );
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