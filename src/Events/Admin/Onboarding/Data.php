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
	 * Get a list of countries. Grouped by continent/region.
	 *
	 * @since 7.0.0.
	 *
	 * @return array<string,array<string,string>> The list of countries.
	 */
	public static function get_country_list(): array {
		$countries = [
			'Africa'     => [
				'AO' => 'Angola',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'BJ' => 'Benin',
				'BW' => 'Botswana',
				'CD' => 'Congo - Kinshasa',
				'CF' => 'Central African Republic',
				'CG' => 'Congo - Brazzaville',
				'CI' => 'Côte d’Ivoire',
				'CM' => 'Cameroon',
				'DJ' => 'Djibouti',
				'ER' => 'Eritrea',
				'ET' => 'Ethiopia',
				'GA' => 'Gabon',
				'GH' => 'Ghana',
				'GM' => 'Gambia',
				'GN' => 'Guinea',
				'GQ' => 'Equatorial Guinea',
				'GW' => 'Guinea-Bissau',
				'KE' => 'Kenya',
				'KM' => 'Comoros',
				'LR' => 'Liberia',
				'LS' => 'Lesotho',
				'MG' => 'Madagascar',
				'ML' => 'Mali',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'MW' => 'Malawi',
				'MZ' => 'Mozambique',
				'NA' => 'Namibia',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'RW' => 'Rwanda',
				'SC' => 'Seychelles',
				'SD' => 'Sudan',
				'SH' => 'Saint Helena',
				'SL' => 'Sierra Leone',
				'SN' => 'Senegal',
				'SO' => 'Somalia',
				'ST' => 'São Tomé and Príncipe',
				'SZ' => 'Eswatini (Swaziland)',
				'TG' => 'Togo',
				'TZ' => 'Tanzania',
				'UG' => 'Uganda',
				'ZA' => 'South Africa',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe',
			],
			'Americas'   => [
				'AG' => 'Antigua and Barbuda',
				'AR' => 'Argentina',
				'AW' => 'Aruba',
				'BB' => 'Barbados',
				'BL' => 'Saint Barthélemy',
				'BM' => 'Bermuda',
				'BO' => 'Bolivia',
				'BR' => 'Brazil',
				'BS' => 'Bahamas',
				'BZ' => 'Belize',
				'CA' => 'Canada',
				'CL' => 'Chile',
				'CO' => 'Colombia',
				'CR' => 'Costa Rica',
				'CU' => 'Cuba',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'EC' => 'Ecuador',
				'GD' => 'Grenada',
				'GL' => 'Greenland',
				'GP' => 'Guadeloupe',
				'GT' => 'Guatemala',
				'GY' => 'Guyana',
				'HN' => 'Honduras',
				'HT' => 'Haiti',
				'JM' => 'Jamaica',
				'KN' => 'Saint Kitts and Nevis',
				'KY' => 'Cayman Islands',
				'LC' => 'Saint Lucia',
				'MF' => 'Saint Martin',
				'MX' => 'Mexico',
				'MS' => 'Montserrat',
				'NI' => 'Nicaragua',
				'PA' => 'Panama',
				'PE' => 'Peru',
				'PR' => 'Puerto Rico',
				'PY' => 'Paraguay',
				'SR' => 'Suriname',
				'SV' => 'El Salvador',
				'SX' => 'Sint Maarten',
				'TC' => 'Turks and Caicos Islands',
				'TT' => 'Trinidad and Tobago',
				'US' => 'United States',
				'UY' => 'Uruguay',
				'VC' => 'Saint Vincent and the Grenadines',
				'VE' => 'Venezuela',
				'VG' => 'British Virgin Islands',
				'VI' => 'U.S. Virgin Islands',
				'FK' => 'Falkland Islands',
				'GF' => 'French Guiana',
			],
			'Antarctica' => [
				'AQ' => 'Antarctica',
			],
			'Asia'       => [
				'AE' => 'United Arab Emirates',
				'AF' => 'Afghanistan',
				'AM' => 'Armenia',
				'AZ' => 'Azerbaijan',
				'BD' => 'Bangladesh',
				'BN' => 'Brunei',
				'BT' => 'Bhutan',
				'CC' => 'Cocos [Keeling] Islands',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CY' => 'Cyprus',
				'GE' => 'Georgia',
				'HK' => 'Hong Kong',
				'ID' => 'Indonesia',
				'IL' => 'Israel',
				'IN' => 'India',
				'IO' => 'British Indian Ocean Territory',
				'IQ' => 'Iraq',
				'IR' => 'Iran',
				'JO' => 'Jordan',
				'JP' => 'Japan',
				'KG' => 'Kyrgyzstan',
				'KH' => 'Cambodia',
				'KP' => 'North Korea',
				'KR' => 'South Korea',
				'KW' => 'Kuwait',
				'KZ' => 'Kazakhstan',
				'LA' => 'Laos',
				'LK' => 'Sri Lanka',
				'MM' => 'Myanmar [Burma]',
				'MN' => 'Mongolia',
				'MO' => 'Macao',
				'MV' => 'Maldives',
				'MY' => 'Malaysia',
				'NP' => 'Nepal',
				'OM' => 'Oman',
				'PH' => 'Philippines',
				'PK' => 'Pakistan',
				'PS' => 'Palestine',
				'QA' => 'Qatar',
				'SA' => 'Saudi Arabia',
				'SG' => 'Singapore',
				'SY' => 'Syria',
				'TH' => 'Thailand',
				'TJ' => 'Tajikistan',
				'TM' => 'Turkmenistan',
				'TW' => 'Taiwan',
				'UZ' => 'Uzbekistan',
				'VN' => 'Vietnam',
				'YE' => 'Yemen',
			],
			'Oceania'    => [
				'AS' => 'American Samoa',
				'AU' => 'Australia',
				'CK' => 'Cook Islands',
				'FJ' => 'Fiji',
				'FM' => 'Micronesia',
				'GU' => 'Guam',
				'KI' => 'Kiribati',
				'MH' => 'Marshall Islands',
				'MP' => 'Northern Mariana Islands',
				'NC' => 'New Caledonia',
				'NF' => 'Norfolk Island',
				'NR' => 'Nauru',
				'NU' => 'Niue',
				'NZ' => 'New Zealand',
				'PF' => 'French Polynesia',
				'PG' => 'Papua New Guinea',
				'PN' => 'Pitcairn Islands',
				'PW' => 'Palau',
				'SB' => 'Solomon Islands',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TV' => 'Tuvalu',
				'UM' => 'U.S. Minor Outlying Islands',
				'VU' => 'Vanuatu',
				'WF' => 'Wallis and Futuna',
				'WS' => 'Samoa',
			],
			'Europe'     => [
				'AD' => 'Andorra',
				'AL' => 'Albania',
				'AT' => 'Austria',
				'AX' => 'Åland Islands',
				'BA' => 'Bosnia and Herzegovina',
				'BE' => 'Belgium',
				'BG' => 'Bulgaria',
				'BY' => 'Belarus',
				'CH' => 'Switzerland',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DE' => 'Germany',
				'DK' => 'Denmark',
				'EE' => 'Estonia',
				'ES' => 'Spain',
				'FI' => 'Finland',
				'FO' => 'Faroe Islands',
				'FR' => 'France',
				'GB' => 'United Kingdom',
				'GG' => 'Guernsey',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'HR' => 'Croatia',
				'HU' => 'Hungary',
				'IE' => 'Ireland',
				'IM' => 'Isle of Man',
				'IS' => 'Iceland',
				'IT' => 'Italy',
				'JE' => 'Jersey',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'LV' => 'Latvia',
				'MC' => 'Monaco',
				'MD' => 'Moldova',
				'ME' => 'Montenegro',
				'MK' => 'North Macedonia',
				'MT' => 'Malta',
				'NL' => 'Netherlands',
				'NO' => 'Norway',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'RO' => 'Romania',
				'RS' => 'Serbia',
				'RU' => 'Russia',
				'SE' => 'Sweden',
				'SI' => 'Slovenia',
				'SJ' => 'Svalbard and Jan Mayen',
				'SK' => 'Slovakia',
				'SM' => 'San Marino',
				'UA' => 'Ukraine',
				'VA' => 'Vatican City',
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
	public static function get_timezone_list(): array {
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
	public static function get_currency_list(): array {
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
}
