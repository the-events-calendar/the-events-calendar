<?php
/**
 * Controller for interfacing with TEC\Common\Onboarding.
 *
 * @since   TBD
 */

namespace TEC\Events\Admin\Onboarding;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Telemetry\Telemetry;
use TEC\Common\StellarWP\Installer\Installer;

/**
 * Class Controller
 *
 * @since   TBD
 * @package TEC\Events\Admin\Onboarding
 */
class Controller extends Controller_Contract {

	/**
	 * The slug for the admin menu.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $slug = 'onboarding-wizard';

	/**
	 * The slug for the parent page.
	 *
	 * @since TBD
	 */
	public static string $parent_slug = 'tribe_events_page_';

	/**
	 * The slug for the admin page
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static string $page_slug = 'tribe_events_page_onboarding-wizard';

	/**
	 * Register the provider.
	 *
	 * @since   TBD
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
	 * @since   TBD
	 */
	public function unregister(): void {
		$this->remove_filters();
		$this->remove_actions();
	}

	/**
	 * Should only be active if we are in the admin.
	 *
	 * @since   TBD
	 * @return bool Only active during FS theme.
	 */
	public function is_active(): bool {
		return is_admin();
	}

	/**
	 * Defines wether the current page is the correct page.
	 *
	 * @since TBD
	 *
	 * @return boolean
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
	 * @since TBD
	 *
	 * @return string
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
	 * @since   TBD
	 */
	public function add_filters() {}

	/**
	 * Add the action hooks.
	 *
	 * @since   TBD
	 */
	public function add_actions() {
		add_action( 'admin_menu', [ $this, 'settings_page' ] );
		add_action( 'admin_init', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Remove the filter hooks.
	 *
	 * @since   TBD
	 */
	public function remove_filters() {}

	/**
	 * Remove the action hooks.
	 *
	 * @since   TBD
	 */
	public function remove_actions() {
		remove_action( 'admin_menu', [ $this, 'settings_page' ] );
		remove_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Settings page callback.
	 *
	 * @since   TBD
	 */
	public function settings_page() {
		add_submenu_page(
			'edit.php?post_type=tribe_events',
			__( 'TEC Onboarding Wizard', 'the-events-calendar' ),
			__( 'Onboarding Wizard', 'the-events-calendar' ),
			'manage_options',
			$this->get_page_slug(),
			[ $this, 'tec_onboarding_wizard_html' ]
		);
	}

	/**
	 * Render the onboarding wizard HTML.
	 *
	 * @since   TBD
	 */
	public function tec_onboarding_wizard_html() {
		$view_manager     = tribe( \Tribe\Events\Views\V2\Manager::class );
		$availableViews  = array_keys( $view_manager->get_registered_views() );
		$activeViews     = array_keys( $view_manager->get_publicly_visible_views() );
		// Don't need these.
		$remove          = [
			"latest-past",
			"organizer",
			"reflector",
			"venue",
			"widget-countdown",
			"widget-events-list",
			"widget-featured-venue",
			"widget-week",
		];

		$cleanedViews  = array_flip( array_diff_key( array_flip( $availableViews ), array_flip( $remove ) ) );
		$availableViews = array_values( $cleanedViews );
		$tz_choices = $this->get_timezone_list();


		$first_boot_data = [
			'availableViews'   => $availableViews,
			'activeViews'      => $activeViews,
			'defaultCurrency'   => tribe_get_option( 'defaultCurrencySymbol', false ),
			'defaultDateFormat' => tribe_get_option( 'dateWithYearFormat', get_option( 'date_format', false )),
			'defaultTimezone'   => tribe_get_option( 'timezone_string', get_option( 'timezone_string', false ) ),
			'defaultWeekStart'  => get_option( 'start_of_week', false ),
			'eventTickets'     => Installer::get()->is_installed( 'event-tickets' ),
			'optin'             => (bool) tribe( Telemetry::class )->get_reconciled_telemetry_opt_in(),
			'organizer'         => tribe( 'events.organizer-repository' )->per_page( - 1 )->fields( 'ids' )->first(),
			'venue'             => tribe( 'events.venue-repository' )->per_page( - 1 )->fields( 'ids' )->first(),
			'timezones'         => $tz_choices
		];

		$default_button = get_submit_button(
			'Open Install Wizard (current)',
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $first_boot_data ),
			]
			);

		$null_data          = $first_boot_data;
		$nulling = [
				'activeViews'       => false,
				'defaultCurrency'   => false,
				'defaultDateFormat' => false,
				'defaultTimezone'   => false,
				'defaultWeekStart'  => false,
				'eventTickets'      => false,
				'optin'             => false,
				'organizer'         => false,
				'venue'             => false,
		];
		$null_data = array_merge( $null_data, $nulling );
		$null_button        = get_submit_button(
			'Open Wizard With No Data',
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $null_data ),
			]
		);

