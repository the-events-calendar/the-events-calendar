<?php
/**
 * Class that holds some data functions for the Wizard.
 *
 * @since 7.0.0
 */

namespace TEC\Events\Admin\Onboarding;

/**
 * Class Data
 *
 * @since 7.0.0
 * @package TEC\Events\Admin\Onboarding
 */
class Data {
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

	/**
	 * Get a list of countries. Grouped by continent/region.
	 *
	 * @since 7.0.0.
	 *
	 * @return array<string,array<string,string>> The list of countries.
	 */
	public function get_country_list(): array {
		$countries = [
			'Africa'     => [
				'Angola' => 'AO',
				'Burkina Faso' => 'BF',
				'Burundi' => 'BI',
				'Benin' => 'BJ',
				'Botswana' => 'BW',
				'Congo - Kinshasa' => 'CD',
				'Central African Republic' => 'CF',
				'Congo - Brazzaville' => 'CG',
				'Côte d’Ivoire' => 'CI',
				'Cameroon' => 'CM',
				'Djibouti' => 'DJ',
				'Eritrea' => 'ER',
				'Ethiopia' => 'ET',
				'Gabon' => 'GA',
				'Ghana' => 'GH',
				'Gambia' => 'GM',
				'Guinea' => 'GN',
				'Equatorial Guinea' => 'GQ',
				'Guinea-Bissau' => 'GW',
				'Kenya' => 'KE',
				'Comoros' => 'KM',
				'Liberia' => 'LR',
				'Lesotho' => 'LS',
				'Madagascar' => 'MG',
				'Mali' => 'ML',
				'Mauritania' => 'MR',
				'Mauritius' => 'MU',
				'Malawi' => 'MW',
				'Mozambique' => 'MZ',
				'Namibia' => 'NA',
				'Niger' => 'NE',
				'Nigeria' => 'NG',
				'Rwanda' => 'RW',
				'Seychelles' => 'SC',
				'Sudan' => 'SD',
				'Saint Helena' => 'SH',
				'Sierra Leone' => 'SL',
				'Senegal' => 'SN',
				'Somalia' => 'SO',
				'São Tomé and Príncipe' => 'ST',
				'Eswatini (Swaziland)' => 'SZ',
				'Togo' => 'TG',
				'Tanzania' => 'TZ',
				'Uganda' => 'UG',
				'South Africa' => 'ZA',
				'Zambia' => 'ZM',
				'Zimbabwe' => 'ZW',
			],
			'Americas'   => [
				'Antigua and Barbuda' => 'AG',
				'Argentina' => 'AR',
				'Aruba' => 'AW',
				'Barbados' => 'BB',
				'Saint Barthélemy' => 'BL',
				'Bermuda' => 'BM',
				'Bolivia' => 'BO',
				'Brazil' => 'BR',
				'Bahamas' => 'BS',
				'Belize' => 'BZ',
				'Canada' => 'CA',
				'Chile' => 'CL',
				'Colombia' => 'CO',
				'Costa Rica' => 'CR',
				'Cuba' => 'CU',
				'Dominica' => 'DM',
				'Dominican Republic' => 'DO',
				'Ecuador' => 'EC',
				'Grenada' => 'GD',
				'Greenland' => 'GL',
				'Guadeloupe' => 'GP',
				'Guatemala' => 'GT',
				'Guyana' => 'GY',
				'Honduras' => 'HN',
				'Haiti' => 'HT',
				'Jamaica' => 'JM',
				'Saint Kitts and Nevis' => 'KN',
				'Cayman Islands' => 'KY',
				'Saint Lucia' => 'LC',
				'Saint Martin' => 'MF',
				'Mexico' => 'MX',
				'Montserrat' => 'MS',
				'Nicaragua' => 'NI',
				'Panama' => 'PA',
				'Peru' => 'PE',
				'Puerto Rico' => 'PR',
				'Paraguay' => 'PY',
				'Suriname' => 'SR',
				'El Salvador' => 'SV',
				'Sint Maarten' => 'SX',
				'Turks and Caicos Islands' => 'TC',
				'Trinidad and Tobago' => 'TT',
				'United States' => 'US',
				'Uruguay' => 'UY',
				'Saint Vincent and the Grenadines' => 'VC',
				'Venezuela' => 'VE',
				'British Virgin Islands' => 'VG',
				'U.S. Virgin Islands' => 'VI',
				'Falkland Islands' => 'FK',
				'French Guiana' => 'GF',
			],
			'Antarctica' => [
				'Antarctica' => 'AQ',
			],
			'Asia'       => [
				'United Arab Emirates' => 'AE',
				'Afghanistan' => 'AF',
				'Armenia' => 'AM',
				'Azerbaijan' => 'AZ',
				'Bangladesh' => 'BD',
				'Brunei' => 'BN',
				'Bhutan' => 'BT',
				'Cocos [Keeling] Islands' => 'CC',
				'China' => 'CN',
				'Christmas Island' => 'CX',
				'Cyprus' => 'CY',
				'Georgia' => 'GE',
				'Hong Kong' => 'HK',
				'Indonesia' => 'ID',
				'Israel' => 'IL',
				'India' => 'IN',
				'British Indian Ocean Territory' => 'IO',
				'Iraq' => 'IQ',
				'Iran' => 'IR',
				'Jordan' => 'JO',
				'Japan' => 'JP',
				'Kyrgyzstan' => 'KG',
				'Cambodia' => 'KH',
				'North Korea' => 'KP',
				'South Korea' => 'KR',
				'Kuwait' => 'KW',
				'Kazakhstan' => 'KZ',
				'Laos' => 'LA',
				'Sri Lanka' => 'LK',
				'Myanmar [Burma]' => 'MM',
				'Mongolia' => 'MN',
				'Macao' => 'MO',
				'Maldives' => 'MV',
				'Malaysia' => 'MY',
				'Nepal' => 'NP',
				'Oman' => 'OM',
				'Philippines' => 'PH',
				'Pakistan' => 'PK',
				'Palestine' => 'PS',
				'Qatar' => 'QA',
				'Saudi Arabia' => 'SA',
				'Singapore' => 'SG',
				'Syria' => 'SY',
				'Thailand' => 'TH',
				'Tajikistan' => 'TJ',
				'Turkmenistan' => 'TM',
				'Taiwan' => 'TW',
				'Uzbekistan' => 'UZ',
				'Vietnam' => 'VN',
				'Yemen' => 'YE',
			],
			'Oceania'    => [
				'American Samoa' => 'AS',
				'Australia' => 'AU',
				'Cook Islands' => 'CK',
				'Fiji' => 'FJ',
				'Micronesia' => 'FM',
				'Guam' => 'GU',
				'Kiribati' => 'KI',
				'Marshall Islands' => 'MH',
				'Northern Mariana Islands' => 'MP',
				'New Caledonia' => 'NC',
				'Norfolk Island' => 'NF',
				'Nauru' => 'NR',
				'Niue' => 'NU',
				'New Zealand' => 'NZ',
				'French Polynesia' => 'PF',
				'Papua New Guinea' => 'PG',
				'Pitcairn Islands' => 'PN',
				'Palau' => 'PW',
				'Solomon Islands' => 'SB',
				'Tokelau' => 'TK',
				'Tonga' => 'TO',
				'Tuvalu' => 'TV',
				'U.S. Minor Outlying Islands' => 'UM',
				'Vanuatu' => 'VU',
				'Wallis and Futuna' => 'WF',
				'Samoa' => 'WS',
			],
			'Europe'     => [
				'Andorra' => 'AD',
				'Albania' => 'AL',
				'Austria' => 'AT',
				'Åland Islands' => 'AX',
				'Bosnia and Herzegovina' => 'BA',
				'Belgium' => 'BE',
				'Bulgaria' => 'BG',
				'Belarus' => 'BY',
				'Switzerland' => 'CH',
				'Cyprus' => 'CY',
				'Czech Republic' => 'CZ',
				'Germany' => 'DE',
				'Denmark' => 'DK',
				'Estonia' => 'EE',
				'Spain' => 'ES',
				'Finland' => 'FI',
				'Faroe Islands' => 'FO',
				'France' => 'FR',
				'United Kingdom' => 'GB',
				'Guernsey' => 'GG',
				'Gibraltar' => 'GI',
				'Greece' => 'GR',
				'Croatia' => 'HR',
				'Hungary' => 'HU',
				'Ireland' => 'IE',
				'Isle of Man' => 'IM',
				'Iceland' => 'IS',
				'Italy' => 'IT',
				'Jersey' => 'JE',
				'Liechtenstein' => 'LI',
				'Lithuania' => 'LT',
				'Luxembourg' => 'LU',
				'Latvia' => 'LV',
				'Monaco' => 'MC',
				'Moldova' => 'MD',
				'Montenegro' => 'ME',
				'North Macedonia' => 'MK',
				'Malta' => 'MT',
				'Netherlands' => 'NL',
				'Norway' => 'NO',
				'Poland' => 'PL',
				'Portugal' => 'PT',
				'Romania' => 'RO',
				'Serbia' => 'RS',
				'Russia' => 'RU',
				'Sweden' => 'SE',
				'Slovenia' => 'SI',
				'Svalbard and Jan Mayen' => 'SJ',
				'Slovakia' => 'SK',
				'San Marino' => 'SM',
				'Ukraine' => 'UA',
				'Vatican City' => 'VA',
			],
		];

		/**
		 * Filter the list of countries.
		 *
		 * @since 7.0.0
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
	 * @since 7.0.0
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
	 * @since 7.0.0
	 *
	 * @return array
	 */
	public function get_currency_list(): array {
		$default_currencies = [
			'aud'     => [
				'code'   => 'AUD',
				'name'   => __( 'Australian Dollar', 'the-events-calendar' ),
				'$' => 'symbol',
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
}
