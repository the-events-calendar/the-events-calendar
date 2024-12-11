<?php
/**
 * Controller for interfacing with TEC\Common\Onboarding.
 *
 * @since 7.0.0
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

/**
 * Class Controller
 *
 * @since 7.0.0
 * @package TEC\Events\Admin\Onboarding
 */
class Controller extends Controller_Contract {

	/**
	 * Register the provider.
	 *
	 * @since 7.0.0
	 */
	public function do_register(): void {
		$this->add_filters();
		$this->add_actions();

		$this->container->singleton( Landing_Page::class );
		$this->container->singleton( Data::class );
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since 7.0.0
	 */
	public function unregister(): void {
		$this->remove_filters();
		$this->remove_actions();
	}

	/**
	 * Add the filter hooks.
	 *
	 * @since 7.0.0
	 */
	public function add_filters(): void {
		// Add the step handlers.
		add_filter( 'tec_events_onboarding_wizard_handle', [ Optin::class, 'handle' ], 10, 3 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ Settings::class, 'handle' ], 11, 3 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ Organizer::class, 'handle' ], 12, 3 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ Venue::class, 'handle' ], 13, 3 );
		add_filter( 'tec_events_onboarding_wizard_handle', [ Tickets::class, 'handle' ], 14, 3 );
		add_filter( 'tec_events_admin_notice_utc_timezone_should_display', [ $this, 'should_not_display_notices_on_onboarding_page' ] );
		add_filter( 'tec_events_admin_notice_event_tickets_should_display', [ $this, 'should_not_display_notices_on_onboarding_page' ] );
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 7.0.0
	 */
	public function add_actions(): void {
		add_action( 'admin_menu', [ $this, 'landing_page' ] );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
		add_action( 'admin_post_' . Landing_Page::DISMISS_ONBOARDING_PAGE_ACTION, [ $this, 'handle_onboarding_page_dismiss' ] );
	}

	/**
	 * Handle the onboarding page dismiss.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function handle_onboarding_page_dismiss(): void {
		$this->container->make( Landing_Page::class )->handle_onboarding_page_dismiss();
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since 7.0.0
	 */
	public function remove_filters(): void {
		// Remove the step handlers.
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Optin::class, 'handle' ], 10 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Settings::class, 'handle' ], 11 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Organizer::class, 'handle' ], 12 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Venue::class, 'handle' ], 13 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Tickets::class, 'handle' ], 14 );
		remove_filter( 'tec_events_admin_notice_utc_timezone_should_display', [ $this, 'should_not_display_notices_on_onboarding_page' ] );
	}

	/**
	 * Remove the action hooks.
	 *
	 * @since 7.0.0
	 */
	public function remove_actions(): void {
		remove_action( 'admin_menu', [ $this, 'landing_page' ] );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
		remove_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
	}

	/**
	 * Settings page callback.
	 *
	 * @since 7.0.0
	 */
	public function landing_page() {
		$this->container->make( Landing_Page::class )->admin_page();
	}

	/**
	 * Should not display notices on onboarding page.
	 *
	 * @since 7.0.0
	 *
	 * @param bool $should_display Whether the notices should display.
	 *
	 * @return bool
	 */
	public function should_not_display_notices_on_onboarding_page( bool $should_display ) {
		return $this->container->make( Landing_Page::class )->should_not_display_notices_on_onboarding_page( $should_display );
	}

	/**
	 * Enqueue scripts for the onboarding wizard.
	 *
	 * @since 7.0.0
	 */
	public function enqueue_assets(): void {
		$this->container->make( Landing_Page::class )->register_assets();
	}

	/**
	 * Registers the REST endpoints that will be used to return the Views HTML.
	 *
	 * @since 7.0.0
	 */
	public function register_rest_endpoints(): void {
		$this->container->make( API::class )->register();
	}
}