		$opted_in_data          = $first_boot_data;
		$opted_in_data['optin'] = true;
		$opted_in_button        = get_submit_button(
			'Open Opted-In Wizard',
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $opted_in_data ),
			]
		);

		$venued_data          = $first_boot_data;
		$venued_data['venue'] = true;
		$venued_button        = get_submit_button(
			'Open Wizard With Venue',
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $venued_data ),
			]
		);

		$organizered_data              = $first_boot_data;
		$organizered_data['organizer'] = true;
		$organizered_button            = get_submit_button(
			'Open Wizard With Organizer',
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $organizered_data ),
			]
		);

		$tickets_data = $first_boot_data;
		$tickets_data['eventTickets'] = true;
		$tickets_button = get_submit_button(
			'Open Wizard With Tickets',
			'secondary tec-events-onboarding-wizard',
			'open',
			true,
			[
				'data-container-element' => 'tec-events-onboarding-wizard-target',
				'data-wizard-boot-data'  => wp_json_encode( $tickets_data ),
			]
		);

		printf(
			'<div class="wrap" id="tec-events-onboarding-wizard-target">%s</div>'
			. $default_button,
			//. $null_button,
			// . $opted_in_button,
			//. $venued_button,
			//. $organizered_button,
			//. $tickets_button,

			esc_html__( 'Loading…', 'tec-events-onboarding-wizard' )
		);
	}

	/**
	 * Enqueue scripts for the onboarding wizard.
	 *
	 * @since   TBD
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
			[],
			$asset['version']
		);
	}

	/**
	 * Get list of timezones. Excludes manual offsets.
	 *
	 * Ruthlessly lifted in part from `wp_timezone_choice()`
	 *
	 * @todo Move this somewhere for reuse!
	 *
	 * @since TBD
	 */
	public function get_timezone_list(): array {
		static $mo_loaded = false, $locale_loaded = null;
		$locale = get_user_locale();

		$continents = [
			'Africa',
			'America',
			'Antarctica',
			'Arctic',
			'Asia',
			'Atlantic',
			'Australia',
			'Europe',
			'Indian',
			'Pacific'
		];

		// Load translations for continents and cities.
		if ( ! $mo_loaded || $locale !== $locale_loaded ) {
			$locale_loaded = $locale ? $locale : get_locale();
			$mofile        = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
			unload_textdomain( 'continents-cities', true );
			load_textdomain( 'continents-cities', $mofile, $locale_loaded );
			$mo_loaded = true;
		}

		$tz_identifiers = timezone_identifiers_list();
		$zonen          = [];

		foreach ( $tz_identifiers as $zone ) {
			$zone = explode( '/', $zone );
			if ( ! in_array( $zone[0], $continents, true ) ) {
				continue;
			}

			// This determines what gets set and translated - we don't translate Etc/* strings here, they are done later.
			$exists    = [
				0 => ( isset( $zone[0] ) && $zone[0] ),
				1 => ( isset( $zone[1] ) && $zone[1] ),
				2 => ( isset( $zone[2] ) && $zone[2] ),
			];
			$exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
			$exists[4] = ( $exists[1] && $exists[3] );
			$exists[5] = ( $exists[2] && $exists[3] );

			// phpcs:disable WordPress.WP.I18n.LowLevelTranslationFunction,WordPress.WP.I18n.NonSingularStringLiteralText
			$zonen[] = [
				'continent'   => ( $exists[0] ? $zone[0] : '' ),
				'city'        => ( $exists[1] ? $zone[1] : '' ),
				'subcity'     => ( $exists[2] ? $zone[2] : '' ),
				't_continent' => ( $exists[3] ? translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
				't_city'      => ( $exists[4] ? translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
				't_subcity'   => ( $exists[5] ? translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' ),
			];
			// phpcs:enable
		}
		usort( $zonen, '_wp_timezone_choice_usort_callback' );

		$zones = [];
		foreach( $continents as $continent ) {
			$zones[$continent] = [];
		}

		foreach ( $zonen as $zone ) {
			// Check if subcity is available (i.e. a state + city)
			if (!empty($zone['t_subcity'])) {
				$city = str_replace( ' ', '_', $zone['t_city']);
				$subcity = str_replace( ' ', '_', $zone['t_subcity']);
				$key = "{$zone['t_continent']}/{$city}/{$subcity}";
				$value = "{$zone['t_city']} - {$zone['t_subcity']}";
			} else {
				// Format without subcity.
				$city = str_replace( ' ', '_', $zone['t_city']);
				$key = "{$zone['t_continent']}/{$city}";
				$value = "{$zone['t_city']}";
			}

			// Format it as a new associative array
			$zones[$zone['t_continent']][$key] = $value;
		}

		return $zones;
	}
}
