<?php
/**
 * Controller for interfacing with TEC\Common\Onboarding.
 *
 * @since 6.8.4
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Admin\Onboarding\API;
use TEC\Events\Admin\Onboarding\Steps\Optin;
use TEC\Events\Admin\Onboarding\Steps\Settings;
use TEC\Events\Admin\Onboarding\Steps\Organizer;
use TEC\Events\Admin\Onboarding\Steps\Venue;
use TEC\Events\Admin\Onboarding\Steps\Tickets;
use TEC\Events\Admin\Onboarding\Data;
use TEC\Events\Admin\Onboarding\Landing_Page;
use TEC\Common\StellarWP\Assets\Config;
use Tribe__Events__Main;

/**
 * Class Controller
 *
 * @since 6.8.4
 * @package TEC\Events\Admin\Onboarding
 */
class Controller extends Controller_Contract {
	/**
	 * The step instances.
	 *
	 * @since 6.13.0
	 *
	 * @var array
	 */
	protected $steps = [];

	/**
	 * Register the provider.
	 *
	 * @since 6.8.4
	 */
	public function do_register(): void {
		Config::add_group_path( 'tec-onboarding', tribe( 'tec.main' )->plugin_path . 'build/', 'wizard' );

		$this->steps = [
			'optin'     => new Optin(),
			'settings'  => new Settings(),
			'organizer' => new Organizer(),
			'venue'     => new Venue(),
			'tickets'   => new Tickets(),
		];

		$this->add_filters();
		$this->add_actions();

		$this->container->singleton( Landing_Page::class );
		$this->container->singleton( Data::class );
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since 6.8.4
	 */
	public function unregister(): void {
		$this->remove_filters();
		$this->remove_actions();
	}

	/**
	 * Add the filter hooks.
	 *
	 * @since 6.8.4
	 */
	public function add_filters(): void {
		// Add the step handlers.
		add_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['optin'], 'handle' ], 10, 2 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['settings'], 'handle' ], 11, 2 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['organizer'], 'handle' ], 12, 2 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['venue'], 'handle' ], 13, 2 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['tickets'], 'handle' ], 14, 2 );
		add_filter( 'tec_telemetry_is_tec_admin_page', [ $this, 'hide_telemetry_on_onboarding_page' ], 10, 1 );
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 6.8.4
	 * @since 6.11.0 Changed the priority of `admin_menu` to reposition menu item.
	 */
	public function add_actions(): void {
		add_action( 'admin_menu', [ $this, 'landing_page' ], 20 );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		add_action( 'admin_post_' . Landing_Page::DISMISS_PAGE_ACTION, [ $this, 'handle_onboarding_page_dismiss' ] );
		add_action( 'admin_notices', [ $this, 'remove_all_admin_notices_in_onboarding_page' ], -1 * PHP_INT_MAX );
		add_action( 'tec_admin_headers_about_to_be_sent', [ $this, 'redirect_tec_pages_to_guided_setup' ] );
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since 6.8.4
	 */
	public function remove_filters(): void {
		// Remove the step handlers.
		remove_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['optin'], 'handle' ], 10 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['settings'], 'handle' ], 11 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['organizer'], 'handle' ], 12 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['venue'], 'handle' ], 13 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ $this->steps['tickets'], 'handle' ], 14 );
		remove_filter( 'tec_telemetry_is_tec_admin_page', [ $this, 'hide_telemetry_on_onboarding_page' ], 10 );
	}

	/**
	 * Remove the action hooks.
	 *
	 * @since 6.8.4
	 * @since 6.11.0 Changed the priority of `admin_menu`.
	 */
	public function remove_actions(): void {
		remove_action( 'admin_menu', [ $this, 'landing_page' ], 20 );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
		remove_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		remove_action( 'admin_post_' . Landing_Page::DISMISS_PAGE_ACTION, [ $this, 'handle_onboarding_page_dismiss' ] );
		remove_action( 'admin_notices', [ $this, 'remove_all_admin_notices_in_onboarding_page' ], -1 * PHP_INT_MAX );
		remove_action( 'tec_admin_headers_about_to_be_sent', [ $this, 'redirect_tec_pages_to_guided_setup' ] );
	}

	/**
	 * Handle the onboarding page dismiss.
	 *
	 * @since 6.8.4
	 *
	 * @return void
	 */
	public function handle_onboarding_page_dismiss(): void {
		$this->container->make( Landing_Page::class )->handle_onboarding_page_dismiss();
	}

	/**
	 * Redirects users to the Guided Setup page when accessing any TEC settings or management page for the first time.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function redirect_tec_pages_to_guided_setup(): void {
		// Do not redirect if they are already on the Guided Setup page. Also prevents an infinite loop if $force is true.
		$page = tec_get_request_var( 'page' );
		if ( Landing_Page::$slug === $page ) {
			return;
		}

		/**
		 * Allows bypassing the checks for if we've don't need to/have already visited the Guided Setup page.
		 * Still respects the post type checks.
		 *
		 * @since 6.13.0
		 *
		 * @param bool $force Whether to force the redirect to the Guided Setup page.
		 *
		 * @return bool
		 */
		$force = (bool) apply_filters( 'tec_events_onboarding_force_redirect_to_guided_setup', false );

		// Do not redirect if the target is not The Events Calendar-related admin pages.
		$post_type = tec_get_request_var( 'post_type' );

		/**
		 * Filters the list of post types associated with The Events Calendar admin pages.
		 *
		 * @since 6.11.0
		 *
		 * @param array $post_types An array of post type slugs associated with The Events Calendar admin.
		 */
		$post_types = apply_filters(
			'tec_events_admin_post_types',
			[
				Tribe__Events__Main::POSTTYPE,
				Tribe__Events__Main::VENUE_POST_TYPE,
				Tribe__Events__Main::ORGANIZER_POST_TYPE,
				'tribe_event_series', // @todo: Have ECP hook in and add this via the filter.
			]
		);

		if ( ! in_array( $post_type, $post_types, true ) ) {
			return;
		}

		if ( ! $force ) {
			// Do not redirect if they have been to the Guided Setup page already.
			if ( (bool) tribe_get_option( 'tec_onboarding_wizard_visited_guided_setup', false ) ) {
				return;
			}

			// Do not redirect if they dismissed the Guided Setup page.
			if ( Landing_Page::is_dismissed() ) {
				return;
			}

			// Do not redirect if they have older versions and are probably already set up.
			$tec_versions = (array) tribe_get_option( 'previous_ecp_versions', [] );
			if ( count( $tec_versions ) > 1 ) {
				return;
			}
		}

		// If we're still here, redirect to the Guided Setup page.
		$setup_url = add_query_arg(
			[
				'post_type' => 'tribe_events',
				'page'      => Landing_Page::$slug,
			],
			admin_url( 'edit.php' )
		);

		// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, StellarWP.CodeAnalysis.RedirectAndDie.Error
		wp_safe_redirect( $setup_url );
		tribe_exit();
	}

	/**
	 * Remove all admin notices in the onboarding page.
	 *
	 * @since 6.9.0
	 */
	public function remove_all_admin_notices_in_onboarding_page(): void {
		if ( ! Landing_Page::is_on_page() ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
	}

	/**
	 * Settings page callback.
	 *
	 * @since 6.8.4
	 */
	public function landing_page() {
		$this->container->make( Landing_Page::class )->admin_page();
	}

	/**
	 * Enqueue scripts for the onboarding wizard.
	 *
	 * @since 6.8.4
	 */
	public function enqueue_assets(): void {
		$this->container->make( Landing_Page::class )->register_assets();
	}

	/**
	 * Registers the REST endpoints that will be used to return the Views HTML.
	 *
	 * @since 6.8.4
	 */
	public function register_rest_endpoints(): void {
		$this->container->make( API::class )->register();
	}

	/**
	 * Hide telemetry on the onboarding page by returning false when the page is detected.
	 *
	 * @since 6.13.0
	 *
	 * @param bool $is_tec_admin_page Whether the current page is a TEC admin page.
	 *
	 * @return bool
	 */
	public function hide_telemetry_on_onboarding_page( $is_tec_admin_page ): bool {
		if ( Landing_Page::is_on_page() ) {
			return false;
		}

		return $is_tec_admin_page;
	}
}
