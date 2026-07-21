<?php
/**
 * Tests the on-activation redirect to the Guided Setup page.
 *
 * @package TEC\Events\Admin\Onboarding
 * @since   TBD
 */

namespace TEC\Events\Admin\Onboarding;

use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe__Settings_Manager;

/**
 * Class Controller_Activation_Redirect_Test
 *
 * @since TBD
 */
class Controller_Activation_Redirect_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * The transient set by Tribe__Events__Main::activate() on a single activation.
	 *
	 * @var string
	 */
	const ACTIVATION_TRANSIENT = '_tribe_events_activation_redirect';

	/**
	 * Stored $_GET so we can restore it after each test.
	 *
	 * @var array
	 */
	protected $get_vars = [];

	/**
	 * Set up a clean, "fresh install" state before each test.
	 *
	 * @before
	 */
	public function set_up_state(): void {
		$this->get_vars = $_GET;

		// The person doing the activation can access the Guided Setup page.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		// Prevent option state from leaking between tests. The tribe options live in both the WP
		// object cache (read by get_option) and a tribe var; neither is rolled back with the DB, so
		// clear both before establishing a known state.
		wp_cache_flush();
		tribe_unset_var( Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// Clean, first-run state: not visited, not dismissed, not an upgraded install.
		delete_transient( self::ACTIVATION_TRANSIENT );
		tribe_update_option( 'tec_onboarding_wizard_visited_guided_setup', false );
		tribe_update_option( Landing_Page::DISMISS_PAGE_OPTION, false );
		tribe_update_option( 'previous_ecp_versions', [] );
	}

	/**
	 * Restore global state after each test.
	 *
	 * @after
	 */
	public function tear_down_state(): void {
		$_GET = $this->get_vars;
		delete_transient( self::ACTIVATION_TRANSIENT );
	}

	/**
	 * Register redirect capture and neuter the exit so the method returns.
	 *
	 * @param array $store Populated with every captured redirect.
	 *
	 * @return void
	 */
	protected function capture_redirects( array &$store ): void {
		$this->set_fn_return(
			'wp_safe_redirect',
			function ( $url, $status = 302, $redirect_by = '' ) use ( &$store ) {
				$store[] = $url;

				return true;
			},
			true
		);

		$this->set_fn_return( 'tribe_exit', fn() => true, true );
	}

	/**
	 * @test
	 */
	public function it_should_redirect_to_guided_setup_on_a_fresh_single_activation(): void {
		set_transient( self::ACTIVATION_TRANSIENT, 1, 30 );

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->maybe_redirect_to_guided_setup_on_activation();

		$this->assertCount( 1, $store, 'A fresh single activation should redirect once.' );
		$this->assertStringContainsString( 'page=' . Landing_Page::$slug, $store[0] );
		$this->assertFalse( get_transient( self::ACTIVATION_TRANSIENT ), 'The transient should be consumed.' );
	}

	/**
	 * @test
	 */
	public function it_should_not_redirect_during_a_bulk_activation(): void {
		set_transient( self::ACTIVATION_TRANSIENT, 1, 30 );
		// WordPress adds this to the Plugins screen URL after a bulk activate.
		$_GET['activate-multi'] = 'true';

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->maybe_redirect_to_guided_setup_on_activation();

		$this->assertCount( 0, $store, 'A bulk activation should not redirect.' );
		$this->assertFalse( get_transient( self::ACTIVATION_TRANSIENT ), 'The transient should still be consumed.' );
	}

	/**
	 * @test
	 */
	public function it_should_not_redirect_without_the_activation_transient(): void {
		// No transient set: this is a normal admin page load.
		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->maybe_redirect_to_guided_setup_on_activation();

		$this->assertCount( 0, $store, 'A normal page load should not redirect.' );
	}

	/**
	 * @test
	 */
	public function it_should_not_redirect_when_the_guided_setup_was_already_visited(): void {
		set_transient( self::ACTIVATION_TRANSIENT, 1, 30 );
		tribe_update_option( 'tec_onboarding_wizard_visited_guided_setup', true );

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->maybe_redirect_to_guided_setup_on_activation();

		$this->assertCount( 0, $store, 'An already-visited setup should not redirect again.' );
	}

	/**
	 * @test
	 */
	public function it_should_not_redirect_when_the_guided_setup_was_dismissed(): void {
		set_transient( self::ACTIVATION_TRANSIENT, 1, 30 );
		tribe_update_option( Landing_Page::DISMISS_PAGE_OPTION, true );

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->maybe_redirect_to_guided_setup_on_activation();

		$this->assertCount( 0, $store, 'A dismissed setup should not redirect.' );
	}

	/**
	 * @test
	 */
	public function it_should_not_redirect_on_an_upgraded_install(): void {
		set_transient( self::ACTIVATION_TRANSIENT, 1, 30 );
		// More than one recorded version means an established install, not a fresh one.
		tribe_update_option( 'previous_ecp_versions', [ '6.0.0', '6.1.0' ] );

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->maybe_redirect_to_guided_setup_on_activation();

		$this->assertCount( 0, $store, 'An upgraded install should not redirect.' );
	}
}
