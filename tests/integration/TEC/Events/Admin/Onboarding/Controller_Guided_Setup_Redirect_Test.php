<?php
/**
 * Characterizes the on-visit redirect to the Guided Setup page.
 *
 * @package TEC\Events\Admin\Onboarding
 * @since   TBD
 */

namespace TEC\Events\Admin\Onboarding;

use Tribe\Tests\Traits\With_Uopz;
use Codeception\TestCase\WPTestCase;
use Tribe__Events__Main as TEC;
use Tribe__Settings_Manager;

/**
 * Class Controller_Guided_Setup_Redirect_Test
 *
 * Locks the behavior of redirect_tec_pages_to_guided_setup() so the shared-guard
 * refactor cannot silently change it.
 *
 * @since TBD
 */
class Controller_Guided_Setup_Redirect_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * Stored request globals so we can restore them after each test.
	 *
	 * @var array
	 */
	protected $get_vars = [];

	/**
	 * Stored request globals so we can restore them after each test.
	 *
	 * @var array
	 */
	protected $request_vars = [];

	/**
	 * Set up a clean, "fresh install" state before each test.
	 *
	 * @before
	 */
	public function set_up_state(): void {
		$this->get_vars     = $_GET;
		$this->request_vars = $_REQUEST;

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		// Prevent option state from leaking between tests. The tribe options live in both the WP
		// object cache (read by get_option) and a tribe var; neither is rolled back with the DB, so
		// clear both before establishing a known state.
		wp_cache_flush();
		tribe_unset_var( Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// Clean, first-run state: not visited, not dismissed, not an upgraded install.
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
		$_GET     = $this->get_vars;
		$_REQUEST = $this->request_vars;
		remove_all_filters( 'tec_events_onboarding_force_redirect_to_guided_setup' );
	}

	/**
	 * Simulate the request being on the given admin page.
	 *
	 * @param array $vars The request vars to set.
	 *
	 * @return void
	 */
	protected function set_request( array $vars ): void {
		foreach ( $vars as $key => $value ) {
			$_GET[ $key ]     = $value;
			$_REQUEST[ $key ] = $value;
		}
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
	public function it_should_redirect_on_a_tec_admin_page_for_a_fresh_install(): void {
		$this->set_request( [ 'post_type' => TEC::POSTTYPE ] );

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->redirect_tec_pages_to_guided_setup();

		$this->assertCount( 1, $store, 'A fresh install on a TEC page should redirect.' );
		$this->assertStringContainsString( 'page=' . Landing_Page::$slug, $store[0] );
	}

	/**
	 * @test
	 */
	public function it_should_not_redirect_when_already_on_the_setup_page(): void {
		$this->set_request(
			[
				'post_type' => TEC::POSTTYPE,
				'page'      => Landing_Page::$slug,
			]
		);

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->redirect_tec_pages_to_guided_setup();

		$this->assertCount( 0, $store, 'The setup page itself should not redirect (no loop).' );
	}

	/**
	 * @test
	 */
	public function it_should_not_redirect_on_a_non_tec_page(): void {
		$this->set_request( [ 'post_type' => 'page' ] );

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->redirect_tec_pages_to_guided_setup();

		$this->assertCount( 0, $store, 'A non-TEC admin page should not redirect.' );
	}

	/**
	 * @test
	 */
	public function it_should_not_redirect_when_already_visited(): void {
		$this->set_request( [ 'post_type' => TEC::POSTTYPE ] );
		tribe_update_option( 'tec_onboarding_wizard_visited_guided_setup', true );

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->redirect_tec_pages_to_guided_setup();

		$this->assertCount( 0, $store, 'An already-visited setup should not redirect again.' );
	}

	/**
	 * @test
	 */
	public function it_should_redirect_when_forced_even_if_visited(): void {
		$this->set_request( [ 'post_type' => TEC::POSTTYPE ] );
		// Visited would normally block, but the force filter must bypass that check.
		tribe_update_option( 'tec_onboarding_wizard_visited_guided_setup', true );
		add_filter( 'tec_events_onboarding_force_redirect_to_guided_setup', '__return_true' );

		$store = [];
		$this->capture_redirects( $store );

		tribe( Controller::class )->redirect_tec_pages_to_guided_setup();

		$this->assertCount( 1, $store, 'The force filter should bypass the visited check.' );
	}
}
