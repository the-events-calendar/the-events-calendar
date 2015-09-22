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
					'' => __( 'Select a Country:', 'the-events-calendar' ),
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
					''   => __( 'Select a Country:', 'the-events-calendar' ),
					'US' => __( 'United States', 'the-events-calendar' ),
					'AF' => __( 'Afghanistan', 'the-events-calendar' ),
					'AL' => __( 'Albania', 'the-events-calendar' ),
					'DZ' => __( 'Algeria', 'the-events-calendar' ),
					'AS' => __( 'American Samoa', 'the-events-calendar' ),
					'AD' => __( 'Andorra', 'the-events-calendar' ),
					'AO' => __( 'Angola', 'the-events-calendar' ),
					'AI' => __( 'Anguilla', 'the-events-calendar' ),
					'AQ' => __( 'Antarctica', 'the-events-calendar' ),
					'AG' => __( 'Antigua And Barbuda', 'the-events-calendar' ),
					'AR' => __( 'Argentina', 'the-events-calendar' ),
					'AM' => __( 'Armenia', 'the-events-calendar' ),
					'AW' => __( 'Aruba', 'the-events-calendar' ),
					'AU' => __( 'Australia', 'the-events-calendar' ),
					'AT' => __( 'Austria', 'the-events-calendar' ),
					'AZ' => __( 'Azerbaijan', 'the-events-calendar' ),
					'BS' => __( 'Bahamas', 'the-events-calendar' ),
					'BH' => __( 'Bahrain', 'the-events-calendar' ),
					'BD' => __( 'Bangladesh', 'the-events-calendar' ),
					'BB' => __( 'Barbados', 'the-events-calendar' ),
					'BY' => __( 'Belarus', 'the-events-calendar' ),
					'BE' => __( 'Belgium', 'the-events-calendar' ),
					'BZ' => __( 'Belize', 'the-events-calendar' ),
					'BJ' => __( 'Benin', 'the-events-calendar' ),
					'BM' => __( 'Bermuda', 'the-events-calendar' ),
					'BT' => __( 'Bhutan', 'the-events-calendar' ),
					'BO' => __( 'Bolivia', 'the-events-calendar' ),
					'BA' => __( 'Bosnia And Herzegowina', 'the-events-calendar' ),
					'BW' => __( 'Botswana', 'the-events-calendar' ),
					'BV' => __( 'Bouvet Island', 'the-events-calendar' ),
					'BR' => __( 'Brazil', 'the-events-calendar' ),
					'IO' => __( 'British Indian Ocean Territory', 'the-events-calendar' ),
					'BN' => __( 'Brunei Darussalam', 'the-events-calendar' ),
					'BG' => __( 'Bulgaria', 'the-events-calendar' ),
					'BF' => __( 'Burkina Faso', 'the-events-calendar' ),
					'BI' => __( 'Burundi', 'the-events-calendar' ),
					'KH' => __( 'Cambodia', 'the-events-calendar' ),
					'CM' => __( 'Cameroon', 'the-events-calendar' ),
					'CA' => __( 'Canada', 'the-events-calendar' ),
					'CV' => __( 'Cape Verde', 'the-events-calendar' ),
					'KY' => __( 'Cayman Islands', 'the-events-calendar' ),
					'CF' => __( 'Central African Republic', 'the-events-calendar' ),
					'TD' => __( 'Chad', 'the-events-calendar' ),
					'CL' => __( 'Chile', 'the-events-calendar' ),
					'CN' => __( 'China', 'the-events-calendar' ),
					'CX' => __( 'Christmas Island', 'the-events-calendar' ),
					'CC' => __( 'Cocos (Keeling) Islands', 'the-events-calendar' ),
					'CO' => __( 'Colombia', 'the-events-calendar' ),
					'KM' => __( 'Comoros', 'the-events-calendar' ),
					'CG' => __( 'Congo', 'the-events-calendar' ),
					'CD' => __( 'Congo, The Democratic Republic Of The', 'the-events-calendar' ),
					'CK' => __( 'Cook Islands', 'the-events-calendar' ),
					'CR' => __( 'Costa Rica', 'the-events-calendar' ),
					'CI' => __( "Cote D'Ivoire", 'the-events-calendar' ),
					'HR' => __( 'Croatia (Local Name: Hrvatska)', 'the-events-calendar' ),
					'CU' => __( 'Cuba', 'the-events-calendar' ),
					'CY' => __( 'Cyprus', 'the-events-calendar' ),
					'CZ' => __( 'Czech Republic', 'the-events-calendar' ),
					'DK' => __( 'Denmark', 'the-events-calendar' ),
					'DJ' => __( 'Djibouti', 'the-events-calendar' ),
					'DM' => __( 'Dominica', 'the-events-calendar' ),
					'DO' => __( 'Dominican Republic', 'the-events-calendar' ),
					'TP' => __( 'East Timor', 'the-events-calendar' ),
					'EC' => __( 'Ecuador', 'the-events-calendar' ),
					'EG' => __( 'Egypt', 'the-events-calendar' ),
					'SV' => __( 'El Salvador', 'the-events-calendar' ),
					'GQ' => __( 'Equatorial Guinea', 'the-events-calendar' ),
					'ER' => __( 'Eritrea', 'the-events-calendar' ),
					'EE' => __( 'Estonia', 'the-events-calendar' ),
					'ET' => __( 'Ethiopia', 'the-events-calendar' ),
					'FK' => __( 'Falkland Islands (Malvinas)', 'the-events-calendar' ),
					'FO' => __( 'Faroe Islands', 'the-events-calendar' ),
					'FJ' => __( 'Fiji', 'the-events-calendar' ),
					'FI' => __( 'Finland', 'the-events-calendar' ),
					'FR' => __( 'France', 'the-events-calendar' ),
					'FX' => __( 'France, Metropolitan', 'the-events-calendar' ),
					'GF' => __( 'French Guiana', 'the-events-calendar' ),
					'PF' => __( 'French Polynesia', 'the-events-calendar' ),
					'TF' => __( 'French Southern Territories', 'the-events-calendar' ),
					'GA' => __( 'Gabon', 'the-events-calendar' ),
					'GM' => __( 'Gambia', 'the-events-calendar' ),
					'GE' => __( 'Georgia', 'the-events-calendar' ),
					'DE' => __( 'Germany', 'the-events-calendar' ),
					'GH' => __( 'Ghana', 'the-events-calendar' ),
					'GI' => __( 'Gibraltar', 'the-events-calendar' ),
					'GR' => __( 'Greece', 'the-events-calendar' ),
					'GL' => __( 'Greenland', 'the-events-calendar' ),
					'GD' => __( 'Grenada', 'the-events-calendar' ),
					'GP' => __( 'Guadeloupe', 'the-events-calendar' ),
					'GU' => __( 'Guam', 'the-events-calendar' ),
					'GT' => __( 'Guatemala', 'the-events-calendar' ),
					'GN' => __( 'Guinea', 'the-events-calendar' ),
					'GW' => __( 'Guinea-Bissau', 'the-events-calendar' ),
					'GY' => __( 'Guyana', 'the-events-calendar' ),
					'HT' => __( 'Haiti', 'the-events-calendar' ),
					'HM' => __( 'Heard And Mc Donald Islands', 'the-events-calendar' ),
					'VA' => __( 'Holy See (Vatican City State)', 'the-events-calendar' ),
					'HN' => __( 'Honduras', 'the-events-calendar' ),
					'HK' => __( 'Hong Kong', 'the-events-calendar' ),
					'HU' => __( 'Hungary', 'the-events-calendar' ),
					'IS' => __( 'Iceland', 'the-events-calendar' ),
					'IN' => __( 'India', 'the-events-calendar' ),
					'ID' => __( 'Indonesia', 'the-events-calendar' ),
					'IR' => __( 'Iran (Islamic Republic Of)', 'the-events-calendar' ),
					'IQ' => __( 'Iraq', 'the-events-calendar' ),
					'IE' => __( 'Ireland', 'the-events-calendar' ),
					'IL' => __( 'Israel', 'the-events-calendar' ),
					'IT' => __( 'Italy', 'the-events-calendar' ),
					'JM' => __( 'Jamaica', 'the-events-calendar' ),
					'JP' => __( 'Japan', 'the-events-calendar' ),
					'JO' => __( 'Jordan', 'the-events-calendar' ),
					'KZ' => __( 'Kazakhstan', 'the-events-calendar' ),
					'KE' => __( 'Kenya', 'the-events-calendar' ),
					'KI' => __( 'Kiribati', 'the-events-calendar' ),
					'KP' => __( "Korea, Democratic People's Republic Of", 'the-events-calendar' ),
					'KR' => __( 'Korea, Republic Of', 'the-events-calendar' ),
					'KW' => __( 'Kuwait', 'the-events-calendar' ),
					'KG' => __( 'Kyrgyzstan', 'the-events-calendar' ),
					'LA' => __( "Lao People's Democratic Republic", 'the-events-calendar' ),
					'LV' => __( 'Latvia', 'the-events-calendar' ),
					'LB' => __( 'Lebanon', 'the-events-calendar' ),
					'LS' => __( 'Lesotho', 'the-events-calendar' ),
					'LR' => __( 'Liberia', 'the-events-calendar' ),
					'LY' => __( 'Libya', 'the-events-calendar' ),
					'LI' => __( 'Liechtenstein', 'the-events-calendar' ),
					'LT' => __( 'Lithuania', 'the-events-calendar' ),
					'LU' => __( 'Luxembourg', 'the-events-calendar' ),
					'MO' => __( 'Macau', 'the-events-calendar' ),
					'MK' => __( 'Macedonia', 'the-events-calendar' ),
					'MG' => __( 'Madagascar', 'the-events-calendar' ),
					'MW' => __( 'Malawi', 'the-events-calendar' ),
					'MY' => __( 'Malaysia', 'the-events-calendar' ),
					'MV' => __( 'Maldives', 'the-events-calendar' ),
					'ML' => __( 'Mali', 'the-events-calendar' ),
					'MT' => __( 'Malta', 'the-events-calendar' ),
					'MH' => __( 'Marshall Islands', 'the-events-calendar' ),
					'MQ' => __( 'Martinique', 'the-events-calendar' ),
					'MR' => __( 'Mauritania', 'the-events-calendar' ),
					'MU' => __( 'Mauritius', 'the-events-calendar' ),
					'YT' => __( 'Mayotte', 'the-events-calendar' ),
					'MX' => __( 'Mexico', 'the-events-calendar' ),
					'FM' => __( 'Micronesia, Federated States Of', 'the-events-calendar' ),
					'MD' => __( 'Moldova, Republic Of', 'the-events-calendar' ),
					'MC' => __( 'Monaco', 'the-events-calendar' ),
					'MN' => __( 'Mongolia', 'the-events-calendar' ),
					'ME' => __( 'Montenegro', 'the-events-calendar' ),
					'MS' => __( 'Montserrat', 'the-events-calendar' ),
					'MA' => __( 'Morocco', 'the-events-calendar' ),
					'MZ' => __( 'Mozambique', 'the-events-calendar' ),
					'MM' => __( 'Myanmar', 'the-events-calendar' ),
					'NA' => __( 'Namibia', 'the-events-calendar' ),
					'NR' => __( 'Nauru', 'the-events-calendar' ),
					'NP' => __( 'Nepal', 'the-events-calendar' ),
					'NL' => __( 'Netherlands', 'the-events-calendar' ),
					'AN' => __( 'Netherlands Antilles', 'the-events-calendar' ),
					'NC' => __( 'New Caledonia', 'the-events-calendar' ),
					'NZ' => __( 'New Zealand', 'the-events-calendar' ),
					'NI' => __( 'Nicaragua', 'the-events-calendar' ),
					'NE' => __( 'Niger', 'the-events-calendar' ),
					'NG' => __( 'Nigeria', 'the-events-calendar' ),
					'NU' => __( 'Niue', 'the-events-calendar' ),
					'NF' => __( 'Norfolk Island', 'the-events-calendar' ),
					'MP' => __( 'Northern Mariana Islands', 'the-events-calendar' ),
					'NO' => __( 'Norway', 'the-events-calendar' ),
					'OM' => __( 'Oman', 'the-events-calendar' ),
					'PK' => __( 'Pakistan', 'the-events-calendar' ),
					'PW' => __( 'Palau', 'the-events-calendar' ),
					'PA' => __( 'Panama', 'the-events-calendar' ),
					'PG' => __( 'Papua New Guinea', 'the-events-calendar' ),
					'PY' => __( 'Paraguay', 'the-events-calendar' ),
					'PE' => __( 'Peru', 'the-events-calendar' ),
					'PH' => __( 'Philippines', 'the-events-calendar' ),
					'PN' => __( 'Pitcairn', 'the-events-calendar' ),
					'PL' => __( 'Poland', 'the-events-calendar' ),
					'PT' => __( 'Portugal', 'the-events-calendar' ),
					'PR' => __( 'Puerto Rico', 'the-events-calendar' ),
					'QA' => __( 'Qatar', 'the-events-calendar' ),
					'RE' => __( 'Reunion', 'the-events-calendar' ),
					'RO' => __( 'Romania', 'the-events-calendar' ),
					'RU' => __( 'Russian Federation', 'the-events-calendar' ),
					'RW' => __( 'Rwanda', 'the-events-calendar' ),
					'KN' => __( 'Saint Kitts And Nevis', 'the-events-calendar' ),
					'LC' => __( 'Saint Lucia', 'the-events-calendar' ),
					'VC' => __( 'Saint Vincent And The Grenadines', 'the-events-calendar' ),
					'WS' => __( 'Samoa', 'the-events-calendar' ),
					'SM' => __( 'San Marino', 'the-events-calendar' ),
					'ST' => __( 'Sao Tome And Principe', 'the-events-calendar' ),
					'SA' => __( 'Saudi Arabia', 'the-events-calendar' ),
					'SN' => __( 'Senegal', 'the-events-calendar' ),
					'RS' => __( 'Serbia', 'the-events-calendar' ),
					'SC' => __( 'Seychelles', 'the-events-calendar' ),
					'SL' => __( 'Sierra Leone', 'the-events-calendar' ),
					'SG' => __( 'Singapore', 'the-events-calendar' ),
					'SK' => __( 'Slovakia (Slovak Republic)', 'the-events-calendar' ),
					'SI' => __( 'Slovenia', 'the-events-calendar' ),
					'SB' => __( 'Solomon Islands', 'the-events-calendar' ),
					'SO' => __( 'Somalia', 'the-events-calendar' ),
					'ZA' => __( 'South Africa', 'the-events-calendar' ),
					'GS' => __( 'South Georgia, South Sandwich Islands', 'the-events-calendar' ),
					'ES' => __( 'Spain', 'the-events-calendar' ),
					'LK' => __( 'Sri Lanka', 'the-events-calendar' ),
					'SH' => __( 'St. Helena', 'the-events-calendar' ),
					'PM' => __( 'St. Pierre And Miquelon', 'the-events-calendar' ),
					'SD' => __( 'Sudan', 'the-events-calendar' ),
					'SR' => __( 'Suriname', 'the-events-calendar' ),
					'SJ' => __( 'Svalbard And Jan Mayen Islands', 'the-events-calendar' ),
					'SZ' => __( 'Swaziland', 'the-events-calendar' ),
					'SE' => __( 'Sweden', 'the-events-calendar' ),
					'CH' => __( 'Switzerland', 'the-events-calendar' ),
					'SY' => __( 'Syrian Arab Republic', 'the-events-calendar' ),
					'TW' => __( 'Taiwan', 'the-events-calendar' ),
					'TJ' => __( 'Tajikistan', 'the-events-calendar' ),
					'TZ' => __( 'Tanzania, United Republic Of', 'the-events-calendar' ),
					'TH' => __( 'Thailand', 'the-events-calendar' ),
					'TG' => __( 'Togo', 'the-events-calendar' ),
					'TK' => __( 'Tokelau', 'the-events-calendar' ),
					'TO' => __( 'Tonga', 'the-events-calendar' ),
					'TT' => __( 'Trinidad And Tobago', 'the-events-calendar' ),
					'TN' => __( 'Tunisia', 'the-events-calendar' ),
					'TR' => __( 'Turkey', 'the-events-calendar' ),
					'TM' => __( 'Turkmenistan', 'the-events-calendar' ),
					'TC' => __( 'Turks And Caicos Islands', 'the-events-calendar' ),
					'TV' => __( 'Tuvalu', 'the-events-calendar' ),
					'UG' => __( 'Uganda', 'the-events-calendar' ),
					'UA' => __( 'Ukraine', 'the-events-calendar' ),
					'AE' => __( 'United Arab Emirates', 'the-events-calendar' ),
					'GB' => __( 'United Kingdom', 'the-events-calendar' ),
					'UM' => __( 'United States Minor Outlying Islands', 'the-events-calendar' ),
					'UY' => __( 'Uruguay', 'the-events-calendar' ),
					'UZ' => __( 'Uzbekistan', 'the-events-calendar' ),
					'VU' => __( 'Vanuatu', 'the-events-calendar' ),
					'VE' => __( 'Venezuela', 'the-events-calendar' ),
					'VN' => __( 'Viet Nam', 'the-events-calendar' ),
					'VG' => __( 'Virgin Islands (British)', 'the-events-calendar' ),
					'VI' => __( 'Virgin Islands (U.S.)', 'the-events-calendar' ),
					'WF' => __( 'Wallis And Futuna Islands', 'the-events-calendar' ),
					'EH' => __( 'Western Sahara', 'the-events-calendar' ),
					'YE' => __( 'Yemen', 'the-events-calendar' ),
					'ZM' => __( 'Zambia', 'the-events-calendar' ),
					'ZW' => __( 'Zimbabwe', 'the-events-calendar' ),
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
				'AL' => __( 'Alabama', 'the-events-calendar' ),
				'AK' => __( 'Alaska', 'the-events-calendar' ),
				'AZ' => __( 'Arizona', 'the-events-calendar' ),
				'AR' => __( 'Arkansas', 'the-events-calendar' ),
				'CA' => __( 'California', 'the-events-calendar' ),
				'CO' => __( 'Colorado', 'the-events-calendar' ),
				'CT' => __( 'Connecticut', 'the-events-calendar' ),
				'DE' => __( 'Delaware', 'the-events-calendar' ),
				'DC' => __( 'District of Columbia', 'the-events-calendar' ),
				'FL' => __( 'Florida', 'the-events-calendar' ),
				'GA' => __( 'Georgia', 'the-events-calendar' ),
				'HI' => __( 'Hawaii', 'the-events-calendar' ),
				'ID' => __( 'Idaho', 'the-events-calendar' ),
				'IL' => __( 'Illinois', 'the-events-calendar' ),
				'IN' => __( 'Indiana', 'the-events-calendar' ),
				'IA' => __( 'Iowa', 'the-events-calendar' ),
				'KS' => __( 'Kansas', 'the-events-calendar' ),
				'KY' => __( 'Kentucky', 'the-events-calendar' ),
				'LA' => __( 'Louisiana', 'the-events-calendar' ),
				'ME' => __( 'Maine', 'the-events-calendar' ),
				'MD' => __( 'Maryland', 'the-events-calendar' ),
				'MA' => __( 'Massachusetts', 'the-events-calendar' ),
				'MI' => __( 'Michigan', 'the-events-calendar' ),
				'MN' => __( 'Minnesota', 'the-events-calendar' ),
				'MS' => __( 'Mississippi', 'the-events-calendar' ),
				'MO' => __( 'Missouri', 'the-events-calendar' ),
				'MT' => __( 'Montana', 'the-events-calendar' ),
				'NE' => __( 'Nebraska', 'the-events-calendar' ),
				'NV' => __( 'Nevada', 'the-events-calendar' ),
				'NH' => __( 'New Hampshire', 'the-events-calendar' ),
				'NJ' => __( 'New Jersey', 'the-events-calendar' ),
				'NM' => __( 'New Mexico', 'the-events-calendar' ),
				'NY' => __( 'New York', 'the-events-calendar' ),
				'NC' => __( 'North Carolina', 'the-events-calendar' ),
				'ND' => __( 'North Dakota', 'the-events-calendar' ),
				'OH' => __( 'Ohio', 'the-events-calendar' ),
				'OK' => __( 'Oklahoma', 'the-events-calendar' ),
				'OR' => __( 'Oregon', 'the-events-calendar' ),
				'PA' => __( 'Pennsylvania', 'the-events-calendar' ),
				'RI' => __( 'Rhode Island', 'the-events-calendar' ),
				'SC' => __( 'South Carolina', 'the-events-calendar' ),
				'SD' => __( 'South Dakota', 'the-events-calendar' ),
				'TN' => __( 'Tennessee', 'the-events-calendar' ),
				'TX' => __( 'Texas', 'the-events-calendar' ),
				'UT' => __( 'Utah', 'the-events-calendar' ),
				'VT' => __( 'Vermont', 'the-events-calendar' ),
				'VA' => __( 'Virginia', 'the-events-calendar' ),
				'WA' => __( 'Washington', 'the-events-calendar' ),
				'WV' => __( 'West Virginia', 'the-events-calendar' ),
				'WI' => __( 'Wisconsin', 'the-events-calendar' ),
				'WY' => __( 'Wyoming', 'the-events-calendar' ),
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
			/**
			 * Filters the amount of minutes to increment the minutes drop-down by
			 *
			 * @param int Increment amount (defaults to 5)
			 */
			$increment = apply_filters( 'tribe_minutes_increment', 5 );
			for ( $minute = 0; $minute < 60; $minute += $increment ) {
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
