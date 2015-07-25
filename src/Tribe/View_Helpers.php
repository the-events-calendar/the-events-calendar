<?php
/**
 * Various helper methods used in views
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__View_Helpers' ) ) {
	class Tribe__Events__View_Helpers {

		/**
		 * Get the countries being used and available for the plugin.
		 *
		 * @param string $postId     The post ID.
		 * @param bool   $useDefault Should we use the defaults?
		 *
		 * @return array The countries array.
		 */
		public static function constructCountries( $postId = '', $useDefault = true ) {

			if ( tribe_get_option( 'tribeEventsCountries' ) != '' ) {
				$countries = array(
					'' => esc_html__( 'Select a Country:', 'tribe-events-calendar' ),
				);

				$country_rows = explode( "\n", tribe_get_option( 'tribeEventsCountries' ) );
				foreach ( $country_rows as $crow ) {
					$country = explode( ',', $crow );
					if ( isset( $country[0] ) && isset( $country[1] ) ) {
						$country[0] = trim( $country[0] );
						$country[1] = trim( $country[1] );

						if ( $country[0] && $country[1] ) {
							$countries[ $country[0] ] = $country[1];
						}
					}
				}
			}

			if ( ! isset( $countries ) || ! is_array( $countries ) || count( $countries ) == 1 ) {
				$countries = array(
					''   => esc_html__( 'Select a Country:', 'tribe-events-calendar' ),
					'US' => esc_html__( 'United States', 'tribe-events-calendar' ),
					'AF' => esc_html__( 'Afghanistan', 'tribe-events-calendar' ),
					'AL' => esc_html__( 'Albania', 'tribe-events-calendar' ),
					'DZ' => esc_html__( 'Algeria', 'tribe-events-calendar' ),
					'AS' => esc_html__( 'American Samoa', 'tribe-events-calendar' ),
					'AD' => esc_html__( 'Andorra', 'tribe-events-calendar' ),
					'AO' => esc_html__( 'Angola', 'tribe-events-calendar' ),
					'AI' => esc_html__( 'Anguilla', 'tribe-events-calendar' ),
					'AQ' => esc_html__( 'Antarctica', 'tribe-events-calendar' ),
					'AG' => esc_html__( 'Antigua And Barbuda', 'tribe-events-calendar' ),
					'AR' => esc_html__( 'Argentina', 'tribe-events-calendar' ),
					'AM' => esc_html__( 'Armenia', 'tribe-events-calendar' ),
					'AW' => esc_html__( 'Aruba', 'tribe-events-calendar' ),
					'AU' => esc_html__( 'Australia', 'tribe-events-calendar' ),
					'AT' => esc_html__( 'Austria', 'tribe-events-calendar' ),
					'AZ' => esc_html__( 'Azerbaijan', 'tribe-events-calendar' ),
					'BS' => esc_html__( 'Bahamas', 'tribe-events-calendar' ),
					'BH' => esc_html__( 'Bahrain', 'tribe-events-calendar' ),
					'BD' => esc_html__( 'Bangladesh', 'tribe-events-calendar' ),
					'BB' => esc_html__( 'Barbados', 'tribe-events-calendar' ),
					'BY' => esc_html__( 'Belarus', 'tribe-events-calendar' ),
					'BE' => esc_html__( 'Belgium', 'tribe-events-calendar' ),
					'BZ' => esc_html__( 'Belize', 'tribe-events-calendar' ),
					'BJ' => esc_html__( 'Benin', 'tribe-events-calendar' ),
					'BM' => esc_html__( 'Bermuda', 'tribe-events-calendar' ),
					'BT' => esc_html__( 'Bhutan', 'tribe-events-calendar' ),
					'BO' => esc_html__( 'Bolivia', 'tribe-events-calendar' ),
					'BA' => esc_html__( 'Bosnia And Herzegowina', 'tribe-events-calendar' ),
					'BW' => esc_html__( 'Botswana', 'tribe-events-calendar' ),
					'BV' => esc_html__( 'Bouvet Island', 'tribe-events-calendar' ),
					'BR' => esc_html__( 'Brazil', 'tribe-events-calendar' ),
					'IO' => esc_html__( 'British Indian Ocean Territory', 'tribe-events-calendar' ),
					'BN' => esc_html__( 'Brunei Darussalam', 'tribe-events-calendar' ),
					'BG' => esc_html__( 'Bulgaria', 'tribe-events-calendar' ),
					'BF' => esc_html__( 'Burkina Faso', 'tribe-events-calendar' ),
					'BI' => esc_html__( 'Burundi', 'tribe-events-calendar' ),
					'KH' => esc_html__( 'Cambodia', 'tribe-events-calendar' ),
					'CM' => esc_html__( 'Cameroon', 'tribe-events-calendar' ),
					'CA' => esc_html__( 'Canada', 'tribe-events-calendar' ),
					'CV' => esc_html__( 'Cape Verde', 'tribe-events-calendar' ),
					'KY' => esc_html__( 'Cayman Islands', 'tribe-events-calendar' ),
					'CF' => esc_html__( 'Central African Republic', 'tribe-events-calendar' ),
					'TD' => esc_html__( 'Chad', 'tribe-events-calendar' ),
					'CL' => esc_html__( 'Chile', 'tribe-events-calendar' ),
					'CN' => esc_html__( 'China', 'tribe-events-calendar' ),
					'CX' => esc_html__( 'Christmas Island', 'tribe-events-calendar' ),
					'CC' => esc_html__( 'Cocos (Keeling) Islands', 'tribe-events-calendar' ),
					'CO' => esc_html__( 'Colombia', 'tribe-events-calendar' ),
					'KM' => esc_html__( 'Comoros', 'tribe-events-calendar' ),
					'CG' => esc_html__( 'Congo', 'tribe-events-calendar' ),
					'CD' => esc_html__( 'Congo, The Democratic Republic Of The', 'tribe-events-calendar' ),
					'CK' => esc_html__( 'Cook Islands', 'tribe-events-calendar' ),
					'CR' => esc_html__( 'Costa Rica', 'tribe-events-calendar' ),
					'CI' => esc_html__( "Cote D'Ivoire", 'tribe-events-calendar' ),
					'HR' => esc_html__( 'Croatia (Local Name: Hrvatska)', 'tribe-events-calendar' ),
					'CU' => esc_html__( 'Cuba', 'tribe-events-calendar' ),
					'CY' => esc_html__( 'Cyprus', 'tribe-events-calendar' ),
					'CZ' => esc_html__( 'Czech Republic', 'tribe-events-calendar' ),
					'DK' => esc_html__( 'Denmark', 'tribe-events-calendar' ),
					'DJ' => esc_html__( 'Djibouti', 'tribe-events-calendar' ),
					'DM' => esc_html__( 'Dominica', 'tribe-events-calendar' ),
					'DO' => esc_html__( 'Dominican Republic', 'tribe-events-calendar' ),
					'TP' => esc_html__( 'East Timor', 'tribe-events-calendar' ),
					'EC' => esc_html__( 'Ecuador', 'tribe-events-calendar' ),
					'EG' => esc_html__( 'Egypt', 'tribe-events-calendar' ),
					'SV' => esc_html__( 'El Salvador', 'tribe-events-calendar' ),
					'GQ' => esc_html__( 'Equatorial Guinea', 'tribe-events-calendar' ),
					'ER' => esc_html__( 'Eritrea', 'tribe-events-calendar' ),
					'EE' => esc_html__( 'Estonia', 'tribe-events-calendar' ),
					'ET' => esc_html__( 'Ethiopia', 'tribe-events-calendar' ),
					'FK' => esc_html__( 'Falkland Islands (Malvinas)', 'tribe-events-calendar' ),
					'FO' => esc_html__( 'Faroe Islands', 'tribe-events-calendar' ),
					'FJ' => esc_html__( 'Fiji', 'tribe-events-calendar' ),
					'FI' => esc_html__( 'Finland', 'tribe-events-calendar' ),
					'FR' => esc_html__( 'France', 'tribe-events-calendar' ),
					'FX' => esc_html__( 'France, Metropolitan', 'tribe-events-calendar' ),
					'GF' => esc_html__( 'French Guiana', 'tribe-events-calendar' ),
					'PF' => esc_html__( 'French Polynesia', 'tribe-events-calendar' ),
					'TF' => esc_html__( 'French Southern Territories', 'tribe-events-calendar' ),
					'GA' => esc_html__( 'Gabon', 'tribe-events-calendar' ),
					'GM' => esc_html__( 'Gambia', 'tribe-events-calendar' ),
					'GE' => esc_html__( 'Georgia', 'tribe-events-calendar' ),
					'DE' => esc_html__( 'Germany', 'tribe-events-calendar' ),
					'GH' => esc_html__( 'Ghana', 'tribe-events-calendar' ),
					'GI' => esc_html__( 'Gibraltar', 'tribe-events-calendar' ),
					'GR' => esc_html__( 'Greece', 'tribe-events-calendar' ),
					'GL' => esc_html__( 'Greenland', 'tribe-events-calendar' ),
					'GD' => esc_html__( 'Grenada', 'tribe-events-calendar' ),
					'GP' => esc_html__( 'Guadeloupe', 'tribe-events-calendar' ),
					'GU' => esc_html__( 'Guam', 'tribe-events-calendar' ),
					'GT' => esc_html__( 'Guatemala', 'tribe-events-calendar' ),
					'GN' => esc_html__( 'Guinea', 'tribe-events-calendar' ),
					'GW' => esc_html__( 'Guinea-Bissau', 'tribe-events-calendar' ),
					'GY' => esc_html__( 'Guyana', 'tribe-events-calendar' ),
					'HT' => esc_html__( 'Haiti', 'tribe-events-calendar' ),
					'HM' => esc_html__( 'Heard And Mc Donald Islands', 'tribe-events-calendar' ),
					'VA' => esc_html__( 'Holy See (Vatican City State)', 'tribe-events-calendar' ),
					'HN' => esc_html__( 'Honduras', 'tribe-events-calendar' ),
					'HK' => esc_html__( 'Hong Kong', 'tribe-events-calendar' ),
					'HU' => esc_html__( 'Hungary', 'tribe-events-calendar' ),
					'IS' => esc_html__( 'Iceland', 'tribe-events-calendar' ),
					'IN' => esc_html__( 'India', 'tribe-events-calendar' ),
					'ID' => esc_html__( 'Indonesia', 'tribe-events-calendar' ),
					'IR' => esc_html__( 'Iran (Islamic Republic Of)', 'tribe-events-calendar' ),
					'IQ' => esc_html__( 'Iraq', 'tribe-events-calendar' ),
					'IE' => esc_html__( 'Ireland', 'tribe-events-calendar' ),
					'IL' => esc_html__( 'Israel', 'tribe-events-calendar' ),
					'IT' => esc_html__( 'Italy', 'tribe-events-calendar' ),
					'JM' => esc_html__( 'Jamaica', 'tribe-events-calendar' ),
					'JP' => esc_html__( 'Japan', 'tribe-events-calendar' ),
					'JO' => esc_html__( 'Jordan', 'tribe-events-calendar' ),
					'KZ' => esc_html__( 'Kazakhstan', 'tribe-events-calendar' ),
					'KE' => esc_html__( 'Kenya', 'tribe-events-calendar' ),
					'KI' => esc_html__( 'Kiribati', 'tribe-events-calendar' ),
					'KP' => esc_html__( "Korea, Democratic People's Republic Of", 'tribe-events-calendar' ),
					'KR' => esc_html__( 'Korea, Republic Of', 'tribe-events-calendar' ),
					'KW' => esc_html__( 'Kuwait', 'tribe-events-calendar' ),
					'KG' => esc_html__( 'Kyrgyzstan', 'tribe-events-calendar' ),
					'LA' => esc_html__( "Lao People's Democratic Republic", 'tribe-events-calendar' ),
					'LV' => esc_html__( 'Latvia', 'tribe-events-calendar' ),
					'LB' => esc_html__( 'Lebanon', 'tribe-events-calendar' ),
					'LS' => esc_html__( 'Lesotho', 'tribe-events-calendar' ),
					'LR' => esc_html__( 'Liberia', 'tribe-events-calendar' ),
					'LY' => esc_html__( 'Libya', 'tribe-events-calendar' ),
					'LI' => esc_html__( 'Liechtenstein', 'tribe-events-calendar' ),
					'LT' => esc_html__( 'Lithuania', 'tribe-events-calendar' ),
					'LU' => esc_html__( 'Luxembourg', 'tribe-events-calendar' ),
					'MO' => esc_html__( 'Macau', 'tribe-events-calendar' ),
					'MK' => esc_html__( 'Macedonia', 'tribe-events-calendar' ),
					'MG' => esc_html__( 'Madagascar', 'tribe-events-calendar' ),
					'MW' => esc_html__( 'Malawi', 'tribe-events-calendar' ),
					'MY' => esc_html__( 'Malaysia', 'tribe-events-calendar' ),
					'MV' => esc_html__( 'Maldives', 'tribe-events-calendar' ),
					'ML' => esc_html__( 'Mali', 'tribe-events-calendar' ),
					'MT' => esc_html__( 'Malta', 'tribe-events-calendar' ),
					'MH' => esc_html__( 'Marshall Islands', 'tribe-events-calendar' ),
					'MQ' => esc_html__( 'Martinique', 'tribe-events-calendar' ),
					'MR' => esc_html__( 'Mauritania', 'tribe-events-calendar' ),
					'MU' => esc_html__( 'Mauritius', 'tribe-events-calendar' ),
					'YT' => esc_html__( 'Mayotte', 'tribe-events-calendar' ),
					'MX' => esc_html__( 'Mexico', 'tribe-events-calendar' ),
					'FM' => esc_html__( 'Micronesia, Federated States Of', 'tribe-events-calendar' ),
					'MD' => esc_html__( 'Moldova, Republic Of', 'tribe-events-calendar' ),
					'MC' => esc_html__( 'Monaco', 'tribe-events-calendar' ),
					'MN' => esc_html__( 'Mongolia', 'tribe-events-calendar' ),
					'ME' => esc_html__( 'Montenegro', 'tribe-events-calendar' ),
					'MS' => esc_html__( 'Montserrat', 'tribe-events-calendar' ),
					'MA' => esc_html__( 'Morocco', 'tribe-events-calendar' ),
					'MZ' => esc_html__( 'Mozambique', 'tribe-events-calendar' ),
					'MM' => esc_html__( 'Myanmar', 'tribe-events-calendar' ),
					'NA' => esc_html__( 'Namibia', 'tribe-events-calendar' ),
					'NR' => esc_html__( 'Nauru', 'tribe-events-calendar' ),
					'NP' => esc_html__( 'Nepal', 'tribe-events-calendar' ),
					'NL' => esc_html__( 'Netherlands', 'tribe-events-calendar' ),
					'AN' => esc_html__( 'Netherlands Antilles', 'tribe-events-calendar' ),
					'NC' => esc_html__( 'New Caledonia', 'tribe-events-calendar' ),
					'NZ' => esc_html__( 'New Zealand', 'tribe-events-calendar' ),
					'NI' => esc_html__( 'Nicaragua', 'tribe-events-calendar' ),
					'NE' => esc_html__( 'Niger', 'tribe-events-calendar' ),
					'NG' => esc_html__( 'Nigeria', 'tribe-events-calendar' ),
					'NU' => esc_html__( 'Niue', 'tribe-events-calendar' ),
					'NF' => esc_html__( 'Norfolk Island', 'tribe-events-calendar' ),
					'MP' => esc_html__( 'Northern Mariana Islands', 'tribe-events-calendar' ),
					'NO' => esc_html__( 'Norway', 'tribe-events-calendar' ),
					'OM' => esc_html__( 'Oman', 'tribe-events-calendar' ),
					'PK' => esc_html__( 'Pakistan', 'tribe-events-calendar' ),
					'PW' => esc_html__( 'Palau', 'tribe-events-calendar' ),
					'PA' => esc_html__( 'Panama', 'tribe-events-calendar' ),
					'PG' => esc_html__( 'Papua New Guinea', 'tribe-events-calendar' ),
					'PY' => esc_html__( 'Paraguay', 'tribe-events-calendar' ),
					'PE' => esc_html__( 'Peru', 'tribe-events-calendar' ),
					'PH' => esc_html__( 'Philippines', 'tribe-events-calendar' ),
					'PN' => esc_html__( 'Pitcairn', 'tribe-events-calendar' ),
					'PL' => esc_html__( 'Poland', 'tribe-events-calendar' ),
					'PT' => esc_html__( 'Portugal', 'tribe-events-calendar' ),
					'PR' => esc_html__( 'Puerto Rico', 'tribe-events-calendar' ),
					'QA' => esc_html__( 'Qatar', 'tribe-events-calendar' ),
					'RE' => esc_html__( 'Reunion', 'tribe-events-calendar' ),
					'RO' => esc_html__( 'Romania', 'tribe-events-calendar' ),
					'RU' => esc_html__( 'Russian Federation', 'tribe-events-calendar' ),
					'RW' => esc_html__( 'Rwanda', 'tribe-events-calendar' ),
					'KN' => esc_html__( 'Saint Kitts And Nevis', 'tribe-events-calendar' ),
					'LC' => esc_html__( 'Saint Lucia', 'tribe-events-calendar' ),
					'VC' => esc_html__( 'Saint Vincent And The Grenadines', 'tribe-events-calendar' ),
					'WS' => esc_html__( 'Samoa', 'tribe-events-calendar' ),
					'SM' => esc_html__( 'San Marino', 'tribe-events-calendar' ),
					'ST' => esc_html__( 'Sao Tome And Principe', 'tribe-events-calendar' ),
					'SA' => esc_html__( 'Saudi Arabia', 'tribe-events-calendar' ),
					'SN' => esc_html__( 'Senegal', 'tribe-events-calendar' ),
					'RS' => esc_html__( 'Serbia', 'tribe-events-calendar' ),
					'SC' => esc_html__( 'Seychelles', 'tribe-events-calendar' ),
					'SL' => esc_html__( 'Sierra Leone', 'tribe-events-calendar' ),
					'SG' => esc_html__( 'Singapore', 'tribe-events-calendar' ),
					'SK' => esc_html__( 'Slovakia (Slovak Republic)', 'tribe-events-calendar' ),
					'SI' => esc_html__( 'Slovenia', 'tribe-events-calendar' ),
					'SB' => esc_html__( 'Solomon Islands', 'tribe-events-calendar' ),
					'SO' => esc_html__( 'Somalia', 'tribe-events-calendar' ),
					'ZA' => esc_html__( 'South Africa', 'tribe-events-calendar' ),
					'GS' => esc_html__( 'South Georgia, South Sandwich Islands', 'tribe-events-calendar' ),
					'ES' => esc_html__( 'Spain', 'tribe-events-calendar' ),
					'LK' => esc_html__( 'Sri Lanka', 'tribe-events-calendar' ),
					'SH' => esc_html__( 'St. Helena', 'tribe-events-calendar' ),
					'PM' => esc_html__( 'St. Pierre And Miquelon', 'tribe-events-calendar' ),
					'SD' => esc_html__( 'Sudan', 'tribe-events-calendar' ),
					'SR' => esc_html__( 'Suriname', 'tribe-events-calendar' ),
					'SJ' => esc_html__( 'Svalbard And Jan Mayen Islands', 'tribe-events-calendar' ),
					'SZ' => esc_html__( 'Swaziland', 'tribe-events-calendar' ),
					'SE' => esc_html__( 'Sweden', 'tribe-events-calendar' ),
					'CH' => esc_html__( 'Switzerland', 'tribe-events-calendar' ),
					'SY' => esc_html__( 'Syrian Arab Republic', 'tribe-events-calendar' ),
					'TW' => esc_html__( 'Taiwan', 'tribe-events-calendar' ),
					'TJ' => esc_html__( 'Tajikistan', 'tribe-events-calendar' ),
					'TZ' => esc_html__( 'Tanzania, United Republic Of', 'tribe-events-calendar' ),
					'TH' => esc_html__( 'Thailand', 'tribe-events-calendar' ),
					'TG' => esc_html__( 'Togo', 'tribe-events-calendar' ),
					'TK' => esc_html__( 'Tokelau', 'tribe-events-calendar' ),
					'TO' => esc_html__( 'Tonga', 'tribe-events-calendar' ),
					'TT' => esc_html__( 'Trinidad And Tobago', 'tribe-events-calendar' ),
					'TN' => esc_html__( 'Tunisia', 'tribe-events-calendar' ),
					'TR' => esc_html__( 'Turkey', 'tribe-events-calendar' ),
					'TM' => esc_html__( 'Turkmenistan', 'tribe-events-calendar' ),
					'TC' => esc_html__( 'Turks And Caicos Islands', 'tribe-events-calendar' ),
					'TV' => esc_html__( 'Tuvalu', 'tribe-events-calendar' ),
					'UG' => esc_html__( 'Uganda', 'tribe-events-calendar' ),
					'UA' => esc_html__( 'Ukraine', 'tribe-events-calendar' ),
					'AE' => esc_html__( 'United Arab Emirates', 'tribe-events-calendar' ),
					'GB' => esc_html__( 'United Kingdom', 'tribe-events-calendar' ),
					'UM' => esc_html__( 'United States Minor Outlying Islands', 'tribe-events-calendar' ),
					'UY' => esc_html__( 'Uruguay', 'tribe-events-calendar' ),
					'UZ' => esc_html__( 'Uzbekistan', 'tribe-events-calendar' ),
					'VU' => esc_html__( 'Vanuatu', 'tribe-events-calendar' ),
					'VE' => esc_html__( 'Venezuela', 'tribe-events-calendar' ),
					'VN' => esc_html__( 'Viet Nam', 'tribe-events-calendar' ),
					'VG' => esc_html__( 'Virgin Islands (British)', 'tribe-events-calendar' ),
					'VI' => esc_html__( 'Virgin Islands (U.S.)', 'tribe-events-calendar' ),
					'WF' => esc_html__( 'Wallis And Futuna Islands', 'tribe-events-calendar' ),
					'EH' => esc_html__( 'Western Sahara', 'tribe-events-calendar' ),
					'YE' => esc_html__( 'Yemen', 'tribe-events-calendar' ),
					'ZM' => esc_html__( 'Zambia', 'tribe-events-calendar' ),
					'ZW' => esc_html__( 'Zimbabwe', 'tribe-events-calendar' ),
				);
			}
			if ( ( $postId || $useDefault ) ) {
				$countryValue = get_post_meta( $postId, '_EventCountry', true );
				if ( $countryValue ) {
					$defaultCountry = array( array_search( $countryValue, $countries ), $countryValue );
				} else {
					$defaultCountry = tribe_get_default_value( 'country' );
				}
				if ( $defaultCountry && $defaultCountry[0] != '' ) {
					$selectCountry = array_shift( $countries );
					asort( $countries );
					$countries = array( $defaultCountry[0] => __( $defaultCountry[1], 'tribe-events-calendar' ) ) + $countries;
					$countries = array( '' => __( $selectCountry, 'tribe-events-calendar' ) ) + $countries;
					array_unique( $countries );
				}

				return $countries;
			} else {
				return $countries;
			}
		}

		/**
		 * Get the i18ned states available to the plugin.
		 *
		 * @return array The states array.
		 */
		public static function loadStates() {
			return array(
				'AL' => esc_html__( 'Alabama', 'tribe-events-calendar' ),
				'AK' => esc_html__( 'Alaska', 'tribe-events-calendar' ),
				'AZ' => esc_html__( 'Arizona', 'tribe-events-calendar' ),
				'AR' => esc_html__( 'Arkansas', 'tribe-events-calendar' ),
				'CA' => esc_html__( 'California', 'tribe-events-calendar' ),
				'CO' => esc_html__( 'Colorado', 'tribe-events-calendar' ),
				'CT' => esc_html__( 'Connecticut', 'tribe-events-calendar' ),
				'DE' => esc_html__( 'Delaware', 'tribe-events-calendar' ),
				'DC' => esc_html__( 'District of Columbia', 'tribe-events-calendar' ),
				'FL' => esc_html__( 'Florida', 'tribe-events-calendar' ),
				'GA' => esc_html__( 'Georgia', 'tribe-events-calendar' ),
				'HI' => esc_html__( 'Hawaii', 'tribe-events-calendar' ),
				'ID' => esc_html__( 'Idaho', 'tribe-events-calendar' ),
				'IL' => esc_html__( 'Illinois', 'tribe-events-calendar' ),
				'IN' => esc_html__( 'Indiana', 'tribe-events-calendar' ),
				'IA' => esc_html__( 'Iowa', 'tribe-events-calendar' ),
				'KS' => esc_html__( 'Kansas', 'tribe-events-calendar' ),
				'KY' => esc_html__( 'Kentucky', 'tribe-events-calendar' ),
				'LA' => esc_html__( 'Louisiana', 'tribe-events-calendar' ),
				'ME' => esc_html__( 'Maine', 'tribe-events-calendar' ),
				'MD' => esc_html__( 'Maryland', 'tribe-events-calendar' ),
				'MA' => esc_html__( 'Massachusetts', 'tribe-events-calendar' ),
				'MI' => esc_html__( 'Michigan', 'tribe-events-calendar' ),
				'MN' => esc_html__( 'Minnesota', 'tribe-events-calendar' ),
				'MS' => esc_html__( 'Mississippi', 'tribe-events-calendar' ),
				'MO' => esc_html__( 'Missouri', 'tribe-events-calendar' ),
				'MT' => esc_html__( 'Montana', 'tribe-events-calendar' ),
				'NE' => esc_html__( 'Nebraska', 'tribe-events-calendar' ),
				'NV' => esc_html__( 'Nevada', 'tribe-events-calendar' ),
				'NH' => esc_html__( 'New Hampshire', 'tribe-events-calendar' ),
				'NJ' => esc_html__( 'New Jersey', 'tribe-events-calendar' ),
				'NM' => esc_html__( 'New Mexico', 'tribe-events-calendar' ),
				'NY' => esc_html__( 'New York', 'tribe-events-calendar' ),
				'NC' => esc_html__( 'North Carolina', 'tribe-events-calendar' ),
				'ND' => esc_html__( 'North Dakota', 'tribe-events-calendar' ),
				'OH' => esc_html__( 'Ohio', 'tribe-events-calendar' ),
				'OK' => esc_html__( 'Oklahoma', 'tribe-events-calendar' ),
				'OR' => esc_html__( 'Oregon', 'tribe-events-calendar' ),
				'PA' => esc_html__( 'Pennsylvania', 'tribe-events-calendar' ),
				'RI' => esc_html__( 'Rhode Island', 'tribe-events-calendar' ),
				'SC' => esc_html__( 'South Carolina', 'tribe-events-calendar' ),
				'SD' => esc_html__( 'South Dakota', 'tribe-events-calendar' ),
				'TN' => esc_html__( 'Tennessee', 'tribe-events-calendar' ),
				'TX' => esc_html__( 'Texas', 'tribe-events-calendar' ),
				'UT' => esc_html__( 'Utah', 'tribe-events-calendar' ),
				'VT' => esc_html__( 'Vermont', 'tribe-events-calendar' ),
				'VA' => esc_html__( 'Virginia', 'tribe-events-calendar' ),
				'WA' => esc_html__( 'Washington', 'tribe-events-calendar' ),
				'WV' => esc_html__( 'West Virginia', 'tribe-events-calendar' ),
				'WI' => esc_html__( 'Wisconsin', 'tribe-events-calendar' ),
				'WY' => esc_html__( 'Wyoming', 'tribe-events-calendar' ),
			);
		}

		/**
		 * Builds a set of options for displaying an hour chooser
		 *
		 * @param string $date the current date (optional)
		 * @param bool   $isStart
		 *
		 * @return string a set of HTML options with hours (current hour selected)
		 */
		public static function getHourOptions( $date = '', $isStart = false ) {
			$hours = self::hours();

			if ( count( $hours ) == 12 ) {
				$h = 'h';
			} else {
				$h = 'H';
			}
			$options = '';

			if ( empty( $date ) ) {
				$hour = ( $isStart ) ? '08' : ( count( $hours ) == 12 ? '05' : '17' );
			} else {
				$timestamp = strtotime( $date );
				$hour      = date( $h, $timestamp );
				// fix hours if time_format has changed from what is saved
				if ( preg_match( '(pm|PM)', $timestamp ) && $h == 'H' ) {
					$hour = $hour + 12;
				}
				if ( $hour > 12 && $h == 'h' ) {
					$hour = $hour - 12;
				}
			}

			$hour = apply_filters( 'tribe_get_hour_options', $hour, $date, $isStart );

			foreach ( $hours as $hourText ) {
				if ( $hour == $hourText ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$options .= "<option value='$hourText' $selected>$hourText</option>\n";
			}

			return $options;
		}

		/**
		 * Builds a set of options for displaying a minute chooser
		 *
		 * @param string $date the current date (optional)
		 * @param bool   $isStart
		 *
		 * @return string a set of HTML options with minutes (current minute selected)
		 */
		public static function getMinuteOptions( $date = '', $isStart = false ) {
			$minutes = self::minutes();
			$options = '';

			if ( empty( $date ) ) {
				$minute = '00';
			} else {
				$minute = date( 'i', strtotime( $date ) );
			}

			$minute = apply_filters( 'tribe_get_minute_options', $minute, $date, $isStart );

			foreach ( $minutes as $minuteText ) {
				if ( $minute == $minuteText ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$options .= "<option value='$minuteText' $selected>$minuteText</option>\n";
			}

			return $options;
		}

		/**
		 * Helper method to return an array of 1-12 for hours
		 *
		 * @return array The hours array.
		 */
		private static function hours() {
			$hours      = array();
			$rangeMax   = self::is_24hr_format() ? 23 : 12;
			$rangeStart = $rangeMax > 12 ? 0 : 1;
			foreach ( range( $rangeStart, $rangeMax ) as $hour ) {
				if ( $hour < 10 ) {
					$hour = '0' . $hour;
				}
				$hours[ $hour ] = $hour;
			}

			// In a 12hr context lets put 12 at the start (so the sequence will run 12, 1, 2, 3 ... 11)
			if ( 12 === $rangeMax ) {
				array_unshift( $hours, array_pop( $hours ) );
			}

			return $hours;
		}

		/**
		 * Determines if the provided date/time format (or else the default WordPress time_format)
		 * is 24hr or not.
		 *
		 * In inconclusive cases, such as if there are now hour-format characters, 12hr format is
		 * assumed.
		 *
		 * @param null $format
		 * @return bool
		 */
		public static function is_24hr_format( $format = null ) {
			// Use the provided format or else use the value of the current time_format setting
			$format = ( null === $format ) ? get_option( 'time_format', Tribe__Events__Date_Utils::TIMEFORMAT ) : $format;

			// Count instances of the H and G symbols
			$h_symbols = substr_count( $format, 'H' );
			$g_symbols = substr_count( $format, 'G' );

			// If none have been found then consider the format to be 12hr
			if ( ! $h_symbols && ! $g_symbols ) return false;

			// It's possible H or G have been included as escaped characters
			$h_escaped = substr_count( $format, '\H' );
			$g_escaped = substr_count( $format, '\G' );

			// Final check, accounting for possibility of escaped values
			return ( $h_symbols > $h_escaped || $g_symbols > $g_escaped );
		}

		/**
		 * Helper method to return an array of 00-59 for minutes
		 *
		 * @return array The minutes array.
		 */
		private static function minutes() {
			$minutes = array();
			for ( $minute = 0; $minute < 60; $minute += 5 ) {
				if ( $minute < 10 ) {
					$minute = '0' . $minute;
				}
				$minutes[ $minute ] = $minute;
			}

			return $minutes;
		}

		/**
		 * Builds a set of options for diplaying a meridian chooser
		 *
		 * @param string $date YYYY-MM-DD HH:MM:SS to select (optional)
		 * @param bool   $isStart
		 *
		 * @return string a set of HTML options with all meridians
		 */
		public static function getMeridianOptions( $date = '', $isStart = false ) {
			if ( strstr( get_option( 'time_format', Tribe__Events__Date_Utils::TIMEFORMAT ), 'A' ) ) {
				$a         = 'A';
				$meridians = array( 'AM', 'PM' );
			} else {
				$a         = 'a';
				$meridians = array( 'am', 'pm' );
			}
			if ( empty( $date ) ) {
				$meridian = ( $isStart ) ? $meridians[0] : $meridians[1];
			} else {
				$meridian = date( $a, strtotime( $date ) );
			}

			$meridian = apply_filters( 'tribe_get_meridian_options', $meridian, $date, $isStart );

			$return = '';
			foreach ( $meridians as $m ) {
				$return .= "<option value='$m'";
				if ( $m == $meridian ) {
					$return .= ' selected="selected"';
				}
				$return .= ">$m</option>\n";
			}

			return $return;
		}

		/**
		 * Helper method to return an array of years
		 * default is back 5 and forward 5
		 *
		 * @return array The array of years.
		 */
		private static function years() {
			$current_year  = (int) date_i18n( 'Y' );
			$years_back    = (int) apply_filters( 'tribe_years_to_go_back', 5, $current_year );
			$years_forward = (int) apply_filters( 'tribe_years_to_go_forward', 5, $current_year );
			$years         = array();
			for ( $i = $years_back; $i > 0; $i -- ) {
				$year    = $current_year - $i;
				$years[] = $year;
			}
			$years[] = $current_year;
			for ( $i = 1; $i <= $years_forward; $i ++ ) {
				$year    = $current_year + $i;
				$years[] = $year;
			}

			return (array) apply_filters( 'tribe_years_array', $years );
		}


		/**
		 * Helper method to return an array of 1-31 for days
		 *
		 * @return array The days array.
		 */
		public static function days( $totalDays ) {
			$days = array();
			foreach ( range( 1, $totalDays ) as $day ) {
				$days[ $day ] = $day;
			}

			return $days;
		}
	}
}
