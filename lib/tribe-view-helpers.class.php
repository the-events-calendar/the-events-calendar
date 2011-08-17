<?php
class Tribe_View_Helpers {
	public static function constructCountries( $postId = '', $useDefault = true ) {
		global $sp_ecp;

		if(sp_get_option('spEventsCountries') != ''){
			$countries = array(
				"" => __("Select a Country:", Events_Calendar_Pro::PLUGIN_DOMAIN)
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
				"" => __("Select a Country:", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"US" => __("United States", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AF" => __("Afghanistan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AL" => __("Albania", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"DZ" => __("Algeria", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AS" => __("American Samoa", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AD" => __("Andorra", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AO" => __("Angola", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AI" => __("Anguilla", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AQ" => __("Antarctica", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AG" => __("Antigua And Barbuda", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AR" => __("Argentina", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AM" => __("Armenia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AW" => __("Aruba", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AU" => __("Australia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AT" => __("Austria", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AZ" => __("Azerbaijan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BS" => __("Bahamas", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BH" => __("Bahrain", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BD" => __("Bangladesh", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BB" => __("Barbados", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BY" => __("Belarus", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BE" => __("Belgium", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BZ" => __("Belize", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BJ" => __("Benin", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BM" => __("Bermuda", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BT" => __("Bhutan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BO" => __("Bolivia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BA" => __("Bosnia And Herzegowina", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BW" => __("Botswana", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BV" => __("Bouvet Island", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BR" => __("Brazil", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"IO" => __("British Indian Ocean Territory", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BN" => __("Brunei Darussalam", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BG" => __("Bulgaria", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BF" => __("Burkina Faso", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"BI" => __("Burundi", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KH" => __("Cambodia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CM" => __("Cameroon", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CA" => __("Canada", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CV" => __("Cape Verde", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KY" => __("Cayman Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CF" => __("Central African Republic", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TD" => __("Chad", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CL" => __("Chile", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CN" => __("China", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CX" => __("Christmas Island", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CC" => __("Cocos (Keeling) Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CO" => __("Colombia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KM" => __("Comoros", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CG" => __("Congo", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CD" => __("Congo, The Democratic Republic Of The", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CK" => __("Cook Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CR" => __("Costa Rica", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CI" => __("Cote D'Ivoire", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"HR" => __("Croatia (Local Name: Hrvatska)", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CU" => __("Cuba", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CY" => __("Cyprus", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CZ" => __("Czech Republic", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"DK" => __("Denmark", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"DJ" => __("Djibouti", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"DM" => __("Dominica", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"DO" => __("Dominican Republic", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TP" => __("East Timor", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"EC" => __("Ecuador", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"EG" => __("Egypt", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SV" => __("El Salvador", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GQ" => __("Equatorial Guinea", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ER" => __("Eritrea", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"EE" => __("Estonia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ET" => __("Ethiopia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"FK" => __("Falkland Islands (Malvinas)", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"FO" => __("Faroe Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"FJ" => __("Fiji", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"FI" => __("Finland", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"FR" => __("France", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"FX" => __("France, Metropolitan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GF" => __("French Guiana", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PF" => __("French Polynesia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TF" => __("French Southern Territories", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GA" => __("Gabon", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GM" => __("Gambia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GE" => __("Georgia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"DE" => __("Germany", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GH" => __("Ghana", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GI" => __("Gibraltar", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GR" => __("Greece", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GL" => __("Greenland", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GD" => __("Grenada", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GP" => __("Guadeloupe", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GU" => __("Guam", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GT" => __("Guatemala", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GN" => __("Guinea", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GW" => __("Guinea-Bissau", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GY" => __("Guyana", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"HT" => __("Haiti", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"HM" => __("Heard And Mc Donald Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"VA" => __("Holy See (Vatican City State)", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"HN" => __("Honduras", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"HK" => __("Hong Kong", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"HU" => __("Hungary", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"IS" => __("Iceland", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"IN" => __("India", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ID" => __("Indonesia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"IR" => __("Iran (Islamic Republic Of)", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"IQ" => __("Iraq", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"IE" => __("Ireland", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"IL" => __("Israel", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"IT" => __("Italy", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"JM" => __("Jamaica", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"JP" => __("Japan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"JO" => __("Jordan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KZ" => __("Kazakhstan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KE" => __("Kenya", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KI" => __("Kiribati", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KP" => __("Korea, Democratic People's Republic Of", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KR" => __("Korea, Republic Of", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KW" => __("Kuwait", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KG" => __("Kyrgyzstan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LA" => __("Lao People's Democratic Republic", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LV" => __("Latvia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LB" => __("Lebanon", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LS" => __("Lesotho", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LR" => __("Liberia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LY" => __("Libyan Arab Jamahiriya", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LI" => __("Liechtenstein", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LT" => __("Lithuania", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LU" => __("Luxembourg", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MO" => __("Macau", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MK" => __("Macedonia, Former Yugoslav Republic Of", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MG" => __("Madagascar", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MW" => __("Malawi", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MY" => __("Malaysia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MV" => __("Maldives", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ML" => __("Mali", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MT" => __("Malta", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MH" => __("Marshall Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MQ" => __("Martinique", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MR" => __("Mauritania", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MU" => __("Mauritius", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"YT" => __("Mayotte", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MX" => __("Mexico", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"FM" => __("Micronesia, Federated States Of", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MD" => __("Moldova, Republic Of", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MC" => __("Monaco", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MN" => __("Mongolia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ME" => __("Montenegro", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MS" => __("Montserrat", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MA" => __("Morocco", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MZ" => __("Mozambique", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MM" => __("Myanmar", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NA" => __("Namibia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NR" => __("Nauru", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NP" => __("Nepal", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NL" => __("Netherlands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AN" => __("Netherlands Antilles", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NC" => __("New Caledonia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NZ" => __("New Zealand", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NI" => __("Nicaragua", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NE" => __("Niger", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NG" => __("Nigeria", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NU" => __("Niue", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NF" => __("Norfolk Island", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"MP" => __("Northern Mariana Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"NO" => __("Norway", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"OM" => __("Oman", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PK" => __("Pakistan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PW" => __("Palau", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PA" => __("Panama", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PG" => __("Papua New Guinea", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PY" => __("Paraguay", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PE" => __("Peru", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PH" => __("Philippines", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PN" => __("Pitcairn", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PL" => __("Poland", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PT" => __("Portugal", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PR" => __("Puerto Rico", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"QA" => __("Qatar", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"RE" => __("Reunion", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"RO" => __("Romania", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"RU" => __("Russian Federation", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"RW" => __("Rwanda", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"KN" => __("Saint Kitts And Nevis", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LC" => __("Saint Lucia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"VC" => __("Saint Vincent And The Grenadines", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"WS" => __("Samoa", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SM" => __("San Marino", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ST" => __("Sao Tome And Principe", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SA" => __("Saudi Arabia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SN" => __("Senegal", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"RS" => __("Serbia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SC" => __("Seychelles", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SL" => __("Sierra Leone", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SG" => __("Singapore", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SK" => __("Slovakia (Slovak Republic)", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SI" => __("Slovenia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SB" => __("Solomon Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SO" => __("Somalia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ZA" => __("South Africa", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GS" => __("South Georgia, South Sandwich Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ES" => __("Spain", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"LK" => __("Sri Lanka", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SH" => __("St. Helena", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"PM" => __("St. Pierre And Miquelon", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SD" => __("Sudan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SR" => __("Suriname", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SJ" => __("Svalbard And Jan Mayen Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SZ" => __("Swaziland", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SE" => __("Sweden", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"CH" => __("Switzerland", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"SY" => __("Syrian Arab Republic", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TW" => __("Taiwan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TJ" => __("Tajikistan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TZ" => __("Tanzania, United Republic Of", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TH" => __("Thailand", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TG" => __("Togo", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TK" => __("Tokelau", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TO" => __("Tonga", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TT" => __("Trinidad And Tobago", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TN" => __("Tunisia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TR" => __("Turkey", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TM" => __("Turkmenistan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TC" => __("Turks And Caicos Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"TV" => __("Tuvalu", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"UG" => __("Uganda", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"UA" => __("Ukraine", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"AE" => __("United Arab Emirates", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"GB" => __("United Kingdom", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"UM" => __("United States Minor Outlying Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"UY" => __("Uruguay", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"UZ" => __("Uzbekistan", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"VU" => __("Vanuatu", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"VE" => __("Venezuela", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"VN" => __("Viet Nam", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"VG" => __("Virgin Islands (British)", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"VI" => __("Virgin Islands (U.S.)", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"WF" => __("Wallis And Futuna Islands", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"EH" => __("Western Sahara", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"YE" => __("Yemen", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"YU" => __("Yugoslavia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ZM" => __("Zambia", Events_Calendar_Pro::PLUGIN_DOMAIN),
				"ZW" => __("Zimbabwe", Events_Calendar_Pro::PLUGIN_DOMAIN)
				);
			if ( ($postId || $useDefault)) {
				$countryValue = get_post_meta( $postId, '_EventCountry', true );
				if( $countryValue ) $defaultCountry = array( array_search( $countryValue, $countries ), $countryValue );
				else $defaultCountry = $sp_ecp->getOption('defaultCountry');
				if( $defaultCountry && $defaultCountry[0] != "") {
					$selectCountry = array_shift( $countries );
					asort($countries);
					$countries = array($defaultCountry[0] => __($defaultCountry[1], Events_Calendar_Pro::PLUGIN_DOMAIN)) + $countries;
					$countries = array("" => __($selectCountry, Events_Calendar_Pro::PLUGIN_DOMAIN)) + $countries;
					array_unique($countries);
				}
				return $countries;
			} else {
				return $countries;
			}
	}
	
	public static function loadStates() {
		return array("AL" => __("Alabama", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"AK" => __("Alaska", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"AZ" => __("Arizona", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"AR" => __("Arkansas", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"CA" => __("California", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"CO" => __("Colorado", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"CT" => __("Connecticut", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"DE" => __("Delaware", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"DC" => __("District of Columbia", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"FL" => __("Florida", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"GA" => __("Georgia", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"HI" => __("Hawaii", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"ID" => __("Idaho", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"IL" => __("Illinois", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"IN" => __("Indiana", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"IA" => __("Iowa", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"KS" => __("Kansas", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"KY" => __("Kentucky", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"LA" => __("Louisiana", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"ME" => __("Maine", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"MD" => __("Maryland", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"MA" => __("Massachusetts", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"MI" => __("Michigan", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"MN" => __("Minnesota", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"MS" => __("Mississippi", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"MO" => __("Missouri", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"MT" => __("Montana", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"NE" => __("Nebraska", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"NV" => __("Nevada", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"NH" => __("New Hampshire", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"NJ" => __("New Jersey", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"NM" => __("New Mexico", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"NY" => __("New York", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"NC" => __("North Carolina", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"ND" => __("North Dakota", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"OH" => __("Ohio", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"OK" => __("Oklahoma", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"OR" => __("Oregon", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"PA" => __("Pennsylvania", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"RI" => __("Rhode Island", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"SC" => __("South Carolina", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"SD" => __("South Dakota", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"TN" => __("Tennessee", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"TX" => __("Texas", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"UT" => __("Utah", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"VT" => __("Vermont", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"VA" => __("Virginia", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"WA" => __("Washington", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"WV" => __("West Virginia", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"WI" => __("Wisconsin", Events_Calendar_Pro::PLUGIN_DOMAIN),
			"WY" => __("Wyoming", Events_Calendar_Pro::PLUGIN_DOMAIN),
		);
	}	
	
	/**
	 * Builds a set of options for displaying an hour chooser
	 * @param string the current date (optional)
	 * @return string a set of HTML options with hours (current hour selected)
	 */
	public static function getHourOptions($date = "", $isStart = false) {
		$hours = Tribe_View_Helpers::hours();
		
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
		$minutes = Tribe_View_Helpers::minutes();
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
		global $sp_ecp;
		$months = $sp_ecp->monthNames();
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
		$days = Tribe_View_Helpers::days($totalDays);
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
		$years = Tribe_View_Helpers::years();
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
