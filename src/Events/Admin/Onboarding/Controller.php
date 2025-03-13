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

/**
 * Class Controller
 *
 * @since 6.8.4
 * @package TEC\Events\Admin\Onboarding
 */
class Controller extends Controller_Contract {

	/**
	 * Register the provider.
	 *
	 * @since 6.8.4
	 */
	public function do_register(): void {
		Config::add_group_path( 'tec-onboarding', tribe( 'tec.main' )->plugin_path . 'build/', 'wizard' );
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
		add_filter( 'tec_events_onboarding_wizard_handle', [ Optin::class, 'handle' ], 10, 2 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ Settings::class, 'handle' ], 11, 2 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ Organizer::class, 'handle' ], 12, 2 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ Venue::class, 'handle' ], 13, 2 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ Tickets::class, 'handle' ], 14, 2 );
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 6.8.4
	 */
	public function add_actions(): void {
		add_action( 'admin_menu', [ $this, 'landing_page' ] );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		add_action( 'admin_post_' . Landing_Page::DISMISS_ONBOARDING_PAGE_ACTION, [ $this, 'handle_onboarding_page_dismiss' ] );
		add_action( 'admin_notices', [ $this, 'remove_all_admin_notices_in_onboarding_page' ], -1 * PHP_INT_MAX );
		add_action( 'tec_redirect_guided_time_setup', [ $this, 'redirect_tec_pages_to_guided_setup' ] ); }

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
	 * Redirects users to the First Time Setup page when accessing any TEC settings or management page for the first time.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function redirect_tec_pages_to_guided_setup(): void {
		// If there is more than one previous version, don't redirect since they're probably already setup.
		$tec_versions = (array) tribe_get_option( 'previous_ecp_versions', [] );
		if ( count( $tec_versions ) > 1 ) {
			return;
		}

		// Get some information about the page that we are trying to access.
		$page      = tribe_get_request_var( 'page' );
		$post_type = tribe_get_request_var( 'post_type' );
		$taxonomy  = tribe_get_request_var( 'taxonomy' );

		// Only target The Events Calendar-related admin pages.
		if ( ! in_array( $post_type, [ 'tribe_events', 'tribe_event_series', 'tribe_venue', 'tribe_organizer' ], true ) ) {
			return;
		}

		// Prevent infinite redirect: If already on the First Time Setup page, do nothing.
		if ( 'first-time-setup' === $page ) {
			return;
		}

		// Check if the user has already been redirected to the First Time Setup page.
		$visited_first_time_setup = get_option( 'tec_onboarding_wizard_visited_first_time_setup', false );
		if ( $visited_first_time_setup ) {
			// If they already visited, no redirection is needed.
			return;
		}

		// Define the base scripts that should trigger the redirect.
		$redirect_scripts = [
			'/wp-admin/edit.php',       // Main Events page, Series, Instructors, Venues, Organizers.
			'/wp-admin/post-new.php',   // Add New Event.
			'/wp-admin/edit-tags.php',  // Tags, Event Categories, Venue Categories, Organizer Categories.
		];

		// Define admin pages that use the 'page' parameter.
		$redirect_admin_pages = [
			'aggregator',         // Import.
			'tec-events-settings', // Settings.
		];

		// Get the current script name and sanitize it.
		$current_script = isset( $_SERVER['SCRIPT_NAME'] ) ? esc_url_raw( $_SERVER['SCRIPT_NAME'] ) : '';

		if ( in_array( $current_script, $redirect_scripts, true ) || in_array( $page, $redirect_admin_pages, true ) ) {
			$setup_url = add_query_arg(
				[
					'post_type' => 'tribe_events',
					'page'      => 'first-time-setup',
				],
				admin_url( 'edit.php' )
			);

			// Stop redirecting once we send the user to the First Time Setup page once.
			update_option( 'tec_onboarding_wizard_visited_first_time_setup', true );

			wp_safe_redirect( $setup_url );
			exit;
		}
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since 6.8.4
	 */
	public function remove_filters(): void {
		// Remove the step handlers.
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Optin::class, 'handle' ], 10 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Settings::class, 'handle' ], 11 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Organizer::class, 'handle' ], 12 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Venue::class, 'handle' ], 13 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Tickets::class, 'handle' ], 14 );
	}

	/**
	 * Remove the action hooks.
	 *
	 * @since 6.8.4
	 */
	public function remove_actions(): void {
		remove_action( 'admin_menu', [ $this, 'landing_page' ] );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
		remove_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		remove_action( 'admin_notices', [ $this, 'remove_all_admin_notices_in_onboarding_page' ], -1 * PHP_INT_MAX );
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
}
