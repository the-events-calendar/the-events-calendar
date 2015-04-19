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
					"" => __( "Select a Country:", 'tribe-events-calendar' )
				);

				$country_rows = explode( "\n", tribe_get_option( 'tribeEventsCountries' ) );
				foreach ( $country_rows as $crow ) {
					$country = explode( ",", $crow );
					if ( isset( $country[0] ) && isset( $country[1] ) ) {
						$country[0] = trim( $country[0] );
						$country[1] = trim( $country[1] );

						if ( $country[0] && $country[1] ) {
							$countries[$country[0]] = $country[1];
						}
					}
				}
			}

			if ( ! isset( $countries ) || ! is_array( $countries ) || count( $countries ) == 1 ) {
				$countries = array(
					""   => __( "Select a Country:", 'tribe-events-calendar' ),
					"US" => __( "United States", 'tribe-events-calendar' ),
					"AF" => __( "Afghanistan", 'tribe-events-calendar' ),
					"AL" => __( "Albania", 'tribe-events-calendar' ),
					"DZ" => __( "Algeria", 'tribe-events-calendar' ),
					"AS" => __( "American Samoa", 'tribe-events-calendar' ),
					"AD" => __( "Andorra", 'tribe-events-calendar' ),
					"AO" => __( "Angola", 'tribe-events-calendar' ),
					"AI" => __( "Anguilla", 'tribe-events-calendar' ),
					"AQ" => __( "Antarctica", 'tribe-events-calendar' ),
					"AG" => __( "Antigua And Barbuda", 'tribe-events-calendar' ),
					"AR" => __( "Argentina", 'tribe-events-calendar' ),
					"AM" => __( "Armenia", 'tribe-events-calendar' ),
					"AW" => __( "Aruba", 'tribe-events-calendar' ),
					"AU" => __( "Australia", 'tribe-events-calendar' ),
					"AT" => __( "Austria", 'tribe-events-calendar' ),
					"AZ" => __( "Azerbaijan", 'tribe-events-calendar' ),
					"BS" => __( "Bahamas", 'tribe-events-calendar' ),
					"BH" => __( "Bahrain", 'tribe-events-calendar' ),
					"BD" => __( "Bangladesh", 'tribe-events-calendar' ),
					"BB" => __( "Barbados", 'tribe-events-calendar' ),
					"BY" => __( "Belarus", 'tribe-events-calendar' ),
					"BE" => __( "Belgium", 'tribe-events-calendar' ),
					"BZ" => __( "Belize", 'tribe-events-calendar' ),
					"BJ" => __( "Benin", 'tribe-events-calendar' ),
					"BM" => __( "Bermuda", 'tribe-events-calendar' ),
					"BT" => __( "Bhutan", 'tribe-events-calendar' ),
					"BO" => __( "Bolivia", 'tribe-events-calendar' ),
					"BA" => __( "Bosnia And Herzegowina", 'tribe-events-calendar' ),
					"BW" => __( "Botswana", 'tribe-events-calendar' ),
					"BV" => __( "Bouvet Island", 'tribe-events-calendar' ),
					"BR" => __( "Brazil", 'tribe-events-calendar' ),
					"IO" => __( "British Indian Ocean Territory", 'tribe-events-calendar' ),
					"BN" => __( "Brunei Darussalam", 'tribe-events-calendar' ),
					"BG" => __( "Bulgaria", 'tribe-events-calendar' ),
					"BF" => __( "Burkina Faso", 'tribe-events-calendar' ),
					"BI" => __( "Burundi", 'tribe-events-calendar' ),
					"KH" => __( "Cambodia", 'tribe-events-calendar' ),
					"CM" => __( "Cameroon", 'tribe-events-calendar' ),
					"CA" => __( "Canada", 'tribe-events-calendar' ),
					"CV" => __( "Cape Verde", 'tribe-events-calendar' ),
					"KY" => __( "Cayman Islands", 'tribe-events-calendar' ),
					"CF" => __( "Central African Republic", 'tribe-events-calendar' ),
					"TD" => __( "Chad", 'tribe-events-calendar' ),
					"CL" => __( "Chile", 'tribe-events-calendar' ),
					"CN" => __( "China", 'tribe-events-calendar' ),
					"CX" => __( "Christmas Island", 'tribe-events-calendar' ),
					"CC" => __( "Cocos (Keeling) Islands", 'tribe-events-calendar' ),
					"CO" => __( "Colombia", 'tribe-events-calendar' ),
					"KM" => __( "Comoros", 'tribe-events-calendar' ),
					"CG" => __( "Congo", 'tribe-events-calendar' ),
					"CD" => __( "Congo, The Democratic Republic Of The", 'tribe-events-calendar' ),
					"CK" => __( "Cook Islands", 'tribe-events-calendar' ),
					"CR" => __( "Costa Rica", 'tribe-events-calendar' ),
					"CI" => __( "Cote D'Ivoire", 'tribe-events-calendar' ),
					"HR" => __( "Croatia (Local Name: Hrvatska)", 'tribe-events-calendar' ),
					"CU" => __( "Cuba", 'tribe-events-calendar' ),
					"CY" => __( "Cyprus", 'tribe-events-calendar' ),
					"CZ" => __( "Czech Republic", 'tribe-events-calendar' ),
					"DK" => __( "Denmark", 'tribe-events-calendar' ),
					"DJ" => __( "Djibouti", 'tribe-events-calendar' ),
					"DM" => __( "Dominica", 'tribe-events-calendar' ),
					"DO" => __( "Dominican Republic", 'tribe-events-calendar' ),
					"TP" => __( "East Timor", 'tribe-events-calendar' ),
					"EC" => __( "Ecuador", 'tribe-events-calendar' ),
					"EG" => __( "Egypt", 'tribe-events-calendar' ),
					"SV" => __( "El Salvador", 'tribe-events-calendar' ),
					"GQ" => __( "Equatorial Guinea", 'tribe-events-calendar' ),
					"ER" => __( "Eritrea", 'tribe-events-calendar' ),
					"EE" => __( "Estonia", 'tribe-events-calendar' ),
					"ET" => __( "Ethiopia", 'tribe-events-calendar' ),
					"FK" => __( "Falkland Islands (Malvinas)", 'tribe-events-calendar' ),
					"FO" => __( "Faroe Islands", 'tribe-events-calendar' ),
					"FJ" => __( "Fiji", 'tribe-events-calendar' ),
					"FI" => __( "Finland", 'tribe-events-calendar' ),
					"FR" => __( "France", 'tribe-events-calendar' ),
					"FX" => __( "France, Metropolitan", 'tribe-events-calendar' ),
					"GF" => __( "French Guiana", 'tribe-events-calendar' ),
					"PF" => __( "French Polynesia", 'tribe-events-calendar' ),
					"TF" => __( "French Southern Territories", 'tribe-events-calendar' ),
					"GA" => __( "Gabon", 'tribe-events-calendar' ),
					"GM" => __( "Gambia", 'tribe-events-calendar' ),
					"GE" => __( "Georgia", 'tribe-events-calendar' ),
					"DE" => __( "Germany", 'tribe-events-calendar' ),
					"GH" => __( "Ghana", 'tribe-events-calendar' ),
					"GI" => __( "Gibraltar", 'tribe-events-calendar' ),
					"GR" => __( "Greece", 'tribe-events-calendar' ),
					"GL" => __( "Greenland", 'tribe-events-calendar' ),
					"GD" => __( "Grenada", 'tribe-events-calendar' ),
					"GP" => __( "Guadeloupe", 'tribe-events-calendar' ),
					"GU" => __( "Guam", 'tribe-events-calendar' ),
					"GT" => __( "Guatemala", 'tribe-events-calendar' ),
					"GN" => __( "Guinea", 'tribe-events-calendar' ),
					"GW" => __( "Guinea-Bissau", 'tribe-events-calendar' ),
					"GY" => __( "Guyana", 'tribe-events-calendar' ),
					"HT" => __( "Haiti", 'tribe-events-calendar' ),
					"HM" => __( "Heard And Mc Donald Islands", 'tribe-events-calendar' ),
					"VA" => __( "Holy See (Vatican City State)", 'tribe-events-calendar' ),
					"HN" => __( "Honduras", 'tribe-events-calendar' ),
					"HK" => __( "Hong Kong", 'tribe-events-calendar' ),
					"HU" => __( "Hungary", 'tribe-events-calendar' ),
					"IS" => __( "Iceland", 'tribe-events-calendar' ),
					"IN" => __( "India", 'tribe-events-calendar' ),
					"ID" => __( "Indonesia", 'tribe-events-calendar' ),
					"IR" => __( "Iran (Islamic Republic Of)", 'tribe-events-calendar' ),
					"IQ" => __( "Iraq", 'tribe-events-calendar' ),
					"IE" => __( "Ireland", 'tribe-events-calendar' ),
					"IL" => __( "Israel", 'tribe-events-calendar' ),
					"IT" => __( "Italy", 'tribe-events-calendar' ),
					"JM" => __( "Jamaica", 'tribe-events-calendar' ),
					"JP" => __( "Japan", 'tribe-events-calendar' ),
					"JO" => __( "Jordan", 'tribe-events-calendar' ),
					"KZ" => __( "Kazakhstan", 'tribe-events-calendar' ),
					"KE" => __( "Kenya", 'tribe-events-calendar' ),
					"KI" => __( "Kiribati", 'tribe-events-calendar' ),
					"KP" => __( "Korea, Democratic People's Republic Of", 'tribe-events-calendar' ),
					"KR" => __( "Korea, Republic Of", 'tribe-events-calendar' ),
					"KW" => __( "Kuwait", 'tribe-events-calendar' ),
					"KG" => __( "Kyrgyzstan", 'tribe-events-calendar' ),
					"LA" => __( "Lao People's Democratic Republic", 'tribe-events-calendar' ),
					"LV" => __( "Latvia", 'tribe-events-calendar' ),
					"LB" => __( "Lebanon", 'tribe-events-calendar' ),
					"LS" => __( "Lesotho", 'tribe-events-calendar' ),
					"LR" => __( "Liberia", 'tribe-events-calendar' ),
					"LY" => __( "Libya", 'tribe-events-calendar' ),
					"LI" => __( "Liechtenstein", 'tribe-events-calendar' ),
					"LT" => __( "Lithuania", 'tribe-events-calendar' ),
					"LU" => __( "Luxembourg", 'tribe-events-calendar' ),
					"MO" => __( "Macau", 'tribe-events-calendar' ),
					"MK" => __( "Macedonia", 'tribe-events-calendar' ),
					"MG" => __( "Madagascar", 'tribe-events-calendar' ),
					"MW" => __( "Malawi", 'tribe-events-calendar' ),
					"MY" => __( "Malaysia", 'tribe-events-calendar' ),
					"MV" => __( "Maldives", 'tribe-events-calendar' ),
					"ML" => __( "Mali", 'tribe-events-calendar' ),
					"MT" => __( "Malta", 'tribe-events-calendar' ),
					"MH" => __( "Marshall Islands", 'tribe-events-calendar' ),
					"MQ" => __( "Martinique", 'tribe-events-calendar' ),
					"MR" => __( "Mauritania", 'tribe-events-calendar' ),
					"MU" => __( "Mauritius", 'tribe-events-calendar' ),
					"YT" => __( "Mayotte", 'tribe-events-calendar' ),
					"MX" => __( "Mexico", 'tribe-events-calendar' ),
					"FM" => __( "Micronesia, Federated States Of", 'tribe-events-calendar' ),
					"MD" => __( "Moldova, Republic Of", 'tribe-events-calendar' ),
					"MC" => __( "Monaco", 'tribe-events-calendar' ),
					"MN" => __( "Mongolia", 'tribe-events-calendar' ),
					"ME" => __( "Montenegro", 'tribe-events-calendar' ),
					"MS" => __( "Montserrat", 'tribe-events-calendar' ),
					"MA" => __( "Morocco", 'tribe-events-calendar' ),
					"MZ" => __( "Mozambique", 'tribe-events-calendar' ),
					"MM" => __( "Myanmar", 'tribe-events-calendar' ),
					"NA" => __( "Namibia", 'tribe-events-calendar' ),
					"NR" => __( "Nauru", 'tribe-events-calendar' ),
					"NP" => __( "Nepal", 'tribe-events-calendar' ),
					"NL" => __( "Netherlands", 'tribe-events-calendar' ),
					"AN" => __( "Netherlands Antilles", 'tribe-events-calendar' ),
					"NC" => __( "New Caledonia", 'tribe-events-calendar' ),
					"NZ" => __( "New Zealand", 'tribe-events-calendar' ),
					"NI" => __( "Nicaragua", 'tribe-events-calendar' ),
					"NE" => __( "Niger", 'tribe-events-calendar' ),
					"NG" => __( "Nigeria", 'tribe-events-calendar' ),
					"NU" => __( "Niue", 'tribe-events-calendar' ),
					"NF" => __( "Norfolk Island", 'tribe-events-calendar' ),
					"MP" => __( "Northern Mariana Islands", 'tribe-events-calendar' ),
					"NO" => __( "Norway", 'tribe-events-calendar' ),
					"OM" => __( "Oman", 'tribe-events-calendar' ),
					"PK" => __( "Pakistan", 'tribe-events-calendar' ),
					"PW" => __( "Palau", 'tribe-events-calendar' ),
					"PA" => __( "Panama", 'tribe-events-calendar' ),
					"PG" => __( "Papua New Guinea", 'tribe-events-calendar' ),
					"PY" => __( "Paraguay", 'tribe-events-calendar' ),
					"PE" => __( "Peru", 'tribe-events-calendar' ),
					"PH" => __( "Philippines", 'tribe-events-calendar' ),
					"PN" => __( "Pitcairn", 'tribe-events-calendar' ),
					"PL" => __( "Poland", 'tribe-events-calendar' ),
					"PT" => __( "Portugal", 'tribe-events-calendar' ),
					"PR" => __( "Puerto Rico", 'tribe-events-calendar' ),
					"QA" => __( "Qatar", 'tribe-events-calendar' ),
					"RE" => __( "Reunion", 'tribe-events-calendar' ),
					"RO" => __( "Romania", 'tribe-events-calendar' ),
					"RU" => __( "Russian Federation", 'tribe-events-calendar' ),
					"RW" => __( "Rwanda", 'tribe-events-calendar' ),
					"KN" => __( "Saint Kitts And Nevis", 'tribe-events-calendar' ),
					"LC" => __( "Saint Lucia", 'tribe-events-calendar' ),
					"VC" => __( "Saint Vincent And The Grenadines", 'tribe-events-calendar' ),
					"WS" => __( "Samoa", 'tribe-events-calendar' ),
					"SM" => __( "San Marino", 'tribe-events-calendar' ),
					"ST" => __( "Sao Tome And Principe", 'tribe-events-calendar' ),
					"SA" => __( "Saudi Arabia", 'tribe-events-calendar' ),
					"SN" => __( "Senegal", 'tribe-events-calendar' ),
					"RS" => __( "Serbia", 'tribe-events-calendar' ),
					"SC" => __( "Seychelles", 'tribe-events-calendar' ),
					"SL" => __( "Sierra Leone", 'tribe-events-calendar' ),
					"SG" => __( "Singapore", 'tribe-events-calendar' ),
					"SK" => __( "Slovakia (Slovak Republic)", 'tribe-events-calendar' ),
					"SI" => __( "Slovenia", 'tribe-events-calendar' ),
					"SB" => __( "Solomon Islands", 'tribe-events-calendar' ),
					"SO" => __( "Somalia", 'tribe-events-calendar' ),
					"ZA" => __( "South Africa", 'tribe-events-calendar' ),
					"GS" => __( "South Georgia, South Sandwich Islands", 'tribe-events-calendar' ),
					"ES" => __( "Spain", 'tribe-events-calendar' ),
					"LK" => __( "Sri Lanka", 'tribe-events-calendar' ),
					"SH" => __( "St. Helena", 'tribe-events-calendar' ),
					"PM" => __( "St. Pierre And Miquelon", 'tribe-events-calendar' ),
					"SD" => __( "Sudan", 'tribe-events-calendar' ),
					"SR" => __( "Suriname", 'tribe-events-calendar' ),
					"SJ" => __( "Svalbard And Jan Mayen Islands", 'tribe-events-calendar' ),
					"SZ" => __( "Swaziland", 'tribe-events-calendar' ),
					"SE" => __( "Sweden", 'tribe-events-calendar' ),
					"CH" => __( "Switzerland", 'tribe-events-calendar' ),
					"SY" => __( "Syrian Arab Republic", 'tribe-events-calendar' ),
					"TW" => __( "Taiwan", 'tribe-events-calendar' ),
					"TJ" => __( "Tajikistan", 'tribe-events-calendar' ),
					"TZ" => __( "Tanzania, United Republic Of", 'tribe-events-calendar' ),
					"TH" => __( "Thailand", 'tribe-events-calendar' ),
					"TG" => __( "Togo", 'tribe-events-calendar' ),
					"TK" => __( "Tokelau", 'tribe-events-calendar' ),
					"TO" => __( "Tonga", 'tribe-events-calendar' ),
					"TT" => __( "Trinidad And Tobago", 'tribe-events-calendar' ),
					"TN" => __( "Tunisia", 'tribe-events-calendar' ),
					"TR" => __( "Turkey", 'tribe-events-calendar' ),
					"TM" => __( "Turkmenistan", 'tribe-events-calendar' ),
					"TC" => __( "Turks And Caicos Islands", 'tribe-events-calendar' ),
					"TV" => __( "Tuvalu", 'tribe-events-calendar' ),
					"UG" => __( "Uganda", 'tribe-events-calendar' ),
					"UA" => __( "Ukraine", 'tribe-events-calendar' ),
					"AE" => __( "United Arab Emirates", 'tribe-events-calendar' ),
					"GB" => __( "United Kingdom", 'tribe-events-calendar' ),
					"UM" => __( "United States Minor Outlying Islands", 'tribe-events-calendar' ),
					"UY" => __( "Uruguay", 'tribe-events-calendar' ),
					"UZ" => __( "Uzbekistan", 'tribe-events-calendar' ),
					"VU" => __( "Vanuatu", 'tribe-events-calendar' ),
					"VE" => __( "Venezuela", 'tribe-events-calendar' ),
					"VN" => __( "Viet Nam", 'tribe-events-calendar' ),
					"VG" => __( "Virgin Islands (British)", 'tribe-events-calendar' ),
					"VI" => __( "Virgin Islands (U.S.)", 'tribe-events-calendar' ),
					"WF" => __( "Wallis And Futuna Islands", 'tribe-events-calendar' ),
					"EH" => __( "Western Sahara", 'tribe-events-calendar' ),
					"YE" => __( "Yemen", 'tribe-events-calendar' ),
					"ZM" => __( "Zambia", 'tribe-events-calendar' ),
					"ZW" => __( "Zimbabwe", 'tribe-events-calendar' )
				);
			}
			if ( ( $postId || $useDefault ) ) {
				$countryValue = get_post_meta( $postId, '_EventCountry', true );
				if ( $countryValue ) {
					$defaultCountry = array( array_search( $countryValue, $countries ), $countryValue );
				} else {
					$defaultCountry = tribe_get_default_value( 'country' );
				}
				if ( $defaultCountry && $defaultCountry[0] != "" ) {
					$selectCountry = array_shift( $countries );
					asort( $countries );
					$countries = array( $defaultCountry[0] => __( $defaultCountry[1], 'tribe-events-calendar' ) ) + $countries;
					$countries = array( "" => __( $selectCountry, 'tribe-events-calendar' ) ) + $countries;
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
				"AL" => __( "Alabama", 'tribe-events-calendar' ),
				"AK" => __( "Alaska", 'tribe-events-calendar' ),
				"AZ" => __( "Arizona", 'tribe-events-calendar' ),
				"AR" => __( "Arkansas", 'tribe-events-calendar' ),
				"CA" => __( "California", 'tribe-events-calendar' ),
				"CO" => __( "Colorado", 'tribe-events-calendar' ),
				"CT" => __( "Connecticut", 'tribe-events-calendar' ),
				"DE" => __( "Delaware", 'tribe-events-calendar' ),
				"DC" => __( "District of Columbia", 'tribe-events-calendar' ),
				"FL" => __( "Florida", 'tribe-events-calendar' ),
				"GA" => __( "Georgia", 'tribe-events-calendar' ),
				"HI" => __( "Hawaii", 'tribe-events-calendar' ),
				"ID" => __( "Idaho", 'tribe-events-calendar' ),
				"IL" => __( "Illinois", 'tribe-events-calendar' ),
				"IN" => __( "Indiana", 'tribe-events-calendar' ),
				"IA" => __( "Iowa", 'tribe-events-calendar' ),
				"KS" => __( "Kansas", 'tribe-events-calendar' ),
				"KY" => __( "Kentucky", 'tribe-events-calendar' ),
				"LA" => __( "Louisiana", 'tribe-events-calendar' ),
				"ME" => __( "Maine", 'tribe-events-calendar' ),
				"MD" => __( "Maryland", 'tribe-events-calendar' ),
				"MA" => __( "Massachusetts", 'tribe-events-calendar' ),
				"MI" => __( "Michigan", 'tribe-events-calendar' ),
				"MN" => __( "Minnesota", 'tribe-events-calendar' ),
				"MS" => __( "Mississippi", 'tribe-events-calendar' ),
				"MO" => __( "Missouri", 'tribe-events-calendar' ),
				"MT" => __( "Montana", 'tribe-events-calendar' ),
				"NE" => __( "Nebraska", 'tribe-events-calendar' ),
				"NV" => __( "Nevada", 'tribe-events-calendar' ),
				"NH" => __( "New Hampshire", 'tribe-events-calendar' ),
				"NJ" => __( "New Jersey", 'tribe-events-calendar' ),
				"NM" => __( "New Mexico", 'tribe-events-calendar' ),
				"NY" => __( "New York", 'tribe-events-calendar' ),
				"NC" => __( "North Carolina", 'tribe-events-calendar' ),
				"ND" => __( "North Dakota", 'tribe-events-calendar' ),
				"OH" => __( "Ohio", 'tribe-events-calendar' ),
				"OK" => __( "Oklahoma", 'tribe-events-calendar' ),
				"OR" => __( "Oregon", 'tribe-events-calendar' ),
				"PA" => __( "Pennsylvania", 'tribe-events-calendar' ),
				"RI" => __( "Rhode Island", 'tribe-events-calendar' ),
				"SC" => __( "South Carolina", 'tribe-events-calendar' ),
				"SD" => __( "South Dakota", 'tribe-events-calendar' ),
				"TN" => __( "Tennessee", 'tribe-events-calendar' ),
				"TX" => __( "Texas", 'tribe-events-calendar' ),
				"UT" => __( "Utah", 'tribe-events-calendar' ),
				"VT" => __( "Vermont", 'tribe-events-calendar' ),
				"VA" => __( "Virginia", 'tribe-events-calendar' ),
				"WA" => __( "Washington", 'tribe-events-calendar' ),
				"WV" => __( "West Virginia", 'tribe-events-calendar' ),
				"WI" => __( "Wisconsin", 'tribe-events-calendar' ),
				"WY" => __( "Wyoming", 'tribe-events-calendar' ),
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
		public static function getHourOptions( $date = "", $isStart = false ) {
			$hours = Tribe__Events__View_Helpers::hours();

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
		public static function getMinuteOptions( $date = "", $isStart = false ) {
			$minutes = Tribe__Events__View_Helpers::minutes();
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
					$hour = "0" . $hour;
				}
				$hours[$hour] = $hour;
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
					$minute = "0" . $minute;
				}
				$minutes[$minute] = $minute;
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
		public static function getMeridianOptions( $date = "", $isStart = false ) {
			if ( strstr( get_option( 'time_format', Tribe__Events__Date_Utils::TIMEFORMAT ), 'A' ) ) {
				$a         = 'A';
				$meridians = array( "AM", "PM" );
			} else {
				$a         = 'a';
				$meridians = array( "am", "pm" );
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
		 * Builds a set of options for displaying a month chooser
		 *
		 * @param string $date the current date to select  (optional)
		 *
		 * @return string a set of HTML options with all months (current month selected)
		 * @deprecated
		 * @todo remove in 3.10
		 */
		public static function getMonthOptions( $date = "" ) {
			_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '3.8' );
			$months    = Tribe__Events__Main::instance()->monthNames( true );
			$options   = '';
			if ( empty( $date ) ) {
				$month = ( date_i18n( 'j' ) == date_i18n( 't' ) ) ? date( 'F', time() + 86400 ) : date_i18n( 'F' );
			} else {
				$month = date( 'F', strtotime( $date ) );
			}
			$monthIndex = 1;
			foreach ( $months as $englishMonth => $monthText ) {
				if ( $monthIndex < 10 ) {
					$monthIndex = "0" . $monthIndex; // need a leading zero in the month
				}
				if ( $month == $englishMonth ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$options .= "<option value='$monthIndex' $selected>$monthText</option>\n";
				$monthIndex ++;
			}

			return $options;
		}

		/**
		 * Builds a set of options for displaying a day chooser
		 *
		 * @param string $date      the current date (optional)
		 * @param int    $totalDays number of days in the month
		 *
		 * @return string a set of HTML options with all days (current day selected)
		 * @deprecated
		 * @todo remove in 3.10
		 */
		public static function getDayOptions( $date = "", $totalDays = 31 ) {
			_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '3.8' );
			$days    = Tribe__Events__View_Helpers::days( $totalDays );
			$options = '';
			if ( empty( $date ) ) {
				$day = date_i18n( 'j' );
				if ( $day == date_i18n( 't' ) ) {
					$day = '01';
				} elseif ( $day < 9 ) {
					$day = '0' . ( $day + 1 );
				} else {
					$day ++;
				}
			} else {
				$day = date( 'd', strtotime( $date ) );
			}
			foreach ( $days as $dayText ) {
				if ( $dayText < 10 ) {
					$dayText = "0" . $dayText; // need a leading zero in the day
				}
				if ( $day == $dayText ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$options .= "<option value='$dayText' $selected>$dayText</option>\n";
			}

			return $options;
		}

		/**
		 * Builds a set of options for displaying a year chooser
		 *
		 * @param string $date the current date (optional)
		 *
		 * @return string a set of HTML options with adjacent years (current year selected)
		 * @deprecated
		 * @todo remove in 3.10
		 */
		public static function getYearOptions( $date = "" ) {
			_deprecated_function( __CLASS__ . '::' . __FUNCTION__ . '()', '3.8' );
			$years   = Tribe__Events__View_Helpers::years();
			$options = '';
			if ( empty( $date ) ) {
				$year = date_i18n( 'Y' );
				if ( date_i18n( 'n' ) == 12 && date_i18n( 'j' ) == 31 ) {
					$year ++;
				}
			} else {
				$year = date( 'Y', strtotime( $date ) );
			}
			foreach ( $years as $yearText ) {
				if ( $year == $yearText ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$options .= "<option value='$yearText' $selected>$yearText</option>\n";
			}

			return $options;
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
				$days[$day] = $day;
			}

			return $days;
		}
	}
}
