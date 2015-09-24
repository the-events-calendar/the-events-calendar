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
					'' => esc_html__( 'Select a Country:', 'the-events-calendar' ),
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
					''   => esc_html__( 'Select a Country:', 'the-events-calendar' ),
					'US' => esc_html__( 'United States', 'the-events-calendar' ),
					'AF' => esc_html__( 'Afghanistan', 'the-events-calendar' ),
					'AL' => esc_html__( 'Albania', 'the-events-calendar' ),
					'DZ' => esc_html__( 'Algeria', 'the-events-calendar' ),
					'AS' => esc_html__( 'American Samoa', 'the-events-calendar' ),
					'AD' => esc_html__( 'Andorra', 'the-events-calendar' ),
					'AO' => esc_html__( 'Angola', 'the-events-calendar' ),
					'AI' => esc_html__( 'Anguilla', 'the-events-calendar' ),
					'AQ' => esc_html__( 'Antarctica', 'the-events-calendar' ),
					'AG' => esc_html__( 'Antigua And Barbuda', 'the-events-calendar' ),
					'AR' => esc_html__( 'Argentina', 'the-events-calendar' ),
					'AM' => esc_html__( 'Armenia', 'the-events-calendar' ),
					'AW' => esc_html__( 'Aruba', 'the-events-calendar' ),
					'AU' => esc_html__( 'Australia', 'the-events-calendar' ),
					'AT' => esc_html__( 'Austria', 'the-events-calendar' ),
					'AZ' => esc_html__( 'Azerbaijan', 'the-events-calendar' ),
					'BS' => esc_html__( 'Bahamas', 'the-events-calendar' ),
					'BH' => esc_html__( 'Bahrain', 'the-events-calendar' ),
					'BD' => esc_html__( 'Bangladesh', 'the-events-calendar' ),
					'BB' => esc_html__( 'Barbados', 'the-events-calendar' ),
					'BY' => esc_html__( 'Belarus', 'the-events-calendar' ),
					'BE' => esc_html__( 'Belgium', 'the-events-calendar' ),
					'BZ' => esc_html__( 'Belize', 'the-events-calendar' ),
					'BJ' => esc_html__( 'Benin', 'the-events-calendar' ),
					'BM' => esc_html__( 'Bermuda', 'the-events-calendar' ),
					'BT' => esc_html__( 'Bhutan', 'the-events-calendar' ),
					'BO' => esc_html__( 'Bolivia', 'the-events-calendar' ),
					'BA' => esc_html__( 'Bosnia And Herzegowina', 'the-events-calendar' ),
					'BW' => esc_html__( 'Botswana', 'the-events-calendar' ),
					'BV' => esc_html__( 'Bouvet Island', 'the-events-calendar' ),
					'BR' => esc_html__( 'Brazil', 'the-events-calendar' ),
					'IO' => esc_html__( 'British Indian Ocean Territory', 'the-events-calendar' ),
					'BN' => esc_html__( 'Brunei Darussalam', 'the-events-calendar' ),
					'BG' => esc_html__( 'Bulgaria', 'the-events-calendar' ),
					'BF' => esc_html__( 'Burkina Faso', 'the-events-calendar' ),
					'BI' => esc_html__( 'Burundi', 'the-events-calendar' ),
					'KH' => esc_html__( 'Cambodia', 'the-events-calendar' ),
					'CM' => esc_html__( 'Cameroon', 'the-events-calendar' ),
					'CA' => esc_html__( 'Canada', 'the-events-calendar' ),
					'CV' => esc_html__( 'Cape Verde', 'the-events-calendar' ),
					'KY' => esc_html__( 'Cayman Islands', 'the-events-calendar' ),
					'CF' => esc_html__( 'Central African Republic', 'the-events-calendar' ),
					'TD' => esc_html__( 'Chad', 'the-events-calendar' ),
					'CL' => esc_html__( 'Chile', 'the-events-calendar' ),
					'CN' => esc_html__( 'China', 'the-events-calendar' ),
					'CX' => esc_html__( 'Christmas Island', 'the-events-calendar' ),
					'CC' => esc_html__( 'Cocos (Keeling) Islands', 'the-events-calendar' ),
					'CO' => esc_html__( 'Colombia', 'the-events-calendar' ),
					'KM' => esc_html__( 'Comoros', 'the-events-calendar' ),
					'CG' => esc_html__( 'Congo', 'the-events-calendar' ),
					'CD' => esc_html__( 'Congo, The Democratic Republic Of The', 'the-events-calendar' ),
					'CK' => esc_html__( 'Cook Islands', 'the-events-calendar' ),
					'CR' => esc_html__( 'Costa Rica', 'the-events-calendar' ),
					'CI' => esc_html__( "Cote D'Ivoire", 'the-events-calendar' ),
					'HR' => esc_html__( 'Croatia (Local Name: Hrvatska)', 'the-events-calendar' ),
					'CU' => esc_html__( 'Cuba', 'the-events-calendar' ),
					'CY' => esc_html__( 'Cyprus', 'the-events-calendar' ),
					'CZ' => esc_html__( 'Czech Republic', 'the-events-calendar' ),
					'DK' => esc_html__( 'Denmark', 'the-events-calendar' ),
					'DJ' => esc_html__( 'Djibouti', 'the-events-calendar' ),
					'DM' => esc_html__( 'Dominica', 'the-events-calendar' ),
					'DO' => esc_html__( 'Dominican Republic', 'the-events-calendar' ),
					'TP' => esc_html__( 'East Timor', 'the-events-calendar' ),
					'EC' => esc_html__( 'Ecuador', 'the-events-calendar' ),
					'EG' => esc_html__( 'Egypt', 'the-events-calendar' ),
					'SV' => esc_html__( 'El Salvador', 'the-events-calendar' ),
					'GQ' => esc_html__( 'Equatorial Guinea', 'the-events-calendar' ),
					'ER' => esc_html__( 'Eritrea', 'the-events-calendar' ),
					'EE' => esc_html__( 'Estonia', 'the-events-calendar' ),
					'ET' => esc_html__( 'Ethiopia', 'the-events-calendar' ),
					'FK' => esc_html__( 'Falkland Islands (Malvinas)', 'the-events-calendar' ),
					'FO' => esc_html__( 'Faroe Islands', 'the-events-calendar' ),
					'FJ' => esc_html__( 'Fiji', 'the-events-calendar' ),
					'FI' => esc_html__( 'Finland', 'the-events-calendar' ),
					'FR' => esc_html__( 'France', 'the-events-calendar' ),
					'FX' => esc_html__( 'France, Metropolitan', 'the-events-calendar' ),
					'GF' => esc_html__( 'French Guiana', 'the-events-calendar' ),
					'PF' => esc_html__( 'French Polynesia', 'the-events-calendar' ),
					'TF' => esc_html__( 'French Southern Territories', 'the-events-calendar' ),
					'GA' => esc_html__( 'Gabon', 'the-events-calendar' ),
					'GM' => esc_html__( 'Gambia', 'the-events-calendar' ),
					'GE' => esc_html__( 'Georgia', 'the-events-calendar' ),
					'DE' => esc_html__( 'Germany', 'the-events-calendar' ),
					'GH' => esc_html__( 'Ghana', 'the-events-calendar' ),
					'GI' => esc_html__( 'Gibraltar', 'the-events-calendar' ),
					'GR' => esc_html__( 'Greece', 'the-events-calendar' ),
					'GL' => esc_html__( 'Greenland', 'the-events-calendar' ),
					'GD' => esc_html__( 'Grenada', 'the-events-calendar' ),
					'GP' => esc_html__( 'Guadeloupe', 'the-events-calendar' ),
					'GU' => esc_html__( 'Guam', 'the-events-calendar' ),
					'GT' => esc_html__( 'Guatemala', 'the-events-calendar' ),
					'GN' => esc_html__( 'Guinea', 'the-events-calendar' ),
					'GW' => esc_html__( 'Guinea-Bissau', 'the-events-calendar' ),
					'GY' => esc_html__( 'Guyana', 'the-events-calendar' ),
					'HT' => esc_html__( 'Haiti', 'the-events-calendar' ),
					'HM' => esc_html__( 'Heard And Mc Donald Islands', 'the-events-calendar' ),
					'VA' => esc_html__( 'Holy See (Vatican City State)', 'the-events-calendar' ),
					'HN' => esc_html__( 'Honduras', 'the-events-calendar' ),
					'HK' => esc_html__( 'Hong Kong', 'the-events-calendar' ),
					'HU' => esc_html__( 'Hungary', 'the-events-calendar' ),
					'IS' => esc_html__( 'Iceland', 'the-events-calendar' ),
					'IN' => esc_html__( 'India', 'the-events-calendar' ),
					'ID' => esc_html__( 'Indonesia', 'the-events-calendar' ),
					'IR' => esc_html__( 'Iran (Islamic Republic Of)', 'the-events-calendar' ),
					'IQ' => esc_html__( 'Iraq', 'the-events-calendar' ),
					'IE' => esc_html__( 'Ireland', 'the-events-calendar' ),
					'IL' => esc_html__( 'Israel', 'the-events-calendar' ),
					'IT' => esc_html__( 'Italy', 'the-events-calendar' ),
					'JM' => esc_html__( 'Jamaica', 'the-events-calendar' ),
					'JP' => esc_html__( 'Japan', 'the-events-calendar' ),
					'JO' => esc_html__( 'Jordan', 'the-events-calendar' ),
					'KZ' => esc_html__( 'Kazakhstan', 'the-events-calendar' ),
					'KE' => esc_html__( 'Kenya', 'the-events-calendar' ),
					'KI' => esc_html__( 'Kiribati', 'the-events-calendar' ),
					'KP' => esc_html__( "Korea, Democratic People's Republic Of", 'the-events-calendar' ),
					'KR' => esc_html__( 'Korea, Republic Of', 'the-events-calendar' ),
					'KW' => esc_html__( 'Kuwait', 'the-events-calendar' ),
					'KG' => esc_html__( 'Kyrgyzstan', 'the-events-calendar' ),
					'LA' => esc_html__( "Lao People's Democratic Republic", 'the-events-calendar' ),
					'LV' => esc_html__( 'Latvia', 'the-events-calendar' ),
					'LB' => esc_html__( 'Lebanon', 'the-events-calendar' ),
					'LS' => esc_html__( 'Lesotho', 'the-events-calendar' ),
					'LR' => esc_html__( 'Liberia', 'the-events-calendar' ),
					'LY' => esc_html__( 'Libya', 'the-events-calendar' ),
					'LI' => esc_html__( 'Liechtenstein', 'the-events-calendar' ),
					'LT' => esc_html__( 'Lithuania', 'the-events-calendar' ),
					'LU' => esc_html__( 'Luxembourg', 'the-events-calendar' ),
					'MO' => esc_html__( 'Macau', 'the-events-calendar' ),
					'MK' => esc_html__( 'Macedonia', 'the-events-calendar' ),
					'MG' => esc_html__( 'Madagascar', 'the-events-calendar' ),
					'MW' => esc_html__( 'Malawi', 'the-events-calendar' ),
					'MY' => esc_html__( 'Malaysia', 'the-events-calendar' ),
					'MV' => esc_html__( 'Maldives', 'the-events-calendar' ),
					'ML' => esc_html__( 'Mali', 'the-events-calendar' ),
					'MT' => esc_html__( 'Malta', 'the-events-calendar' ),
					'MH' => esc_html__( 'Marshall Islands', 'the-events-calendar' ),
					'MQ' => esc_html__( 'Martinique', 'the-events-calendar' ),
					'MR' => esc_html__( 'Mauritania', 'the-events-calendar' ),
					'MU' => esc_html__( 'Mauritius', 'the-events-calendar' ),
					'YT' => esc_html__( 'Mayotte', 'the-events-calendar' ),
					'MX' => esc_html__( 'Mexico', 'the-events-calendar' ),
					'FM' => esc_html__( 'Micronesia, Federated States Of', 'the-events-calendar' ),
					'MD' => esc_html__( 'Moldova, Republic Of', 'the-events-calendar' ),
					'MC' => esc_html__( 'Monaco', 'the-events-calendar' ),
					'MN' => esc_html__( 'Mongolia', 'the-events-calendar' ),
					'ME' => esc_html__( 'Montenegro', 'the-events-calendar' ),
					'MS' => esc_html__( 'Montserrat', 'the-events-calendar' ),
					'MA' => esc_html__( 'Morocco', 'the-events-calendar' ),
					'MZ' => esc_html__( 'Mozambique', 'the-events-calendar' ),
					'MM' => esc_html__( 'Myanmar', 'the-events-calendar' ),
					'NA' => esc_html__( 'Namibia', 'the-events-calendar' ),
					'NR' => esc_html__( 'Nauru', 'the-events-calendar' ),
					'NP' => esc_html__( 'Nepal', 'the-events-calendar' ),
					'NL' => esc_html__( 'Netherlands', 'the-events-calendar' ),
					'AN' => esc_html__( 'Netherlands Antilles', 'the-events-calendar' ),
					'NC' => esc_html__( 'New Caledonia', 'the-events-calendar' ),
					'NZ' => esc_html__( 'New Zealand', 'the-events-calendar' ),
					'NI' => esc_html__( 'Nicaragua', 'the-events-calendar' ),
					'NE' => esc_html__( 'Niger', 'the-events-calendar' ),
					'NG' => esc_html__( 'Nigeria', 'the-events-calendar' ),
					'NU' => esc_html__( 'Niue', 'the-events-calendar' ),
					'NF' => esc_html__( 'Norfolk Island', 'the-events-calendar' ),
					'MP' => esc_html__( 'Northern Mariana Islands', 'the-events-calendar' ),
					'NO' => esc_html__( 'Norway', 'the-events-calendar' ),
					'OM' => esc_html__( 'Oman', 'the-events-calendar' ),
					'PK' => esc_html__( 'Pakistan', 'the-events-calendar' ),
					'PW' => esc_html__( 'Palau', 'the-events-calendar' ),
					'PA' => esc_html__( 'Panama', 'the-events-calendar' ),
					'PG' => esc_html__( 'Papua New Guinea', 'the-events-calendar' ),
					'PY' => esc_html__( 'Paraguay', 'the-events-calendar' ),
					'PE' => esc_html__( 'Peru', 'the-events-calendar' ),
					'PH' => esc_html__( 'Philippines', 'the-events-calendar' ),
					'PN' => esc_html__( 'Pitcairn', 'the-events-calendar' ),
					'PL' => esc_html__( 'Poland', 'the-events-calendar' ),
					'PT' => esc_html__( 'Portugal', 'the-events-calendar' ),
					'PR' => esc_html__( 'Puerto Rico', 'the-events-calendar' ),
					'QA' => esc_html__( 'Qatar', 'the-events-calendar' ),
					'RE' => esc_html__( 'Reunion', 'the-events-calendar' ),
					'RO' => esc_html__( 'Romania', 'the-events-calendar' ),
					'RU' => esc_html__( 'Russian Federation', 'the-events-calendar' ),
					'RW' => esc_html__( 'Rwanda', 'the-events-calendar' ),
					'KN' => esc_html__( 'Saint Kitts And Nevis', 'the-events-calendar' ),
					'LC' => esc_html__( 'Saint Lucia', 'the-events-calendar' ),
					'VC' => esc_html__( 'Saint Vincent And The Grenadines', 'the-events-calendar' ),
					'WS' => esc_html__( 'Samoa', 'the-events-calendar' ),
					'SM' => esc_html__( 'San Marino', 'the-events-calendar' ),
					'ST' => esc_html__( 'Sao Tome And Principe', 'the-events-calendar' ),
					'SA' => esc_html__( 'Saudi Arabia', 'the-events-calendar' ),
					'SN' => esc_html__( 'Senegal', 'the-events-calendar' ),
					'RS' => esc_html__( 'Serbia', 'the-events-calendar' ),
					'SC' => esc_html__( 'Seychelles', 'the-events-calendar' ),
					'SL' => esc_html__( 'Sierra Leone', 'the-events-calendar' ),
					'SG' => esc_html__( 'Singapore', 'the-events-calendar' ),
					'SK' => esc_html__( 'Slovakia (Slovak Republic)', 'the-events-calendar' ),
					'SI' => esc_html__( 'Slovenia', 'the-events-calendar' ),
					'SB' => esc_html__( 'Solomon Islands', 'the-events-calendar' ),
					'SO' => esc_html__( 'Somalia', 'the-events-calendar' ),
					'ZA' => esc_html__( 'South Africa', 'the-events-calendar' ),
					'GS' => esc_html__( 'South Georgia, South Sandwich Islands', 'the-events-calendar' ),
					'ES' => esc_html__( 'Spain', 'the-events-calendar' ),
					'LK' => esc_html__( 'Sri Lanka', 'the-events-calendar' ),
					'SH' => esc_html__( 'St. Helena', 'the-events-calendar' ),
					'PM' => esc_html__( 'St. Pierre And Miquelon', 'the-events-calendar' ),
					'SD' => esc_html__( 'Sudan', 'the-events-calendar' ),
					'SR' => esc_html__( 'Suriname', 'the-events-calendar' ),
					'SJ' => esc_html__( 'Svalbard And Jan Mayen Islands', 'the-events-calendar' ),
					'SZ' => esc_html__( 'Swaziland', 'the-events-calendar' ),
					'SE' => esc_html__( 'Sweden', 'the-events-calendar' ),
					'CH' => esc_html__( 'Switzerland', 'the-events-calendar' ),
					'SY' => esc_html__( 'Syrian Arab Republic', 'the-events-calendar' ),
					'TW' => esc_html__( 'Taiwan', 'the-events-calendar' ),
					'TJ' => esc_html__( 'Tajikistan', 'the-events-calendar' ),
					'TZ' => esc_html__( 'Tanzania, United Republic Of', 'the-events-calendar' ),
					'TH' => esc_html__( 'Thailand', 'the-events-calendar' ),
					'TG' => esc_html__( 'Togo', 'the-events-calendar' ),
					'TK' => esc_html__( 'Tokelau', 'the-events-calendar' ),
					'TO' => esc_html__( 'Tonga', 'the-events-calendar' ),
					'TT' => esc_html__( 'Trinidad And Tobago', 'the-events-calendar' ),
					'TN' => esc_html__( 'Tunisia', 'the-events-calendar' ),
					'TR' => esc_html__( 'Turkey', 'the-events-calendar' ),
					'TM' => esc_html__( 'Turkmenistan', 'the-events-calendar' ),
					'TC' => esc_html__( 'Turks And Caicos Islands', 'the-events-calendar' ),
					'TV' => esc_html__( 'Tuvalu', 'the-events-calendar' ),
					'UG' => esc_html__( 'Uganda', 'the-events-calendar' ),
					'UA' => esc_html__( 'Ukraine', 'the-events-calendar' ),
					'AE' => esc_html__( 'United Arab Emirates', 'the-events-calendar' ),
					'GB' => esc_html__( 'United Kingdom', 'the-events-calendar' ),
					'UM' => esc_html__( 'United States Minor Outlying Islands', 'the-events-calendar' ),
					'UY' => esc_html__( 'Uruguay', 'the-events-calendar' ),
					'UZ' => esc_html__( 'Uzbekistan', 'the-events-calendar' ),
					'VU' => esc_html__( 'Vanuatu', 'the-events-calendar' ),
					'VE' => esc_html__( 'Venezuela', 'the-events-calendar' ),
					'VN' => esc_html__( 'Viet Nam', 'the-events-calendar' ),
					'VG' => esc_html__( 'Virgin Islands (British)', 'the-events-calendar' ),
					'VI' => esc_html__( 'Virgin Islands (U.S.)', 'the-events-calendar' ),
					'WF' => esc_html__( 'Wallis And Futuna Islands', 'the-events-calendar' ),
					'EH' => esc_html__( 'Western Sahara', 'the-events-calendar' ),
					'YE' => esc_html__( 'Yemen', 'the-events-calendar' ),
					'ZM' => esc_html__( 'Zambia', 'the-events-calendar' ),
					'ZW' => esc_html__( 'Zimbabwe', 'the-events-calendar' ),
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
					$countries = array( $defaultCountry[0] => __( $defaultCountry[1], 'the-events-calendar' ) ) + $countries;
					$countries = array( '' => __( $selectCountry, 'the-events-calendar' ) ) + $countries;
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
				'AL' => esc_html__( 'Alabama', 'the-events-calendar' ),
				'AK' => esc_html__( 'Alaska', 'the-events-calendar' ),
				'AZ' => esc_html__( 'Arizona', 'the-events-calendar' ),
				'AR' => esc_html__( 'Arkansas', 'the-events-calendar' ),
				'CA' => esc_html__( 'California', 'the-events-calendar' ),
				'CO' => esc_html__( 'Colorado', 'the-events-calendar' ),
				'CT' => esc_html__( 'Connecticut', 'the-events-calendar' ),
				'DE' => esc_html__( 'Delaware', 'the-events-calendar' ),
				'DC' => esc_html__( 'District of Columbia', 'the-events-calendar' ),
				'FL' => esc_html__( 'Florida', 'the-events-calendar' ),
				'GA' => esc_html__( 'Georgia', 'the-events-calendar' ),
				'HI' => esc_html__( 'Hawaii', 'the-events-calendar' ),
				'ID' => esc_html__( 'Idaho', 'the-events-calendar' ),
				'IL' => esc_html__( 'Illinois', 'the-events-calendar' ),
				'IN' => esc_html__( 'Indiana', 'the-events-calendar' ),
				'IA' => esc_html__( 'Iowa', 'the-events-calendar' ),
				'KS' => esc_html__( 'Kansas', 'the-events-calendar' ),
				'KY' => esc_html__( 'Kentucky', 'the-events-calendar' ),
				'LA' => esc_html__( 'Louisiana', 'the-events-calendar' ),
				'ME' => esc_html__( 'Maine', 'the-events-calendar' ),
				'MD' => esc_html__( 'Maryland', 'the-events-calendar' ),
				'MA' => esc_html__( 'Massachusetts', 'the-events-calendar' ),
				'MI' => esc_html__( 'Michigan', 'the-events-calendar' ),
				'MN' => esc_html__( 'Minnesota', 'the-events-calendar' ),
				'MS' => esc_html__( 'Mississippi', 'the-events-calendar' ),
				'MO' => esc_html__( 'Missouri', 'the-events-calendar' ),
				'MT' => esc_html__( 'Montana', 'the-events-calendar' ),
				'NE' => esc_html__( 'Nebraska', 'the-events-calendar' ),
				'NV' => esc_html__( 'Nevada', 'the-events-calendar' ),
				'NH' => esc_html__( 'New Hampshire', 'the-events-calendar' ),
				'NJ' => esc_html__( 'New Jersey', 'the-events-calendar' ),
				'NM' => esc_html__( 'New Mexico', 'the-events-calendar' ),
				'NY' => esc_html__( 'New York', 'the-events-calendar' ),
				'NC' => esc_html__( 'North Carolina', 'the-events-calendar' ),
				'ND' => esc_html__( 'North Dakota', 'the-events-calendar' ),
				'OH' => esc_html__( 'Ohio', 'the-events-calendar' ),
				'OK' => esc_html__( 'Oklahoma', 'the-events-calendar' ),
				'OR' => esc_html__( 'Oregon', 'the-events-calendar' ),
				'PA' => esc_html__( 'Pennsylvania', 'the-events-calendar' ),
				'RI' => esc_html__( 'Rhode Island', 'the-events-calendar' ),
				'SC' => esc_html__( 'South Carolina', 'the-events-calendar' ),
				'SD' => esc_html__( 'South Dakota', 'the-events-calendar' ),
				'TN' => esc_html__( 'Tennessee', 'the-events-calendar' ),
				'TX' => esc_html__( 'Texas', 'the-events-calendar' ),
				'UT' => esc_html__( 'Utah', 'the-events-calendar' ),
				'VT' => esc_html__( 'Vermont', 'the-events-calendar' ),
				'VA' => esc_html__( 'Virginia', 'the-events-calendar' ),
				'WA' => esc_html__( 'Washington', 'the-events-calendar' ),
				'WV' => esc_html__( 'West Virginia', 'the-events-calendar' ),
				'WI' => esc_html__( 'Wisconsin', 'the-events-calendar' ),
				'WY' => esc_html__( 'Wyoming', 'the-events-calendar' ),
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
			$options = '';

			if ( empty( $date ) ) {
				$minute = '00';
			} else {
				$minute = date( 'i', strtotime( $date ) );
			}

			$minute = apply_filters( 'tribe_get_minute_options', $minute, $date, $isStart );
			$minutes = self::minutes( $minute );

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
		 * @param  int $exact_minute optionally specify an exact minute to be included (outwith the default intervals)
		 *
		 * @return array The minutes array.
		 */
		private static function minutes( $exact_minute = 0 ) {
			$minutes = array();

			// The exact minute should be an absint between 0 and 59
			$exact_minute = absint( $exact_minute );

			if ( $exact_minute < 0 || $exact_minute > 59 ) {
				$exact_minute = 0;
			}

			/**
			 * Filters the amount of minutes to increment the minutes drop-down by
			 *
			 * @param int Increment amount (defaults to 5)
			 */
			$default_increment = apply_filters( 'tribe_minutes_increment', 5 );

			// Unless an exact minute has been specified we can minimize the amount of looping we do
			$increment = ( 0 === $exact_minute ) ? $default_increment : 1;

			for ( $minute = 0; $minute < 60; $minute += $increment ) {
				// Skip if this $minute doesn't meet the increment pattern and isn't an additional exact minute
				if ( 0 !== $minute % $default_increment && $exact_minute !== $minute ) {
					continue;
				}

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
