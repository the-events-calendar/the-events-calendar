<?php
/**
 * Controller for interfacing with TEC\Common\Onboarding.
 *
 * @since 7.0.0
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Telemetry\Telemetry;
use TEC\Common\StellarWP\Installer\Installer;
use TEC\Events\Admin\Onboarding\API;
use TEC\Events\Admin\Onboarding\Steps\Optin;
use TEC\Events\Admin\Onboarding\Steps\Settings;
use TEC\Events\Admin\Onboarding\Steps\Organizer;
use TEC\Events\Admin\Onboarding\Steps\Venue;
use TEC\Events\Admin\Onboarding\Steps\Tickets;
use TEC\Events\Admin\Onboarding\Data;
/**
 * Class Controller
 *
 * @since 7.0.0
 * @package TEC\Events\Admin\Onboarding
 */
class Controller extends Controller_Contract {

	/**
	 * The slug for the admin menu.
	 *
	 * @since 7.0.0
	 *
	 * @var string
	 */
	public static string $slug = 'first-time-setup';

	/**
	 * The slug for the parent page.
	 *
	 * @since 7.0.0
	 */
	public static string $parent_slug = 'tribe_events_page_';

	/**
	 * The slug for the admin page
	 *
	 * @since 7.0.0
	 *
	 * @var string
	 */
	public static string $page_slug = 'tribe_events_page_first-time-setup';

	/**
	 * Register the provider.
	 *
	 * @since 7.0.0
	 */
	public function do_register(): void {
		$this->add_filters();
		$this->add_actions();

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );
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
	 * Should only be active if we are in the admin.
	 *
	 * @since 7.0.0
	 */
	public function is_active(): bool {
		return true;
	}

	/**
	 * Defines wether the current page is the correct page.
	 *
	 * @since 7.0.0
	 */
	public function is_on_page(): bool {
		$admin_pages = tribe( 'admin.pages' );
		$admin_page  = $admin_pages->get_current_page();
		$page_slug   = $this->get_page_slug();

		return ! empty( $admin_page ) && $admin_page === $page_slug;
	}

