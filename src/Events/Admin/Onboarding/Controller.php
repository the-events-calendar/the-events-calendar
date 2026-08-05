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
	 * @since 6.17.2 Redirect to the Guided Setup page on a fresh activation.
	 */
	public function add_actions(): void {
		add_action( 'admin_menu', [ $this, 'landing_page' ], 20 );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		add_action( 'admin_post_' . Landing_Page::DISMISS_PAGE_ACTION, [ $this, 'handle_onboarding_page_dismiss' ] );
		add_action( 'admin_notices', [ $this, 'remove_all_admin_notices_in_onboarding_page' ], -1 * PHP_INT_MAX );
		add_action( 'tec_admin_headers_about_to_be_sent', [ $this, 'redirect_tec_pages_to_guided_setup' ] );
		add_action( 'tec_admin_headers_about_to_be_sent', [ $this, 'maybe_redirect_to_guided_setup_on_activation' ] );
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
	 * @since 6.17.2 Removed the activation redirect to the Guided Setup page.
	 */
	public function remove_actions(): void {
		remove_action( 'admin_menu', [ $this, 'landing_page' ], 20 );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
		remove_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		remove_action( 'admin_post_' . Landing_Page::DISMISS_PAGE_ACTION, [ $this, 'handle_onboarding_page_dismiss' ] );
		remove_action( 'admin_notices', [ $this, 'remove_all_admin_notices_in_onboarding_page' ], -1 * PHP_INT_MAX );
		remove_action( 'tec_admin_headers_about_to_be_sent', [ $this, 'redirect_tec_pages_to_guided_setup' ] );
		remove_action( 'tec_admin_headers_about_to_be_sent', [ $this, 'maybe_redirect_to_guided_setup_on_activation' ] );
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

		if ( ! $this->should_redirect_to_guided_setup() ) {
			return;
		}

		// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, StellarWP.CodeAnalysis.RedirectAndDie.Error
		wp_safe_redirect( $this->get_guided_setup_url() );
		tribe_exit();
	}

	/**
	 * Redirects to the Guided Setup page right after the plugin is activated on its own.
	 *
	 * A single (non-bulk) activation of The Events Calendar sets the
	 * `_tribe_events_activation_redirect` transient in Tribe__Events__Main::activate(). On the
	 * next admin page load we consume that transient and send the user to the Guided Setup page,
	 * mirroring how sister plugins greet a new install.
	 *
	 * @since 6.17.2
	 *
	 * @return void
	 */
	public function maybe_redirect_to_guided_setup_on_activation(): void {
		// Set on activation in Tribe__Events__Main::activate(). Absent means this is a normal page load.
		if ( ! get_transient( '_tribe_events_activation_redirect' ) ) {
			return;
		}

		// Do not hijack a bulk plugin activation. WordPress adds `activate-multi` to the Plugins
		// screen URL after several plugins are activated together, so the user stays in context.
		// Consume the flag here too: the answer is settled for this activation, and leaving it
		// behind would redirect the user on their very next admin page load instead.
		if ( null !== tec_get_request_var( 'activate-multi' ) ) {
			delete_transient( '_tribe_events_activation_redirect' );

			return;
		}

		// Do not redirect users who cannot access the Guided Setup page. Leave the flag in place:
		// it is site-wide and short-lived, so a user who can see the page should still be greeted
		// if they are the next one to land in the admin.
		if ( ! current_user_can( tribe( Landing_Page::class )->required_capability() ) ) {
			return;
		}

		// From here on the activation has been handled, whether or not we end up redirecting.
		delete_transient( '_tribe_events_activation_redirect' );

		if ( ! $this->should_redirect_to_guided_setup() ) {
			return;
		}

		// phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, StellarWP.CodeAnalysis.RedirectAndDie.Error
		wp_safe_redirect( $this->get_guided_setup_url() );
		tribe_exit();
	}

	/**
	 * Whether the user should be sent to the Guided Setup page.
	 *
	 * Shared by the on-visit redirect and the on-activation redirect so both honor the same
	 * conditions: skip if the page was already visited or dismissed, or if this is an upgraded
	 * install (more than one recorded version) rather than a fresh one.
	 *
	 * @since 6.17.2
	 *
	 * @return bool Whether the redirect should happen.
	 */
	protected function should_redirect_to_guided_setup(): bool {
		/**
		 * Allows bypassing the checks for whether we need to/have already visited the Guided Setup page.
		 *
		 * @since 6.13.0
		 *
		 * @param bool $force Whether to force the redirect to the Guided Setup page.
		 *
		 * @return bool
		 */
		$force = (bool) apply_filters( 'tec_events_onboarding_force_redirect_to_guided_setup', false );

		if ( $force ) {
			return true;
		}

		// Do not redirect if they have been to the Guided Setup page already.
		if ( (bool) tribe_get_option( 'tec_onboarding_wizard_visited_guided_setup', false ) ) {
			return false;
		}

		// Do not redirect if they dismissed the Guided Setup page. Read the option directly:
		// the page opts out of Abstract_Admin_Page::$is_dismissible, so is_dismissed() always
		// returns false, and dismissing should stop the redirect without hiding the menu item.
		if ( (bool) tribe_get_option( Landing_Page::DISMISS_PAGE_OPTION, false ) ) {
			return false;
		}

		// Do not redirect if they have older versions and are probably already set up.
		$tec_versions = (array) tribe_get_option( 'previous_ecp_versions', [] );
		if ( count( $tec_versions ) > 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * Builds the URL of the Guided Setup page.
	 *
	 * @since 6.17.2
	 *
	 * @return string The Guided Setup page URL.
	 */
	protected function get_guided_setup_url(): string {
		return add_query_arg(
			[
				'post_type' => 'tribe_events',
				'page'      => Landing_Page::$slug,
			],
			admin_url( 'edit.php' )
		);
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
