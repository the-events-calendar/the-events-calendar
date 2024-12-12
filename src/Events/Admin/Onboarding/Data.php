<?php
/**
 * Class that holds some data functions for the Wizard.
 *
 * @since 6.8.4
 */

namespace TEC\Events\Admin\Onboarding;

use Tribe\Events\Views\V2\Manager as Views_Manager;

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
			'country' => $this->find_country_by_value( get_post_meta( $venue_id, '_VenueCountry', true ) ),
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
		$countries = [
			'Africa'     => [
				'AO' => 'Angola',
				'BJ' => 'Benin',
				'BW' => 'Botswana',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'CM' => 'Cameroon',
				'CF' => 'Central African Republic',
				'KM' => 'Comoros',
				'CG' => 'Congo - Brazzaville',
				'CD' => 'Congo - Kinshasa',
				'CI' => 'Côte d’Ivoire',
				'DJ' => 'Djibouti',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'SZ' => 'Eswatini (Swaziland)',
				'ET' => 'Ethiopia',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GH' => 'Ghana',
				'GW' => 'Guinea-Bissau',
				'GN' => 'Guinea',
				'KE' => 'Kenya',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'ML' => 'Mali',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'MZ' => 'Mozambique',
				'NA' => 'Namibia',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'RW' => 'Rwanda',
				'SH' => 'Saint Helena',
				'ST' => 'São Tomé and Príncipe',
				'SN' => 'Senegal',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'SD' => 'Sudan',
				'TZ' => 'Tanzania',
				'TG' => 'Togo',
				'UG' => 'Uganda',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe',
			],
			'Americas'   => [
				'AG' => 'Antigua and Barbuda',
				'AR' => 'Argentina',
				'AW' => 'Aruba',
				'BS' => 'Bahamas',
				'BB' => 'Barbados',
				'BZ' => 'Belize',
				'BM' => 'Bermuda',
				'BO' => 'Bolivia',
				'BR' => 'Brazil',
				'VG' => 'British Virgin Islands',
				'CA' => 'Canada',
				'KY' => 'Cayman Islands',
				'CL' => 'Chile',
				'CO' => 'Colombia',
				'CR' => 'Costa Rica',
				'CU' => 'Cuba',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'EC' => 'Ecuador',
				'SV' => 'El Salvador',
				'FK' => 'Falkland Islands',
				'GF' => 'French Guiana',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GP' => 'Guadeloupe',
				'GT' => 'Guatemala',
				'GY' => 'Guyana',
				'HT' => 'Haiti',
				'HN' => 'Honduras',
				'JM' => 'Jamaica',
				'MX' => 'Mexico',
				'MS' => 'Montserrat',
				'NI' => 'Nicaragua',
				'PA' => 'Panama',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'PR' => 'Puerto Rico',
				'BL' => 'Saint Barthélemy',
				'KN' => 'Saint Kitts and Nevis',
				'LC' => 'Saint Lucia',
				'MF' => 'Saint Martin',
				'VC' => 'Saint Vincent and the Grenadines',
				'SX' => 'Sint Maarten',
				'SR' => 'Suriname',
				'TT' => 'Trinidad and Tobago',
				'TC' => 'Turks and Caicos Islands',
				'VI' => 'U.S. Virgin Islands',
				'US' => 'United States',
				'UY' => 'Uruguay',
				'VE' => 'Venezuela',
			],
			'Antarctica' => [
				'AQ' => 'Antarctica',
			],
			'Asia'       => [
				'AF' => 'Afghanistan',
				'AM' => 'Armenia',
				'AZ' => 'Azerbaijan',
				'BD' => 'Bangladesh',
				'BT' => 'Bhutan',
				'IO' => 'British Indian Ocean Territory',
				'BN' => 'Brunei',
				'KH' => 'Cambodia',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos [Keeling] Islands',
				'CY' => 'Cyprus',
				'GE' => 'Georgia',
				'HK' => 'Hong Kong',
				'IN' => 'India',
				'ID' => 'Indonesia',
				'IR' => 'Iran',
				'IQ' => 'Iraq',
				'IL' => 'Israel',
				'JP' => 'Japan',
				'JO' => 'Jordan',
				'KZ' => 'Kazakhstan',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => 'Laos',
				'MO' => 'Macao',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'MN' => 'Mongolia',
				'MM' => 'Myanmar [Burma]',
				'NP' => 'Nepal',
				'KP' => 'North Korea',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PS' => 'Palestine',
				'PH' => 'Philippines',
				'QA' => 'Qatar',
				'SA' => 'Saudi Arabia',
				'SG' => 'Singapore',
				'KR' => 'South Korea',
				'LK' => 'Sri Lanka',
				'SY' => 'Syria',
				'TW' => 'Taiwan',
				'TJ' => 'Tajikistan',
				'TH' => 'Thailand',
				'TM' => 'Turkmenistan',
				'AE' => 'United Arab Emirates',
				'UZ' => 'Uzbekistan',
				'VN' => 'Vietnam',
				'YE' => 'Yemen',
			],
			'Oceania'    => [
				'AS' => 'American Samoa',
				'AU' => 'Australia',
				'CK' => 'Cook Islands',
				'FJ' => 'Fiji',
				'PF' => 'French Polynesia',
				'GU' => 'Guam',
				'KI' => 'Kiribati',
				'MH' => 'Marshall Islands',
				'FM' => 'Micronesia',
				'NR' => 'Nauru',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NU' => 'Niue',
				'NF' => 'Norfolk Island',
				'MP' => 'Northern Mariana Islands',
				'PW' => 'Palau',
				'PG' => 'Papua New Guinea',
				'PN' => 'Pitcairn Islands',
				'WS' => 'Samoa',
				'SB' => 'Solomon Islands',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TV' => 'Tuvalu',
				'UM' => 'U.S. Minor Outlying Islands',
				'VU' => 'Vanuatu',
				'WF' => 'Wallis and Futuna',
			],
			'Europe'     => [
				'AX' => 'Åland Islands',
				'AL' => 'Albania',
				'AD' => 'Andorra',
				'AT' => 'Austria',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BA' => 'Bosnia and Herzegovina',
				'BG' => 'Bulgaria',
				'HR' => 'Croatia',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DK' => 'Denmark',
				'EE' => 'Estonia',
				'FO' => 'Faroe Islands',
				'FI' => 'Finland',
				'FR' => 'France',
				'DE' => 'Germany',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'GG' => 'Guernsey',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IE' => 'Ireland',
				'IM' => 'Isle of Man',
				'IT' => 'Italy',
				'JE' => 'Jersey',
				'LV' => 'Latvia',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MT' => 'Malta',
				'MD' => 'Moldova',
				'MC' => 'Monaco',
				'ME' => 'Montenegro',
				'NL' => 'Netherlands',
				'MK' => 'North Macedonia',
				'NO' => 'Norway',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'RO' => 'Romania',
				'RU' => 'Russia',
				'SM' => 'San Marino',
				'RS' => 'Serbia',
				'SK' => 'Slovakia',
				'SI' => 'Slovenia',
				'ES' => 'Spain',
				'SJ' => 'Svalbard and Jan Mayen',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'UA' => 'Ukraine',
				'GB' => 'United Kingdom',
				'VA' => 'Vatican City',
			],
		];

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
	 * Find a country by its key.
	 *
	 * @since 6.8.4
	 *
	 * @param string $key The country key.
	 *
	 * @return string|null The country name or null if not found.
	 */
	public function find_country_by_key( $key ): ?string {
		if ( empty( $key ) ) {
			return null;
		}

		$countries = $this->get_country_list();
		// Use array_filter to locate the array containing the key.
		$filtered = array_filter( $countries, fn( $country_list ) => array_key_exists( $key, $country_list ) );

		// If the filtered array is not empty, fetch the value.
		if ( ! empty( $filtered ) ) {
			$continent = reset( $filtered ); // Get the first match.
			return $continent[ $key ];
		}
		return null;
	}

	/**
	 * Find a country key by its value.
	 *
	 * @since 6.8.4
	 *
	 * @param string $value The country value.
	 *
	 * @return string|null The country key or null if not found.
	 */
	public function find_country_by_value( $value ): ?string {
		if ( empty( $value ) ) {
			return null;
		}

		$countries = $this->get_country_list();
		// Use array_filter to locate the array containing the key.
		$filtered = array_filter( $countries, fn( $country_list ) => in_array( $value, $country_list ) );

		// If the filtered array is not empty, fetch the value.
		if ( ! empty( $filtered ) ) {
			$continent = reset( $filtered ); // Get the first match.
			return array_search( $value, $continent );
		}

		return null;
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
		$default_currencies = [
			'aud'     => [
				'code'   => 'AUD',
				'name'   => __( 'Australian Dollar', 'the-events-calendar' ),
				'symbol' => '$',
				'entity' => '&#36;',
			],
			'brl'     => [
				'code'   => 'BRL',
				'name'   => __( 'Brazilian Real', 'the-events-calendar' ),
				'symbol' => 'R$',
				'entity' => 'R&#36;',
			],
			'gbp'     => [
				'code'   => 'GBP',
				'name'   => __( 'British Pound', 'the-events-calendar' ),
				'symbol' => '£',
				'entity' => '&pound;',
			],
			'cad'     => [
				'code'   => 'CAD',
				'name'   => __( 'Canadian Dollar', 'the-events-calendar' ),
				'symbol' => '$',
				'entity' => '&#36;',
			],
			'cny'     => [
				'code'   => 'CNY',
				'name'   => __( 'Chinese Yen (¥)', 'the-events-calendar' ),
				'symbol' => '¥',
				'entity' => '&yen;',
			],
			'cny2'    => [
				'code'   => 'CNY',
				'name'   => __( 'Chinese Yuan (元)', 'the-events-calendar' ),
				'symbol' => '元',
				'entity' => '&#20803;',
			],
			'czk'     => [
				'code'   => 'CZK',
				'name'   => __( 'Czech Koruna', 'the-events-calendar' ),
				'symbol' => 'Kč',
				'entity' => 'K&#x10D;',
			],
			'dkk'     => [
				'code'   => 'DKK',
				'name'   => __( 'Danish Krone', 'the-events-calendar' ),
				'symbol' => 'kr.',
				'entity' => 'kr.',
			],
			'euro'    => [
				'code'   => 'EUR',
				'name'   => __( 'Euro', 'the-events-calendar' ),
				'symbol' => '€',
				'entity' => '&euro;',
			],
			'hkd'     => [
				'code'   => 'HKD',
				'name'   => __( 'Hong Kong Dollar', 'the-events-calendar' ),
				'symbol' => '$',
				'entity' => '&#36;',
			],
			'huf'     => [
				'code'   => 'HUF',
				'name'   => __( 'Hungarian Forint', 'the-events-calendar' ),
				'symbol' => 'Ft',
				'entity' => 'Ft',
			],
			'inr'     => [
				'code'   => 'INR',
				'name'   => __( 'Indian Rupee', 'the-events-calendar' ),
				'symbol' => '₹',
				'entity' => '&#x20B9;',
			],
			'idr'     => [
				'code'   => 'IDR',
				'name'   => __( 'Indonesian Rupiah', 'the-events-calendar' ),
				'symbol' => 'Rp',
				'entity' => 'Rp',
			],
			'ils'     => [
				'code'   => 'ILS',
				'name'   => __( 'Israeli New Sheqel', 'the-events-calendar' ),
				'symbol' => '₪',
				'entity' => '&#x20AA;',
			],
			'jpy'     => [
				'code'   => 'JPY',
				'name'   => __( 'Japanese Yen', 'the-events-calendar' ),
				'symbol' => '¥',
				'entity' => '&yen;',
			],
			'krw'     => [
				'code'   => 'KRW',
				'name'   => __( 'Korean Won', 'the-events-calendar' ),
				'symbol' => '₩',
				'entity' => '&#8361;',
			],
			'myr'     => [
				'code'   => 'MYR',
				'name'   => __( 'Malaysian Ringgit', 'the-events-calendar' ),
				'symbol' => 'RM',
				'entity' => 'RM',
			],
			'mxn'     => [
				'code'   => 'MXN',
				'name'   => __( 'Mexican Peso', 'the-events-calendar' ),
				'symbol' => '$',
				'entity' => '&#36;',
			],
			'ngn'     => [
				'code'   => 'NGN',
				'name'   => __( 'Nigerian Naira', 'the-events-calendar' ),
				'symbol' => '₦',
				'entity' => '&#8358;',
			],
			'nzd'     => [
				'code'   => 'NZD',
				'name'   => __( 'New Zealand Dollar', 'the-events-calendar' ),
				'symbol' => '$',
				'entity' => '&#36;',
			],
			'nok'     => [
				'code'   => 'NOK',
				'name'   => __( 'Norwegian Krone', 'the-events-calendar' ),
				'symbol' => 'kr',
				'entity' => 'kr',
			],
			'php'     => [
				'code'   => 'PHP',
				'name'   => __( 'Philippine Peso', 'the-events-calendar' ),
				'symbol' => '₱',
				'entity' => '&#x20B1;',
			],
			'pln'     => [
				'code'   => 'PLN',
				'name'   => __( 'Polish Złoty', 'the-events-calendar' ),
				'symbol' => 'zł',
				'entity' => 'z&#x142;',
			],
			'rub'     => [
				'code'   => 'RUB',
				'name'   => __( 'Russian Ruble', 'the-events-calendar' ),
				'symbol' => '₽',
				'entity' => '&#8381;',
			],
			'sek'     => [
				'code'   => 'SEK',
				'name'   => __( 'Swedish Krona', 'the-events-calendar' ),
				'symbol' => 'kr',
				'entity' => 'kr',
			],
			'sgd'     => [
				'code'   => 'SGD',
				'name'   => __( 'Singapore Dollar', 'the-events-calendar' ),
				'symbol' => '$',
				'entity' => '&#36;',
			],
			'zar'     => [
				'code'   => 'ZAR',
				'name'   => __( 'South African Rand', 'the-events-calendar' ),
				'symbol' => 'R',
				'entity' => 'R',
			],
			'chf'     => [
				'code'   => 'CHF',
				'name'   => __( 'Swiss Franc', 'the-events-calendar' ),
				'symbol' => 'Fr',
				'entity' => 'Fr',
			],
			'twd'     => [
				'code'   => 'TWD',
				'name'   => __( 'Taiwan New Dollar', 'the-events-calendar' ),
				'symbol' => '$',
				'entity' => '&#36;',
			],
			'thb'     => [
				'code'   => 'THB',
				'name'   => __( 'Thai Baht', 'the-events-calendar' ),
				'symbol' => '฿',
				'entity' => '&#x0E3F;',
			],
			'trl'     => [
				'code'   => 'TRL',
				'name'   => __( 'Turkish Lira', 'the-events-calendar' ),
				'symbol' => '₺',
				'entity' => '&#8378;',
			],
			'usd'     => [
				'code'   => 'USD',
				'name'   => __( 'US Dollar', 'the-events-calendar' ),
				'symbol' => '$',
				'entity' => '&#36;',
			],
			'usdcent' => [
				'code'   => 'USDCENT',
				'name'   => __( 'US Cent', 'the-events-calendar' ),
				'symbol' => '¢',
				'entity' => '&cent;',
			],
			'vnd'     => [
				'code'   => 'VND',
				'name'   => __( 'Vietnamese Dong', 'the-events-calendar' ),
				'symbol' => '₫',
				'entity' => '&#8363;',
			],
		];

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
