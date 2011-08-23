<?php
/**
 * Various helper methods used in views
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('TribeEventsViewHelpers')) {
	class TribeEventsViewHelpers {
		public static function constructCountries( $postId = '', $useDefault = true ) {
			$tribe_ecp = TribeEvents::instance();

			if(sp_get_option('spEventsCountries') != ''){
				$countries = array(
					"" => __("Select a Country:", TribeEvents::PLUGIN_DOMAIN)
					);

				$country_rows = explode("\n", sp_get_option('spEventsCountries'));
				foreach($country_rows as $crow){
					$country = explode(",", $crow);
					$country[0] = trim($country[0]);
					$country[1] = trim($country[1]);

					if($country[0] && $country[1]){
						$countries[$country[0]] = $country[1];
					}
				}
			}

			if(!isset($countries) || !is_array($countries) || count($countries) == 1)
				$countries = array(
					"" => __("Select a Country:", TribeEvents::PLUGIN_DOMAIN),
					"US" => __("United States", TribeEvents::PLUGIN_DOMAIN),
					"AF" => __("Afghanistan", TribeEvents::PLUGIN_DOMAIN),
					"AL" => __("Albania", TribeEvents::PLUGIN_DOMAIN),
					"DZ" => __("Algeria", TribeEvents::PLUGIN_DOMAIN),
					"AS" => __("American Samoa", TribeEvents::PLUGIN_DOMAIN),
					"AD" => __("Andorra", TribeEvents::PLUGIN_DOMAIN),
					"AO" => __("Angola", TribeEvents::PLUGIN_DOMAIN),
					"AI" => __("Anguilla", TribeEvents::PLUGIN_DOMAIN),
					"AQ" => __("Antarctica", TribeEvents::PLUGIN_DOMAIN),
					"AG" => __("Antigua And Barbuda", TribeEvents::PLUGIN_DOMAIN),
					"AR" => __("Argentina", TribeEvents::PLUGIN_DOMAIN),
					"AM" => __("Armenia", TribeEvents::PLUGIN_DOMAIN),
					"AW" => __("Aruba", TribeEvents::PLUGIN_DOMAIN),
					"AU" => __("Australia", TribeEvents::PLUGIN_DOMAIN),
					"AT" => __("Austria", TribeEvents::PLUGIN_DOMAIN),
					"AZ" => __("Azerbaijan", TribeEvents::PLUGIN_DOMAIN),
					"BS" => __("Bahamas", TribeEvents::PLUGIN_DOMAIN),
					"BH" => __("Bahrain", TribeEvents::PLUGIN_DOMAIN),
					"BD" => __("Bangladesh", TribeEvents::PLUGIN_DOMAIN),
					"BB" => __("Barbados", TribeEvents::PLUGIN_DOMAIN),
					"BY" => __("Belarus", TribeEvents::PLUGIN_DOMAIN),
					"BE" => __("Belgium", TribeEvents::PLUGIN_DOMAIN),
					"BZ" => __("Belize", TribeEvents::PLUGIN_DOMAIN),
					"BJ" => __("Benin", TribeEvents::PLUGIN_DOMAIN),
					"BM" => __("Bermuda", TribeEvents::PLUGIN_DOMAIN),
					"BT" => __("Bhutan", TribeEvents::PLUGIN_DOMAIN),
					"BO" => __("Bolivia", TribeEvents::PLUGIN_DOMAIN),
					"BA" => __("Bosnia And Herzegowina", TribeEvents::PLUGIN_DOMAIN),
					"BW" => __("Botswana", TribeEvents::PLUGIN_DOMAIN),
					"BV" => __("Bouvet Island", TribeEvents::PLUGIN_DOMAIN),
					"BR" => __("Brazil", TribeEvents::PLUGIN_DOMAIN),
					"IO" => __("British Indian Ocean Territory", TribeEvents::PLUGIN_DOMAIN),
					"BN" => __("Brunei Darussalam", TribeEvents::PLUGIN_DOMAIN),
					"BG" => __("Bulgaria", TribeEvents::PLUGIN_DOMAIN),
					"BF" => __("Burkina Faso", TribeEvents::PLUGIN_DOMAIN),
					"BI" => __("Burundi", TribeEvents::PLUGIN_DOMAIN),
					"KH" => __("Cambodia", TribeEvents::PLUGIN_DOMAIN),
					"CM" => __("Cameroon", TribeEvents::PLUGIN_DOMAIN),
					"CA" => __("Canada", TribeEvents::PLUGIN_DOMAIN),
					"CV" => __("Cape Verde", TribeEvents::PLUGIN_DOMAIN),
					"KY" => __("Cayman Islands", TribeEvents::PLUGIN_DOMAIN),
					"CF" => __("Central African Republic", TribeEvents::PLUGIN_DOMAIN),
					"TD" => __("Chad", TribeEvents::PLUGIN_DOMAIN),
					"CL" => __("Chile", TribeEvents::PLUGIN_DOMAIN),
					"CN" => __("China", TribeEvents::PLUGIN_DOMAIN),
					"CX" => __("Christmas Island", TribeEvents::PLUGIN_DOMAIN),
					"CC" => __("Cocos (Keeling) Islands", TribeEvents::PLUGIN_DOMAIN),
					"CO" => __("Colombia", TribeEvents::PLUGIN_DOMAIN),
					"KM" => __("Comoros", TribeEvents::PLUGIN_DOMAIN),
					"CG" => __("Congo", TribeEvents::PLUGIN_DOMAIN),
					"CD" => __("Congo, The Democratic Republic Of The", TribeEvents::PLUGIN_DOMAIN),
					"CK" => __("Cook Islands", TribeEvents::PLUGIN_DOMAIN),
					"CR" => __("Costa Rica", TribeEvents::PLUGIN_DOMAIN),
					"CI" => __("Cote D'Ivoire", TribeEvents::PLUGIN_DOMAIN),
					"HR" => __("Croatia (Local Name: Hrvatska)", TribeEvents::PLUGIN_DOMAIN),
					"CU" => __("Cuba", TribeEvents::PLUGIN_DOMAIN),
					"CY" => __("Cyprus", TribeEvents::PLUGIN_DOMAIN),
					"CZ" => __("Czech Republic", TribeEvents::PLUGIN_DOMAIN),
					"DK" => __("Denmark", TribeEvents::PLUGIN_DOMAIN),
					"DJ" => __("Djibouti", TribeEvents::PLUGIN_DOMAIN),
					"DM" => __("Dominica", TribeEvents::PLUGIN_DOMAIN),
					"DO" => __("Dominican Republic", TribeEvents::PLUGIN_DOMAIN),
					"TP" => __("East Timor", TribeEvents::PLUGIN_DOMAIN),
					"EC" => __("Ecuador", TribeEvents::PLUGIN_DOMAIN),
					"EG" => __("Egypt", TribeEvents::PLUGIN_DOMAIN),
					"SV" => __("El Salvador", TribeEvents::PLUGIN_DOMAIN),
					"GQ" => __("Equatorial Guinea", TribeEvents::PLUGIN_DOMAIN),
					"ER" => __("Eritrea", TribeEvents::PLUGIN_DOMAIN),
					"EE" => __("Estonia", TribeEvents::PLUGIN_DOMAIN),
					"ET" => __("Ethiopia", TribeEvents::PLUGIN_DOMAIN),
					"FK" => __("Falkland Islands (Malvinas)", TribeEvents::PLUGIN_DOMAIN),
					"FO" => __("Faroe Islands", TribeEvents::PLUGIN_DOMAIN),
					"FJ" => __("Fiji", TribeEvents::PLUGIN_DOMAIN),
					"FI" => __("Finland", TribeEvents::PLUGIN_DOMAIN),
					"FR" => __("France", TribeEvents::PLUGIN_DOMAIN),
					"FX" => __("France, Metropolitan", TribeEvents::PLUGIN_DOMAIN),
					"GF" => __("French Guiana", TribeEvents::PLUGIN_DOMAIN),
					"PF" => __("French Polynesia", TribeEvents::PLUGIN_DOMAIN),
					"TF" => __("French Southern Territories", TribeEvents::PLUGIN_DOMAIN),
					"GA" => __("Gabon", TribeEvents::PLUGIN_DOMAIN),
					"GM" => __("Gambia", TribeEvents::PLUGIN_DOMAIN),
					"GE" => __("Georgia", TribeEvents::PLUGIN_DOMAIN),
					"DE" => __("Germany", TribeEvents::PLUGIN_DOMAIN),
					"GH" => __("Ghana", TribeEvents::PLUGIN_DOMAIN),
					"GI" => __("Gibraltar", TribeEvents::PLUGIN_DOMAIN),
					"GR" => __("Greece", TribeEvents::PLUGIN_DOMAIN),
					"GL" => __("Greenland", TribeEvents::PLUGIN_DOMAIN),
					"GD" => __("Grenada", TribeEvents::PLUGIN_DOMAIN),
					"GP" => __("Guadeloupe", TribeEvents::PLUGIN_DOMAIN),
					"GU" => __("Guam", TribeEvents::PLUGIN_DOMAIN),
					"GT" => __("Guatemala", TribeEvents::PLUGIN_DOMAIN),
					"GN" => __("Guinea", TribeEvents::PLUGIN_DOMAIN),
					"GW" => __("Guinea-Bissau", TribeEvents::PLUGIN_DOMAIN),
					"GY" => __("Guyana", TribeEvents::PLUGIN_DOMAIN),
					"HT" => __("Haiti", TribeEvents::PLUGIN_DOMAIN),
					"HM" => __("Heard And Mc Donald Islands", TribeEvents::PLUGIN_DOMAIN),
					"VA" => __("Holy See (Vatican City State)", TribeEvents::PLUGIN_DOMAIN),
					"HN" => __("Honduras", TribeEvents::PLUGIN_DOMAIN),
					"HK" => __("Hong Kong", TribeEvents::PLUGIN_DOMAIN),
					"HU" => __("Hungary", TribeEvents::PLUGIN_DOMAIN),
					"IS" => __("Iceland", TribeEvents::PLUGIN_DOMAIN),
					"IN" => __("India", TribeEvents::PLUGIN_DOMAIN),
					"ID" => __("Indonesia", TribeEvents::PLUGIN_DOMAIN),
					"IR" => __("Iran (Islamic Republic Of)", TribeEvents::PLUGIN_DOMAIN),
					"IQ" => __("Iraq", TribeEvents::PLUGIN_DOMAIN),
					"IE" => __("Ireland", TribeEvents::PLUGIN_DOMAIN),
					"IL" => __("Israel", TribeEvents::PLUGIN_DOMAIN),
					"IT" => __("Italy", TribeEvents::PLUGIN_DOMAIN),
					"JM" => __("Jamaica", TribeEvents::PLUGIN_DOMAIN),
					"JP" => __("Japan", TribeEvents::PLUGIN_DOMAIN),
					"JO" => __("Jordan", TribeEvents::PLUGIN_DOMAIN),
					"KZ" => __("Kazakhstan", TribeEvents::PLUGIN_DOMAIN),
					"KE" => __("Kenya", TribeEvents::PLUGIN_DOMAIN),
					"KI" => __("Kiribati", TribeEvents::PLUGIN_DOMAIN),
					"KP" => __("Korea, Democratic People's Republic Of", TribeEvents::PLUGIN_DOMAIN),
					"KR" => __("Korea, Republic Of", TribeEvents::PLUGIN_DOMAIN),
					"KW" => __("Kuwait", TribeEvents::PLUGIN_DOMAIN),
					"KG" => __("Kyrgyzstan", TribeEvents::PLUGIN_DOMAIN),
					"LA" => __("Lao People's Democratic Republic", TribeEvents::PLUGIN_DOMAIN),
					"LV" => __("Latvia", TribeEvents::PLUGIN_DOMAIN),
					"LB" => __("Lebanon", TribeEvents::PLUGIN_DOMAIN),
					"LS" => __("Lesotho", TribeEvents::PLUGIN_DOMAIN),
					"LR" => __("Liberia", TribeEvents::PLUGIN_DOMAIN),
					"LY" => __("Libyan Arab Jamahiriya", TribeEvents::PLUGIN_DOMAIN),
					"LI" => __("Liechtenstein", TribeEvents::PLUGIN_DOMAIN),
					"LT" => __("Lithuania", TribeEvents::PLUGIN_DOMAIN),
					"LU" => __("Luxembourg", TribeEvents::PLUGIN_DOMAIN),
					"MO" => __("Macau", TribeEvents::PLUGIN_DOMAIN),
					"MK" => __("Macedonia, Former Yugoslav Republic Of", TribeEvents::PLUGIN_DOMAIN),
					"MG" => __("Madagascar", TribeEvents::PLUGIN_DOMAIN),
					"MW" => __("Malawi", TribeEvents::PLUGIN_DOMAIN),
					"MY" => __("Malaysia", TribeEvents::PLUGIN_DOMAIN),
					"MV" => __("Maldives", TribeEvents::PLUGIN_DOMAIN),
					"ML" => __("Mali", TribeEvents::PLUGIN_DOMAIN),
					"MT" => __("Malta", TribeEvents::PLUGIN_DOMAIN),
					"MH" => __("Marshall Islands", TribeEvents::PLUGIN_DOMAIN),
					"MQ" => __("Martinique", TribeEvents::PLUGIN_DOMAIN),
					"MR" => __("Mauritania", TribeEvents::PLUGIN_DOMAIN),
					"MU" => __("Mauritius", TribeEvents::PLUGIN_DOMAIN),
					"YT" => __("Mayotte", TribeEvents::PLUGIN_DOMAIN),
					"MX" => __("Mexico", TribeEvents::PLUGIN_DOMAIN),
					"FM" => __("Micronesia, Federated States Of", TribeEvents::PLUGIN_DOMAIN),
					"MD" => __("Moldova, Republic Of", TribeEvents::PLUGIN_DOMAIN),
					"MC" => __("Monaco", TribeEvents::PLUGIN_DOMAIN),
					"MN" => __("Mongolia", TribeEvents::PLUGIN_DOMAIN),
					"ME" => __("Montenegro", TribeEvents::PLUGIN_DOMAIN),
					"MS" => __("Montserrat", TribeEvents::PLUGIN_DOMAIN),
					"MA" => __("Morocco", TribeEvents::PLUGIN_DOMAIN),
					"MZ" => __("Mozambique", TribeEvents::PLUGIN_DOMAIN),
					"MM" => __("Myanmar", TribeEvents::PLUGIN_DOMAIN),
					"NA" => __("Namibia", TribeEvents::PLUGIN_DOMAIN),
					"NR" => __("Nauru", TribeEvents::PLUGIN_DOMAIN),
					"NP" => __("Nepal", TribeEvents::PLUGIN_DOMAIN),
					"NL" => __("Netherlands", TribeEvents::PLUGIN_DOMAIN),
					"AN" => __("Netherlands Antilles", TribeEvents::PLUGIN_DOMAIN),
					"NC" => __("New Caledonia", TribeEvents::PLUGIN_DOMAIN),
					"NZ" => __("New Zealand", TribeEvents::PLUGIN_DOMAIN),
					"NI" => __("Nicaragua", TribeEvents::PLUGIN_DOMAIN),
					"NE" => __("Niger", TribeEvents::PLUGIN_DOMAIN),
					"NG" => __("Nigeria", TribeEvents::PLUGIN_DOMAIN),
					"NU" => __("Niue", TribeEvents::PLUGIN_DOMAIN),
					"NF" => __("Norfolk Island", TribeEvents::PLUGIN_DOMAIN),
					"MP" => __("Northern Mariana Islands", TribeEvents::PLUGIN_DOMAIN),
					"NO" => __("Norway", TribeEvents::PLUGIN_DOMAIN),
					"OM" => __("Oman", TribeEvents::PLUGIN_DOMAIN),
					"PK" => __("Pakistan", TribeEvents::PLUGIN_DOMAIN),
					"PW" => __("Palau", TribeEvents::PLUGIN_DOMAIN),
					"PA" => __("Panama", TribeEvents::PLUGIN_DOMAIN),
					"PG" => __("Papua New Guinea", TribeEvents::PLUGIN_DOMAIN),
					"PY" => __("Paraguay", TribeEvents::PLUGIN_DOMAIN),
					"PE" => __("Peru", TribeEvents::PLUGIN_DOMAIN),
					"PH" => __("Philippines", TribeEvents::PLUGIN_DOMAIN),
					"PN" => __("Pitcairn", TribeEvents::PLUGIN_DOMAIN),
					"PL" => __("Poland", TribeEvents::PLUGIN_DOMAIN),
					"PT" => __("Portugal", TribeEvents::PLUGIN_DOMAIN),
					"PR" => __("Puerto Rico", TribeEvents::PLUGIN_DOMAIN),
					"QA" => __("Qatar", TribeEvents::PLUGIN_DOMAIN),
					"RE" => __("Reunion", TribeEvents::PLUGIN_DOMAIN),
					"RO" => __("Romania", TribeEvents::PLUGIN_DOMAIN),
					"RU" => __("Russian Federation", TribeEvents::PLUGIN_DOMAIN),
					"RW" => __("Rwanda", TribeEvents::PLUGIN_DOMAIN),
					"KN" => __("Saint Kitts And Nevis", TribeEvents::PLUGIN_DOMAIN),
					"LC" => __("Saint Lucia", TribeEvents::PLUGIN_DOMAIN),
					"VC" => __("Saint Vincent And The Grenadines", TribeEvents::PLUGIN_DOMAIN),
					"WS" => __("Samoa", TribeEvents::PLUGIN_DOMAIN),
					"SM" => __("San Marino", TribeEvents::PLUGIN_DOMAIN),
					"ST" => __("Sao Tome And Principe", TribeEvents::PLUGIN_DOMAIN),
					"SA" => __("Saudi Arabia", TribeEvents::PLUGIN_DOMAIN),
					"SN" => __("Senegal", TribeEvents::PLUGIN_DOMAIN),
					"RS" => __("Serbia", TribeEvents::PLUGIN_DOMAIN),
					"SC" => __("Seychelles", TribeEvents::PLUGIN_DOMAIN),
					"SL" => __("Sierra Leone", TribeEvents::PLUGIN_DOMAIN),
					"SG" => __("Singapore", TribeEvents::PLUGIN_DOMAIN),
					"SK" => __("Slovakia (Slovak Republic)", TribeEvents::PLUGIN_DOMAIN),
					"SI" => __("Slovenia", TribeEvents::PLUGIN_DOMAIN),
					"SB" => __("Solomon Islands", TribeEvents::PLUGIN_DOMAIN),
					"SO" => __("Somalia", TribeEvents::PLUGIN_DOMAIN),
					"ZA" => __("South Africa", TribeEvents::PLUGIN_DOMAIN),
					"GS" => __("South Georgia, South Sandwich Islands", TribeEvents::PLUGIN_DOMAIN),
					"ES" => __("Spain", TribeEvents::PLUGIN_DOMAIN),
					"LK" => __("Sri Lanka", TribeEvents::PLUGIN_DOMAIN),
					"SH" => __("St. Helena", TribeEvents::PLUGIN_DOMAIN),
					"PM" => __("St. Pierre And Miquelon", TribeEvents::PLUGIN_DOMAIN),
					"SD" => __("Sudan", TribeEvents::PLUGIN_DOMAIN),
					"SR" => __("Suriname", TribeEvents::PLUGIN_DOMAIN),
					"SJ" => __("Svalbard And Jan Mayen Islands", TribeEvents::PLUGIN_DOMAIN),
					"SZ" => __("Swaziland", TribeEvents::PLUGIN_DOMAIN),
					"SE" => __("Sweden", TribeEvents::PLUGIN_DOMAIN),
					"CH" => __("Switzerland", TribeEvents::PLUGIN_DOMAIN),
					"SY" => __("Syrian Arab Republic", TribeEvents::PLUGIN_DOMAIN),
					"TW" => __("Taiwan", TribeEvents::PLUGIN_DOMAIN),
					"TJ" => __("Tajikistan", TribeEvents::PLUGIN_DOMAIN),
					"TZ" => __("Tanzania, United Republic Of", TribeEvents::PLUGIN_DOMAIN),
					"TH" => __("Thailand", TribeEvents::PLUGIN_DOMAIN),
					"TG" => __("Togo", TribeEvents::PLUGIN_DOMAIN),
					"TK" => __("Tokelau", TribeEvents::PLUGIN_DOMAIN),
					"TO" => __("Tonga", TribeEvents::PLUGIN_DOMAIN),
					"TT" => __("Trinidad And Tobago", TribeEvents::PLUGIN_DOMAIN),
					"TN" => __("Tunisia", TribeEvents::PLUGIN_DOMAIN),
					"TR" => __("Turkey", TribeEvents::PLUGIN_DOMAIN),
					"TM" => __("Turkmenistan", TribeEvents::PLUGIN_DOMAIN),
					"TC" => __("Turks And Caicos Islands", TribeEvents::PLUGIN_DOMAIN),
					"TV" => __("Tuvalu", TribeEvents::PLUGIN_DOMAIN),
					"UG" => __("Uganda", TribeEvents::PLUGIN_DOMAIN),
					"UA" => __("Ukraine", TribeEvents::PLUGIN_DOMAIN),
					"AE" => __("United Arab Emirates", TribeEvents::PLUGIN_DOMAIN),
					"GB" => __("United Kingdom", TribeEvents::PLUGIN_DOMAIN),
					"UM" => __("United States Minor Outlying Islands", TribeEvents::PLUGIN_DOMAIN),
					"UY" => __("Uruguay", TribeEvents::PLUGIN_DOMAIN),
					"UZ" => __("Uzbekistan", TribeEvents::PLUGIN_DOMAIN),
					"VU" => __("Vanuatu", TribeEvents::PLUGIN_DOMAIN),
					"VE" => __("Venezuela", TribeEvents::PLUGIN_DOMAIN),
					"VN" => __("Viet Nam", TribeEvents::PLUGIN_DOMAIN),
					"VG" => __("Virgin Islands (British)", TribeEvents::PLUGIN_DOMAIN),
					"VI" => __("Virgin Islands (U.S.)", TribeEvents::PLUGIN_DOMAIN),
					"WF" => __("Wallis And Futuna Islands", TribeEvents::PLUGIN_DOMAIN),
					"EH" => __("Western Sahara", TribeEvents::PLUGIN_DOMAIN),
					"YE" => __("Yemen", TribeEvents::PLUGIN_DOMAIN),
					"YU" => __("Yugoslavia", TribeEvents::PLUGIN_DOMAIN),
					"ZM" => __("Zambia", TribeEvents::PLUGIN_DOMAIN),
					"ZW" => __("Zimbabwe", TribeEvents::PLUGIN_DOMAIN)
					);
				if ( ($postId || $useDefault)) {
					$countryValue = get_post_meta( $postId, '_EventCountry', true );
					if( $countryValue ) $defaultCountry = array( array_search( $countryValue, $countries ), $countryValue );
					else $defaultCountry = $tribe_ecp->getOption('defaultCountry');
					if( $defaultCountry && $defaultCountry[0] != "") {
						$selectCountry = array_shift( $countries );
						asort($countries);
						$countries = array($defaultCountry[0] => __($defaultCountry[1], TribeEvents::PLUGIN_DOMAIN)) + $countries;
						$countries = array("" => __($selectCountry, TribeEvents::PLUGIN_DOMAIN)) + $countries;
						array_unique($countries);
					}
					return $countries;
				} else {
					return $countries;
				}
		}
	
		public static function loadStates() {
			return array("AL" => __("Alabama", TribeEvents::PLUGIN_DOMAIN),
				"AK" => __("Alaska", TribeEvents::PLUGIN_DOMAIN),
				"AZ" => __("Arizona", TribeEvents::PLUGIN_DOMAIN),
				"AR" => __("Arkansas", TribeEvents::PLUGIN_DOMAIN),
				"CA" => __("California", TribeEvents::PLUGIN_DOMAIN),
				"CO" => __("Colorado", TribeEvents::PLUGIN_DOMAIN),
				"CT" => __("Connecticut", TribeEvents::PLUGIN_DOMAIN),
				"DE" => __("Delaware", TribeEvents::PLUGIN_DOMAIN),
				"DC" => __("District of Columbia", TribeEvents::PLUGIN_DOMAIN),
				"FL" => __("Florida", TribeEvents::PLUGIN_DOMAIN),
				"GA" => __("Georgia", TribeEvents::PLUGIN_DOMAIN),
				"HI" => __("Hawaii", TribeEvents::PLUGIN_DOMAIN),
				"ID" => __("Idaho", TribeEvents::PLUGIN_DOMAIN),
				"IL" => __("Illinois", TribeEvents::PLUGIN_DOMAIN),
				"IN" => __("Indiana", TribeEvents::PLUGIN_DOMAIN),
				"IA" => __("Iowa", TribeEvents::PLUGIN_DOMAIN),
				"KS" => __("Kansas", TribeEvents::PLUGIN_DOMAIN),
				"KY" => __("Kentucky", TribeEvents::PLUGIN_DOMAIN),
				"LA" => __("Louisiana", TribeEvents::PLUGIN_DOMAIN),
				"ME" => __("Maine", TribeEvents::PLUGIN_DOMAIN),
				"MD" => __("Maryland", TribeEvents::PLUGIN_DOMAIN),
				"MA" => __("Massachusetts", TribeEvents::PLUGIN_DOMAIN),
				"MI" => __("Michigan", TribeEvents::PLUGIN_DOMAIN),
				"MN" => __("Minnesota", TribeEvents::PLUGIN_DOMAIN),
				"MS" => __("Mississippi", TribeEvents::PLUGIN_DOMAIN),
				"MO" => __("Missouri", TribeEvents::PLUGIN_DOMAIN),
				"MT" => __("Montana", TribeEvents::PLUGIN_DOMAIN),
				"NE" => __("Nebraska", TribeEvents::PLUGIN_DOMAIN),
				"NV" => __("Nevada", TribeEvents::PLUGIN_DOMAIN),
				"NH" => __("New Hampshire", TribeEvents::PLUGIN_DOMAIN),
				"NJ" => __("New Jersey", TribeEvents::PLUGIN_DOMAIN),
				"NM" => __("New Mexico", TribeEvents::PLUGIN_DOMAIN),
				"NY" => __("New York", TribeEvents::PLUGIN_DOMAIN),
				"NC" => __("North Carolina", TribeEvents::PLUGIN_DOMAIN),
				"ND" => __("North Dakota", TribeEvents::PLUGIN_DOMAIN),
				"OH" => __("Ohio", TribeEvents::PLUGIN_DOMAIN),
				"OK" => __("Oklahoma", TribeEvents::PLUGIN_DOMAIN),
				"OR" => __("Oregon", TribeEvents::PLUGIN_DOMAIN),
				"PA" => __("Pennsylvania", TribeEvents::PLUGIN_DOMAIN),
				"RI" => __("Rhode Island", TribeEvents::PLUGIN_DOMAIN),
				"SC" => __("South Carolina", TribeEvents::PLUGIN_DOMAIN),
				"SD" => __("South Dakota", TribeEvents::PLUGIN_DOMAIN),
				"TN" => __("Tennessee", TribeEvents::PLUGIN_DOMAIN),
				"TX" => __("Texas", TribeEvents::PLUGIN_DOMAIN),
				"UT" => __("Utah", TribeEvents::PLUGIN_DOMAIN),
				"VT" => __("Vermont", TribeEvents::PLUGIN_DOMAIN),
				"VA" => __("Virginia", TribeEvents::PLUGIN_DOMAIN),
				"WA" => __("Washington", TribeEvents::PLUGIN_DOMAIN),
				"WV" => __("West Virginia", TribeEvents::PLUGIN_DOMAIN),
				"WI" => __("Wisconsin", TribeEvents::PLUGIN_DOMAIN),
				"WY" => __("Wyoming", TribeEvents::PLUGIN_DOMAIN),
			);
		}	
	
		/**
		 * Builds a set of options for displaying an hour chooser
		 * @param string the current date (optional)
		 * @return string a set of HTML options with hours (current hour selected)
		 */
		public static function getHourOptions($date = "", $isStart = false) {
			$hours = TribeEventsViewHelpers::hours();
		
			if (count($hours) == 12)
				$h = 'h';
			else
				$h = 'H';
			$options = '';
		
			if (empty($date)) {
				$hour = ( $isStart ) ? '08' : '05';
			} else {
				$timestamp = strtotime($date);
				$hour = date($h, $timestamp);
				// fix hours if time_format has changed from what is saved
				if (preg_match('(pm|PM)', $timestamp) && $h == 'H')
					$hour = $hour + 12;
				if ($hour > 12 && $h == 'h')
					$hour = $hour - 12;
			}
			foreach ($hours as $hourText) {
				if ($hour == $hourText) {
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
		 * @param string the current date (optional)
		 * @return string a set of HTML options with minutes (current minute selected)
		 */
		public static function getMinuteOptions($date = "") {
			$minutes = TribeEventsViewHelpers::minutes();
			$options = '';
		
			if (empty($date)) {
				$minute = '00';
			} else {
				$minute = date('i', strtotime($date));
			}
		
			foreach ($minutes as $minuteText) {
				if ($minute == $minuteText) {
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
		 */
		private static function hours() {
			$hours = array();
			$rangeMax = ( strstr(get_option('time_format', TribeDateUtils::TIMEFORMAT), 'H') ) ? 23 : 12;
			foreach (range(1, $rangeMax) as $hour) {
				if ($hour < 10) {
					$hour = "0" . $hour;
				}
				$hours[$hour] = $hour;
			}
			return $hours;
		}

		/**
		 * Helper method to return an array of 00-59 for minutes
		 */
		private static function minutes() {
			$minutes = array();
			for ($minute = 0; $minute < 60; $minute+=5) {
				if ($minute < 10) {
					$minute = "0" . $minute;
				}
				$minutes[$minute] = $minute;
			}
			return $minutes;
		}
	
		/**
		 * Builds a set of options for diplaying a meridian chooser
		 *
		 * @param string YYYY-MM-DD HH:MM:SS to select (optional)
		 * @return string a set of HTML options with all meridians 
		 */
		public static function getMeridianOptions($date = "", $isStart = false) {
			if (strstr(get_option('time_format', TribeDateUtils::TIMEFORMAT), 'A')) {
				$a = 'A';
				$meridians = array("AM", "PM");
			} else {
				$a = 'a';
				$meridians = array("am", "pm");
			}
			if (empty($date)) {
				$meridian = ( $isStart ) ? $meridians[0] : $meridians[1];
			} else {
				$meridian = date($a, strtotime($date));
			}
			$return = '';
			foreach ($meridians as $m) {
				$return .= "<option value='$m'";
				if ($m == $meridian) {
					$return .= ' selected="selected"';
				}
				$return .= ">$m</option>\n";
			}
			return $return;
		}

		/**
		 * Builds a set of options for displaying a month chooser
		 * @param string the current date to select  (optional)
		 * @return string a set of HTML options with all months (current month selected)
		 */
		public static function getMonthOptions($date = "") {
			$tribe_ecp = TribeEvents::instance();
			$months = $tribe_ecp->monthNames();
			$options = '';
			if (empty($date)) {
				$month = ( date_i18n('j') == date_i18n('t') ) ? date('F', time() + 86400) : date_i18n('F');
			} else {
				$month = date('F', strtotime($date));
			}
			$monthIndex = 1;
			foreach ($months as $englishMonth => $monthText) {
				if ($monthIndex < 10) {
					$monthIndex = "0" . $monthIndex;  // need a leading zero in the month
				}
				if ($month == $englishMonth) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$options .= "<option value='$monthIndex' $selected>$monthText</option>\n";
				$monthIndex++;
			}
			return $options;
		}

		/**
		 * Builds a set of options for displaying a day chooser
		 * @param int number of days in the month
		 * @param string the current date (optional)
		 * @return string a set of HTML options with all days (current day selected)
		 */
		public static function getDayOptions($date = "", $totalDays = 31) {
			$days = TribeEventsViewHelpers::days($totalDays);
			$options = '';
			if (empty($date)) {
				$day = date_i18n('j');
				if ($day == date_i18n('t'))
					$day = '01';
				elseif ($day < 9)
					$day = '0' . ( $day + 1 );
				else
					$day++;
			} else {
				$day = date('d', strtotime($date));
			}
			foreach ($days as $dayText) {
				if ($dayText < 10) {
					$dayText = "0" . $dayText;  // need a leading zero in the day
				}
				if ($day == $dayText) {
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
		 * @param string the current date (optional)
		 * @return string a set of HTML options with adjacent years (current year selected)
		 */
		public static function getYearOptions($date = "") {
			$years = TribeEventsViewHelpers::years();
			$options = '';
			if (empty($date)) {
				$year = date_i18n('Y');
				if (date_i18n('n') == 12 && date_i18n('j') == 31)
					$year++;
			} else {
				$year = date('Y', strtotime($date));
			}
			foreach ($years as $yearText) {
				if ($year == $yearText) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				$options .= "<option value='$yearText' $selected>$yearText</option>\n";
			}
			return $options;
		}

		/**
		 * Helper method to return an array of years, back 2 and forward 5
		 */
		private static function years( ) {
			$year = ( int )date_i18n( 'Y' );
			// Back two years, forward 5
			$year_list = array( $year - 5, $year - 4, $year - 3, $year - 2, $year - 1, $year, $year + 1, $year + 2, $year + 3, $year + 4, $year + 5 );
			$years = array();
			foreach( $year_list as $single_year ) {
				$years[ $single_year ] = $single_year;
			}

			return $years;
		}


		/**
		 * Helper method to return an array of 1-31 for days
		 */
		public static function days( $totalDays ) {
			$days = array();
			foreach( range( 1, $totalDays ) as $day ) {
				$days[ $day ] = $day;
			}
			return $days;
		}
	}
}
?>