	/**
	 * Get the page slug.
	 *
	 * @since 7.0.0
	 */
	public function get_page_slug(): string {
		if ( ! empty( static::$page_slug ) ) {
			return static::$page_slug;
		}

		static::$page_slug = static::$parent_slug . static::$slug;

		return static::$page_slug;
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
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 7.0.0
	 */
	public function add_actions(): void {
		add_action( 'admin_menu', [ $this, 'settings_page' ] );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since 7.0.0
	 */
	public function remove_filters(): void {
		// Remove the step handlers.
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Optin::class, 'handle' ], 10, 3 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Settings::class, 'handle' ], 11, 3 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Organizer::class, 'handle' ], 12, 3 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Venue::class, 'handle' ], 13, 3 );
		remove_filter( 'tec_events_onboarding_wizard_handle', [ Tickets::class, 'handle' ], 14, 3 );
	}

	/**
	 * Remove the action hooks.
	 *
	 * @since 7.0.0
	 */
	public function remove_actions(): void {
		remove_action( 'admin_menu', [ $this, 'settings_page' ] );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
		remove_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
	}

	/**
	 * Settings page callback.
	 *
	 * @since 7.0.0
	 */
	public function settings_page(): void {
		add_submenu_page(
			'edit.php?post_type=tribe_events',
			__( 'TEC Onboarding Wizard and Setup Page', 'the-events-calendar' ),
			__( 'First Time Setup', 'the-events-calendar' ),
			'manage_options',
			$this->get_page_slug(),
			[ $this, 'tec_onboarding_wizard_button' ],
			0
		);
	}

	/**
	 * Enqueue scripts for the onboarding wizard.
	 *
	 * @since 7.0.0
	 */
	public function enqueue_assets(): void {
		if ( ! $this->is_on_page() ) {
			return;
		}

		$plugin     = tribe( 'tec.main' );
		$asset_file = $plugin->plugin_path . 'src/build/wizard/index.asset.php';

		// Danger, Will Robinson.
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_script(
			'tec-events-onboarding-wizard-script',
			plugins_url( 'src/build/wizard/index.js', dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ),
			$asset['dependencies'],
			$asset['version'],
			[
				'in_footer' => true,
			]
		);

		wp_enqueue_style(
			'tec-events-onboarding-wizard-style',
			plugins_url( 'src/build/wizard/index.css', dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ),
			[ 'wp-components' ],
			$asset['version']
		);
	}

	/**
	 * Registers the REST endpoints that will be used to return the Views HTML.
	 *
	 * @since 7.0.0
	 */
	public function register_rest_endpoints(): void {
		$this->container->make( API::class )->register();
	}

	/**
	 * Get the initial data for the wizard.
	 *
	 * @since 7.0.0
	 *
	 * @return array<string, mixed> The initial data.
	 */
	public function get_initial_data(): array {
		$initial_data = [
			/* TEC settings */
			'tribeEnableViews'      => tribe_get_option( 'tribeEnableViews', [ 'list' ] ),
			'availableViews'        => $this->get_available_views(),
			'defaultCurrencySymbol' => tribe_get_option( 'defaultCurrencySymbol', '$' ),
			'date_format'           => get_option( 'date_format', 'F j, Y' ),
			'opt-in-status'         => (bool) tribe( Telemetry::class )->get_reconciled_telemetry_opt_in(),
			/* WP Settings */
			'timezone_string'       => get_option( 'timezone_string', false ),
			'start_of_week'         => get_option( 'start_of_week', false ),
			/* ET install step */
			'event-tickets'         => Installer::get()->is_installed( 'event-tickets' ),
			/* nonces */
			'action_nonce'          => wp_create_nonce( API::NONCE_ACTION ),
			'_wpnonce'              => wp_create_nonce( 'wp_rest' ),
			/* Linked posts */
			'organizer'             => $this->get_organizer_data(),
			'venue'                 => $this->get_venue_data(),
			/* Data */
			'timezones'             => Data::get_timezone_list(),
			'countries'             => Data::get_country_list(),
			'currencies'            => Data::get_currency_list(),
		];

		/**
		 * Filter the initial data.
		 *
		 * @since 7.0.0
		 *
		 * @param array    $initial_data The initial data.
		 * @param Controller $controller The controller object.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'tribe_events_onboarding_wizard_initial_data', $initial_data, $this );
	}

	/**
	 * Render the onboarding wizard button.
	 *
	 * @since 7.0.0
	 */
	public function tec_onboarding_wizard_button(): void {
		$button = get_submit_button(
			esc_html__( 'Open Install Wizard (current)', 'the-events-calendar' ),
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'id'                     => 'tec-events-onboarding-wizard',
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $this->get_initial_data() ),
			]
		);

		$button .= sprintf(
			'<div class="wrap" id="tec-events-onboarding-wizard-target">%s</div>',
			esc_html__( 'Loading…', 'the-events-calendar' )
		);

		echo $button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, StellarWP.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the organizer data.
	 * Looks for a single existing organizer and returns the data.
	 *
	 * @since 7.0.0
	 */
	public function get_organizer_data(): array {
		$organizer_id = tribe( 'events.organizer-repository' )->per_page( - 1 )->fields( 'ids' )->first();

		if ( empty( $organizer_id ) ) {
			return [];
		}

		return [
			'id'      => $organizer_id,
			'name'    => get_the_title( $organizer_id ),
			'email'   => get_post_meta( $organizer_id, '_OrganizerEmail', true ),
			'phone'   => get_post_meta( $organizer_id, '_OrganizerPhone', true ),
			'website' => get_post_meta( $organizer_id, '_OrganizerWebsite', true ),
		];
	}

	/**
	 * Get the venue data.
	 * Looks for a single existing venue and returns the data.
	 *
	 * @since 7.0.0
	 */
	public function get_venue_data(): array {
		$venue_id = tribe( 'events.venue-repository' )->per_page( - 1 )->fields( 'ids' )->first();

		if ( empty( $venue_id ) ) {
			return [];
		}

		return [
			'id'      => $venue_id,
			'name'    => get_the_title( $venue_id ),
			'address' => get_post_meta( $venue_id, '_VenueAddress', true ),
			'city'    => get_post_meta( $venue_id, '_VenueCity', true ),
			'country' => get_post_meta( $venue_id, '_VenueCountry', true ),
			'phone'   => get_post_meta( $venue_id, '_VenuePhone', true ),
			'state'   => get_post_meta( $venue_id, '_VenueState', true ),
			'website' => get_post_meta( $venue_id, '_VenueWebsite', true ),
			'zip'     => get_post_meta( $venue_id, '_VenueZip', true ),
		];
	}

	/**
	 * Get the available views.
	 *
	 * @since 7.0.0
	 */
	public function get_available_views(): array {
		$view_manager    = tribe( \Tribe\Events\Views\V2\Manager::class );
		$available_views = array_keys( $view_manager->get_registered_views() );
		$remove          = [
			'all',
			'latest-past',
			'organizer',
			'reflector',
			'venue',
			'widget-countdown',
			'widget-events-list',
			'widget-featured-venue',
			'widget-week',
		];

		$cleaned_views = array_flip( array_diff_key( array_flip( $available_views ), array_flip( $remove ) ) );

		return array_values( $cleaned_views );
	}
}
