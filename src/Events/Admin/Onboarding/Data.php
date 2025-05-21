<?php
/**
 * Class that holds some data functions for the Wizard.
 *
 * @since 6.8.4
 */

namespace TEC\Events\Admin\Onboarding;

use Tribe\Events\Views\V2\Manager as Views_Manager;
use TEC\Common\Lists\Currency;
use TEC\Common\Lists\Country;
/**
 * Class Data
 *
 * @since 6.8.4
 * @package TEC\Events\Admin\Onboarding
 */
class Data {
	/**
	 * Get the organizer data.
	 * Looks for a single existing organizer and returns the data.
	 *
	 * @since 6.8.4
	 *
	 * @return array<string,string> The organizer data.
	 */
	public function get_organizer_data(): array {
		$organizer_id = tribe( 'events.organizer-repository' )->fields( 'ids' )->first();

		if ( empty( $organizer_id ) ) {
			return [];
		}

		return [
			'organizerId' => $organizer_id,
			'name'        => get_the_title( $organizer_id ),
			'email'       => get_post_meta( $organizer_id, '_OrganizerEmail', true ),
			'phone'       => get_post_meta( $organizer_id, '_OrganizerPhone', true ),
			'website'     => get_post_meta( $organizer_id, '_OrganizerWebsite', true ),
		];
	}

	/**
	 * Get the venue data.
	 * Looks for a single existing venue and returns the data.
	 *
	 * @since 6.8.4
	 *
	 * @return array<string,string> The venue data.
	 */
	public function get_venue_data(): array {
		$venue_id = tribe( 'events.venue-repository' )->fields( 'ids' )->first();

		if ( empty( $venue_id ) ) {
			return [];
		}

		return [
			'venueId' => $venue_id,
			'name'    => get_the_title( $venue_id ),
			'address' => get_post_meta( $venue_id, '_VenueAddress', true ),
			'city'    => get_post_meta( $venue_id, '_VenueCity', true ),
			'country' => tribe( Country::class )->find_country_by_value( get_post_meta( $venue_id, '_VenueCountry', true ) ),
			'phone'   => get_post_meta( $venue_id, '_VenuePhone', true ),
			'state'   => get_post_meta( $venue_id, '_VenueState', true ),
			'website' => get_post_meta( $venue_id, '_VenueWebsite', true ),
			'zip'     => get_post_meta( $venue_id, '_VenueZip', true ),
		];
	}

	/**
	 * Check if there are any events.
	 *
	 * @since 6.8.4
	 *
	 * @return bool
	 */
	public function has_events() {
		$events = tribe_events()->fields( 'ids' )->first();

		return ! empty( $events );
	}

	/**
	 * Get the available views.
	 *
	 * @since 6.8.4
	 *
	 * @return array<string> The available views.
	 */
	public function get_available_views(): array {
		$view_manager    = tribe( Views_Manager::class );
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
			'widget-events-qr-code',
		];

		$cleaned_views = array_flip( array_diff_key( array_flip( $available_views ), array_flip( $remove ) ) );

		return array_values( $cleaned_views );
	}

	/**
	 * Get a list of countries. Grouped by continent/region.
	 *
	 * @since 6.8.4.
	 *
	 * @return array<string,array<string,string>> The list of countries.
	 */
	public function get_country_list(): array {
		$countries = tribe( Country::class )->get_country_list();

		/**
		 * Filter the list of countries.
		 *
		 * @since 6.8.4
		 *
		 * @param array $countries The list of countries. Grouped by continent/region.
		 */
		return apply_filters( 'tec_events_onboarding_wizard_country_list', $countries );
	}

	/**
	 * Get list of timezones. Excludes manual offsets.
	 *
	 * Ruthlessly lifted in part from `wp_timezone_choice()`
	 *
	 * @todo Move this somewhere for reuse!
	 *
	 * @since 6.8.4
	 *
	 * @return array<string,string> The list of timezones.
	 */
	public function get_timezone_list(): array {
		// phpcs:disable
		static $mo_loaded = false, $locale_loaded = null;
		$locale           = get_user_locale();
		$continents       = [
			'Africa',
			'America',
			'Antarctica',
			'Arctic',
			'Asia',
			'Atlantic',
			'Australia',
			'Europe',
			'Indian',
			'Pacific',
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
			// Sections: Continent/City/Subcity.
			$sections = substr_count( $zone, '/' ) + 1;
			$zone     = explode( '/', $zone );

			if ( ! in_array( $zone[0], $continents ) ) {
				continue;
			}

			// Skip UTC offsets.
			if ( $sections <= 1 ) {
				continue;
			}

			$assemble = [];

			if ( $sections > 0 ) {
				$assemble['continent'] = translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' );
			}

			if ( $sections > 1 ) {
				$assemble['city'] = translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' );
			}

			if ( $sections > 2 ) {
				$assemble['subcity'] = translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' );
			}

			if ( empty( $assemble ) ) {
				continue;
			}

			$zonen[] = $assemble;
			// phpcs:enable
		}

		$zones = [];
		foreach ( $continents as $continent ) {
			$zones[ $continent ] = [];
		}

		foreach ( $zonen as $zone ) {
			// Check if subcity is available (i.e. a state + city).
			if ( ! empty( $zone['subcity'] ) ) {
				$city    = str_replace( ' ', '_', $zone['city'] );
				$subcity = str_replace( ' ', '_', $zone['subcity'] );
				$key     = "{$zone['continent']}/{$city}/{$subcity}";
				$value   = "{$zone['city']} - {$zone['subcity']}";
			} else {
				// Format without subcity.
				$city  = str_replace( ' ', '_', $zone['city'] );
				$key   = "{$zone['continent']}/{$city}";
				$value = "{$zone['city']}";
			}

			// Format it as a new associative array.
			$zones[ $zone['continent'] ][ $key ] = $value;
		}

		$zones = array_filter( $zones );

		return apply_filters( 'tec_events_onboarding_wizard_timezone_list', $zones );
	}

	/**
	 * Get a list of currencies.
	 * Note: we don't currently use "code" or "entity", but they are included for future use.
	 *
	 * @since 6.8.4
	 *
	 * @return array
	 */
	public function get_currency_list(): array {
		$default_currencies = tribe( Currency::class )->get_currency_list();

		return (array) apply_filters( 'tec_events_onboarding_wizard_currencies_list', $default_currencies );
	}

	/**
	 * Get the saved wizard settings.
	 *
	 * @since 6.8.4
	 *
	 * @return array
	 */
	public function get_wizard_settings() {
		return get_option( 'tec_onboarding_wizard_data', [] );
	}

	/**
	 * Update the wizard settings.
	 *
	 * @since 6.8.4
	 *
	 * @param array $settings The settings to update.
	 */
	public function update_wizard_settings( $settings ): bool {
		return update_option( 'tec_onboarding_wizard_data', $settings );
	}

	/**
	 * Get a specific wizard setting by key.
	 *
	 * @since 6.8.4
	 *
	 * @param string $key           The setting key.
	 * @param mixed  $default_value The default value.
	 *
	 * @return mixed
	 */
	public function get_wizard_setting( $key, $default_value = null ) {
		$settings = $this->get_wizard_settings();

		return $settings[ $key ] ?? $default_value;
	}

	/**
	 * Update a specific wizard setting.
	 *
	 * @since 6.8.4
	 *
	 * @param string $key   The setting key.
	 * @param mixed  $value The setting value.
	 */
	public function update_wizard_setting( $key, $value ) {
		$settings         = $this->get_wizard_settings();
		$settings[ $key ] = $value;

		$this->update_wizard_settings( $settings );
	}
}
