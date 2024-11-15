<?php
/**
 * Controller for interfacing with TEC\Common\Onboarding.
 *
 * @since 6.8.1
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Telemetry\Telemetry;
use TEC\Common\StellarWP\Installer\Installer;
use TEC\Events\Admin\Onboarding\Wizard;
use TEC\Events\Admin\Onboarding\Steps\Optin;
use TEC\Events\Admin\Onboarding\Steps\Settings;
use TEC\Events\Admin\Onboarding\Steps\Organizer;
use TEC\Events\Admin\Onboarding\Steps\Venue;
use TEC\Events\Admin\Onboarding\Steps\Tickets;
use TEC\Events\Admin\Onboarding\Data;
/**
 * Class Controller
 *
 * @since 6.8.1
 * @package TEC\Events\Admin\Onboarding
 */
class Controller extends Controller_Contract {

	/**
	 * The slug for the admin menu.
	 *
	 * @since 6.8.1
	 *
	 * @var string
	 */
	public static string $slug = 'onboarding-wizard';

	/**
	 * The slug for the parent page.
	 *
	 * @since 6.8.1
	 */
	public static string $parent_slug = 'tribe_events_page_';

	/**
	 * The slug for the admin page
	 *
	 * @since 6.8.1
	 *
	 * @var string
	 */
	public static string $page_slug = 'tribe_events_page_onboarding-wizard';

	/**
	 * Register the provider.
	 *
	 * @since 6.8.1
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
	 * @since 6.8.1
	 */
	public function unregister(): void {
		$this->remove_filters();
		$this->remove_actions();
	}

	/**
	 * Should only be active if we are in the admin.
	 *
	 * @since 6.8.1
	 */
	public function is_active(): bool {
		return true;
	}

	/**
	 * Defines wether the current page is the correct page.
	 *
	 * @since 6.8.1
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
	 * @since 6.8.1
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
	 * @since 6.8.1
	 */
	public function add_filters() {
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
	 * @since 6.8.1
	 */
	public function add_actions() {
		add_action( 'admin_menu', [ $this, 'settings_page' ] );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since 6.8.1
	 */
	public function remove_filters() {
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
	 * @since 6.8.1
	 */
	public function remove_actions() {
		remove_action( 'admin_menu', [ $this, 'settings_page' ] );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
		remove_action( 'rest_api_init', [ $this, 'register_rest_endpoints' ] );
	}

	/**
	 * Settings page callback.
	 *
	 * @since 6.8.1
	 */
	public function settings_page() {
		add_submenu_page(
			'edit.php?post_type=tribe_events',
			__( 'TEC Onboarding Wizard', 'the-events-calendar' ),
			__( 'Onboarding Wizard', 'the-events-calendar' ),
			'manage_options',
			$this->get_page_slug(),
			[ $this, 'tec_onboarding_wizard_button' ]
		);
	}

	/**
	 * Enqueue scripts for the onboarding wizard.
	 *
	 * @since 6.8.1
	 */
	public function enqueue_assets() {
		if ( ! $this->is_on_page() ) {
			return;
		}

		$plugin     = tribe( 'tec.main' );
		$asset_file = $plugin->plugin_path . 'src/build/wizard/index.asset.php';
		$asset      = include $asset_file;

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
			[ 'wp-components'],
			$asset['version']
		);
	}

	/**
	 * Registers the REST endpoints that will be used to return the Views HTML.
	 *
	 * @since 6.8.1
	 */
	public function register_rest_endpoints(): void {
		$this->container->make( Wizard::class )->register();
	}

	/**
	 * Render the onboarding wizard button.
	 *
	 * @since 6.8.1
	 */
	public function tec_onboarding_wizard_button(): void {
		// phpcs:disable
		$view_manager    = tribe( \Tribe\Events\Views\V2\Manager::class );
		$active_views    = array_keys( $view_manager->get_publicly_visible_views() );
		$first_boot_data = [
			'activeViews'       => $active_views,
			'availableViews'    => $this->get_available_views(),
			'defaultCurrency'   => tribe_get_option( 'defaultCurrencySymbol', false ),
			'defaultDateFormat' => tribe_get_option( 'dateWithYearFormat', get_option( 'date_format', false ) ),
			'defaultTimezone'   => tribe_get_option( 'timezone_string', get_option( 'timezone_string', false ) ),
			'defaultWeekStart'  => get_option( 'start_of_week', false ),
			'eventTickets'      => Installer::get()->is_installed( 'event-tickets' ),
			'action_nonce'      => wp_create_nonce( Wizard::NONCE_ACTION ),
			'_wpnonce'          => wp_create_nonce( 'wp_rest' ),
			'optin'             => (bool) tribe( Telemetry::class )->get_reconciled_telemetry_opt_in(),
			'organizer'         => $this->get_organizer_data(),
			'timezones'         => Data::get_timezone_list(),
			'countries'         => Data::get_country_list(),
			'venue'             => $this->get_venue_data(),
		];

		printf(
			'<div class="wrap" id="tec-events-onboarding-wizard-target">%s</div>'
			. get_submit_button(
				esc_html__( 'Open Install Wizard (current)', 'the-events-calendar' ),
				'secondary tec-events-onboarding-wizard',
				'open',
				true,
				[
					'data-container-element' => 'tec-events-onboarding-wizard-target',
					'data-wizard-boot-data'  => wp_json_encode( $first_boot_data ),
				]
			),
			esc_html__( 'Loading…', 'tec-events-onboarding-wizard' )
		);

		// phpcs:enable
	}

	/**
	 * Render the a "nulled" (all info empty, ignoring site settings) onboarding wizard button.
	 *
	 * @since 6.8.1
	 */
	public function get_null_button(): void {
		// phpcs:disable
		$null_data = [
			'activeViews'       => false,
			'availableViews'    => $this->get_available_views(),
			'defaultCurrency'   => false,
			'defaultDateFormat' => false,
			'defaultTimezone'   => false,
			'defaultWeekStart'  => false,
			'eventTickets'      => false,
			'action_nonce'      => wp_create_nonce( Wizard::NONCE_ACTION ),
			'_wpnonce'          => wp_create_nonce( 'wp_rest' ),
			'optin'             => false,
			'organizer'         => false,
			'timezones'         => Data::get_timezone_list(),
			'countries'         => Data::get_country_list(),
			'venue'             => false,
		];

		printf(
			'<div class="wrap" id="tec-events-onboarding-wizard-target">%s</div>'
			. get_submit_button(
				esc_html__( 'Open Install Wizard (nulled)', 'the-events-calendar' ),
				'secondary tec-events-onboarding-wizard',
				'open',
				true,
				[
					'data-container-element' => 'tec-events-onboarding-wizard-target',
					'data-wizard-boot-data'  => wp_json_encode( $null_data ),
				]
			),
			esc_html__( 'Loading…', 'tec-events-onboarding-wizard' )
		);
		// phpcs:enable
	}

	/**
	 * Get the organizer data.
	 * Looks for a single existing organizer and returns the data.
	 *
	 * @since 6.8.1
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
	 * @since 6.8.1
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
	 * @since 6.8.1
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
