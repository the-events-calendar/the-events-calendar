<?php
if ( !class_exists( 'Events_Calendar_Pro' ) ) {
	/**
	 * Main plugin
	 */
	class Events_Calendar_Pro {
		const EVENTSERROROPT		= '_tec_events_errors';
		const CATEGORYNAME	 		= 'Events'; // legacy category
		const OPTIONNAME 			= 'sp_events_calendar_options';
		const POSTTYPE				= 'sp_events';
		const TAXONOMY				= 'sp_events_cat';
		// default formats, they are overridden by WP options or by arguments to date methods
		const DATEONLYFORMAT 		= 'F j, Y';
		const TIMEFORMAT			= 'g:i A';
		const DBDATEFORMAT	 		= 'Y-m-d';
		const DBDATETIMEFORMAT 		= 'Y-m-d G:i:s';
		const DBYEARMONTHTIMEFORMAT = 'Y-m';
		const VENUE_POST_TYPE = 'sp_venue';
		const VENUE_TITLE = 'Venue';
		const ORGANIZER_POST_TYPE = 'sp_organizer';
		const ORGANIZER_TITLE = 'Organizer';

		private $postTypeArgs = array(
			'public' => true,
			'rewrite' => array('slug' => 'event'),
			'menu_position' => 6,
			'supports' => array('title','editor','excerpt','author','thumbnail')
		);
		private $postVenueTypeArgs = array(
			'public' => true,
			'rewrite' => false,
			'menu_position' => 6,
			'supports' => array('thumbnail')
		);
		private $postOrganizerTypeArgs = array(
			'public' => true,
			'rewrite' => false,
			'menu_position' => 6,
			'supports' => array('thumbnail')
		);
		private $taxonomyLabels;

		public $supportUrl = 'http://support.makedesignnotwar.com/';
		public $envatoUrl = 'http://plugins.shaneandpeter.com/';


		private $rewriteSlug = 'events';
		private $rewriteSlugSingular = 'event';
		private $taxRewriteSlug = 'event/category';
		private $monthSlug = 'month';
		private $pastSlug = 'past';
		private $upcomingSlug = 'upcoming';
		private $defaultOptions = '';
		public $latestOptions;
		private $postExceptionThrown = false;
		private $optionsExceptionThrown = false;
		public $displaying;
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginName;
		public $pluginDomain = 'events-calendar-pro';
		private $tabIndexStart = 2000;

		public $metaTags = array(
			'_EventAllDay',
			'_EventStartDate',
			'_EventEndDate',
			'_EventVenueID',
			'_EventShowMapLink',
			'_EventShowMap',
			'_EventCost',
			'_EventOrganizerID',
			'_EventPhone',
			self::EVENTSERROROPT
		);

		public $legacyVenueTags = array(
			'_EventVenue',
			'_EventCountry',
			'_EventAddress',
			'_EventCity',
			'_EventState',
			'_EventProvince',
			'_EventZip'
		);

		public $venueTags = array(
			'_VenueVenue',
			'_VenueCountry',
			'_VenueAddress',
			'_VenueCity',
			'_VenueStateProvince',
			'_VenueZip',
			'_VenuePhone'
		);

		public $organizerTags = array(
			'_OrganizerOrganizer',
			'_OrganizerEmail',
			'_OrganizerWebsite',
			'_OrganizerPhone'
		);

		public $states = array();
		public $currentPostTimestamp;
		public $daysOfWeekShort;
		public $daysOfWeek;
		public $daysOfWeekMin;
		public $monthsFull;
		public $monthsShort;
		private function constructDaysOfWeek() {
			global $wp_locale;
			for ($i = 0; $i <= 6; $i++) {
				$day = $wp_locale->get_weekday($i);
				$this->daysOfWeek[$i] = $day;
				$this->daysOfWeekShort[$i] = $wp_locale->get_weekday_abbrev($day);
				$this->daysOfWeekMin[$i] = $wp_locale->get_weekday_initial($day);
			}
		}

		public $countries;
		public function constructCountries( $postId = '', $useDefault = true ) {

				if(sp_get_option('spEventsCountries') != ''){
					$countries = array(
						"" => __("Select a Country:", $this->pluginDomain)
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

				if(!is_array($countries) || count($countries) == 1)
					$countries = array(
						"" => __("Select a Country:", $this->pluginDomain),
						"US" => __("United States", $this->pluginDomain),
						"AF" => __("Afghanistan", $this->pluginDomain),
						"AL" => __("Albania", $this->pluginDomain),
						"DZ" => __("Algeria", $this->pluginDomain),
						"AS" => __("American Samoa", $this->pluginDomain),
						"AD" => __("Andorra", $this->pluginDomain),
						"AO" => __("Angola", $this->pluginDomain),
						"AI" => __("Anguilla", $this->pluginDomain),
						"AQ" => __("Antarctica", $this->pluginDomain),
						"AG" => __("Antigua And Barbuda", $this->pluginDomain),
						"AR" => __("Argentina", $this->pluginDomain),
						"AM" => __("Armenia", $this->pluginDomain),
						"AW" => __("Aruba", $this->pluginDomain),
						"AU" => __("Australia", $this->pluginDomain),
						"AT" => __("Austria", $this->pluginDomain),
						"AZ" => __("Azerbaijan", $this->pluginDomain),
						"BS" => __("Bahamas", $this->pluginDomain),
						"BH" => __("Bahrain", $this->pluginDomain),
						"BD" => __("Bangladesh", $this->pluginDomain),
						"BB" => __("Barbados", $this->pluginDomain),
						"BY" => __("Belarus", $this->pluginDomain),
						"BE" => __("Belgium", $this->pluginDomain),
						"BZ" => __("Belize", $this->pluginDomain),
						"BJ" => __("Benin", $this->pluginDomain),
						"BM" => __("Bermuda", $this->pluginDomain),
						"BT" => __("Bhutan", $this->pluginDomain),
						"BO" => __("Bolivia", $this->pluginDomain),
						"BA" => __("Bosnia And Herzegowina", $this->pluginDomain),
						"BW" => __("Botswana", $this->pluginDomain),
						"BV" => __("Bouvet Island", $this->pluginDomain),
						"BR" => __("Brazil", $this->pluginDomain),
						"IO" => __("British Indian Ocean Territory", $this->pluginDomain),
						"BN" => __("Brunei Darussalam", $this->pluginDomain),
						"BG" => __("Bulgaria", $this->pluginDomain),
						"BF" => __("Burkina Faso", $this->pluginDomain),
						"BI" => __("Burundi", $this->pluginDomain),
						"KH" => __("Cambodia", $this->pluginDomain),
						"CM" => __("Cameroon", $this->pluginDomain),
						"CA" => __("Canada", $this->pluginDomain),
						"CV" => __("Cape Verde", $this->pluginDomain),
						"KY" => __("Cayman Islands", $this->pluginDomain),
						"CF" => __("Central African Republic", $this->pluginDomain),
						"TD" => __("Chad", $this->pluginDomain),
						"CL" => __("Chile", $this->pluginDomain),
						"CN" => __("China", $this->pluginDomain),
						"CX" => __("Christmas Island", $this->pluginDomain),
						"CC" => __("Cocos (Keeling) Islands", $this->pluginDomain),
						"CO" => __("Colombia", $this->pluginDomain),
						"KM" => __("Comoros", $this->pluginDomain),
						"CG" => __("Congo", $this->pluginDomain),
						"CD" => __("Congo, The Democratic Republic Of The", $this->pluginDomain),
						"CK" => __("Cook Islands", $this->pluginDomain),
						"CR" => __("Costa Rica", $this->pluginDomain),
						"CI" => __("Cote D'Ivoire", $this->pluginDomain),
						"HR" => __("Croatia (Local Name: Hrvatska)", $this->pluginDomain),
						"CU" => __("Cuba", $this->pluginDomain),
						"CY" => __("Cyprus", $this->pluginDomain),
						"CZ" => __("Czech Republic", $this->pluginDomain),
						"DK" => __("Denmark", $this->pluginDomain),
						"DJ" => __("Djibouti", $this->pluginDomain),
						"DM" => __("Dominica", $this->pluginDomain),
						"DO" => __("Dominican Republic", $this->pluginDomain),
						"TP" => __("East Timor", $this->pluginDomain),
						"EC" => __("Ecuador", $this->pluginDomain),
						"EG" => __("Egypt", $this->pluginDomain),
						"SV" => __("El Salvador", $this->pluginDomain),
						"GQ" => __("Equatorial Guinea", $this->pluginDomain),
						"ER" => __("Eritrea", $this->pluginDomain),
						"EE" => __("Estonia", $this->pluginDomain),
						"ET" => __("Ethiopia", $this->pluginDomain),
						"FK" => __("Falkland Islands (Malvinas)", $this->pluginDomain),
						"FO" => __("Faroe Islands", $this->pluginDomain),
						"FJ" => __("Fiji", $this->pluginDomain),
						"FI" => __("Finland", $this->pluginDomain),
						"FR" => __("France", $this->pluginDomain),
						"FX" => __("France, Metropolitan", $this->pluginDomain),
						"GF" => __("French Guiana", $this->pluginDomain),
						"PF" => __("French Polynesia", $this->pluginDomain),
						"TF" => __("French Southern Territories", $this->pluginDomain),
						"GA" => __("Gabon", $this->pluginDomain),
						"GM" => __("Gambia", $this->pluginDomain),
						"GE" => __("Georgia", $this->pluginDomain),
						"DE" => __("Germany", $this->pluginDomain),
						"GH" => __("Ghana", $this->pluginDomain),
						"GI" => __("Gibraltar", $this->pluginDomain),
						"GR" => __("Greece", $this->pluginDomain),
						"GL" => __("Greenland", $this->pluginDomain),
						"GD" => __("Grenada", $this->pluginDomain),
						"GP" => __("Guadeloupe", $this->pluginDomain),
						"GU" => __("Guam", $this->pluginDomain),
						"GT" => __("Guatemala", $this->pluginDomain),
						"GN" => __("Guinea", $this->pluginDomain),
						"GW" => __("Guinea-Bissau", $this->pluginDomain),
						"GY" => __("Guyana", $this->pluginDomain),
						"HT" => __("Haiti", $this->pluginDomain),
						"HM" => __("Heard And Mc Donald Islands", $this->pluginDomain),
						"VA" => __("Holy See (Vatican City State)", $this->pluginDomain),
						"HN" => __("Honduras", $this->pluginDomain),
						"HK" => __("Hong Kong", $this->pluginDomain),
						"HU" => __("Hungary", $this->pluginDomain),
						"IS" => __("Iceland", $this->pluginDomain),
						"IN" => __("India", $this->pluginDomain),
						"ID" => __("Indonesia", $this->pluginDomain),
						"IR" => __("Iran (Islamic Republic Of)", $this->pluginDomain),
						"IQ" => __("Iraq", $this->pluginDomain),
						"IE" => __("Ireland", $this->pluginDomain),
						"IL" => __("Israel", $this->pluginDomain),
						"IT" => __("Italy", $this->pluginDomain),
						"JM" => __("Jamaica", $this->pluginDomain),
						"JP" => __("Japan", $this->pluginDomain),
						"JO" => __("Jordan", $this->pluginDomain),
						"KZ" => __("Kazakhstan", $this->pluginDomain),
						"KE" => __("Kenya", $this->pluginDomain),
						"KI" => __("Kiribati", $this->pluginDomain),
						"KP" => __("Korea, Democratic People's Republic Of", $this->pluginDomain),
						"KR" => __("Korea, Republic Of", $this->pluginDomain),
						"KW" => __("Kuwait", $this->pluginDomain),
						"KG" => __("Kyrgyzstan", $this->pluginDomain),
						"LA" => __("Lao People's Democratic Republic", $this->pluginDomain),
						"LV" => __("Latvia", $this->pluginDomain),
						"LB" => __("Lebanon", $this->pluginDomain),
						"LS" => __("Lesotho", $this->pluginDomain),
						"LR" => __("Liberia", $this->pluginDomain),
						"LY" => __("Libyan Arab Jamahiriya", $this->pluginDomain),
						"LI" => __("Liechtenstein", $this->pluginDomain),
						"LT" => __("Lithuania", $this->pluginDomain),
						"LU" => __("Luxembourg", $this->pluginDomain),
						"MO" => __("Macau", $this->pluginDomain),
						"MK" => __("Macedonia, Former Yugoslav Republic Of", $this->pluginDomain),
						"MG" => __("Madagascar", $this->pluginDomain),
						"MW" => __("Malawi", $this->pluginDomain),
						"MY" => __("Malaysia", $this->pluginDomain),
						"MV" => __("Maldives", $this->pluginDomain),
						"ML" => __("Mali", $this->pluginDomain),
						"MT" => __("Malta", $this->pluginDomain),
						"MH" => __("Marshall Islands", $this->pluginDomain),
						"MQ" => __("Martinique", $this->pluginDomain),
						"MR" => __("Mauritania", $this->pluginDomain),
						"MU" => __("Mauritius", $this->pluginDomain),
						"YT" => __("Mayotte", $this->pluginDomain),
						"MX" => __("Mexico", $this->pluginDomain),
						"FM" => __("Micronesia, Federated States Of", $this->pluginDomain),
						"MD" => __("Moldova, Republic Of", $this->pluginDomain),
						"MC" => __("Monaco", $this->pluginDomain),
						"MN" => __("Mongolia", $this->pluginDomain),
						"ME" => __("Montenegro", $this->pluginDomain),
						"MS" => __("Montserrat", $this->pluginDomain),
						"MA" => __("Morocco", $this->pluginDomain),
						"MZ" => __("Mozambique", $this->pluginDomain),
						"MM" => __("Myanmar", $this->pluginDomain),
						"NA" => __("Namibia", $this->pluginDomain),
						"NR" => __("Nauru", $this->pluginDomain),
						"NP" => __("Nepal", $this->pluginDomain),
						"NL" => __("Netherlands", $this->pluginDomain),
						"AN" => __("Netherlands Antilles", $this->pluginDomain),
						"NC" => __("New Caledonia", $this->pluginDomain),
						"NZ" => __("New Zealand", $this->pluginDomain),
						"NI" => __("Nicaragua", $this->pluginDomain),
						"NE" => __("Niger", $this->pluginDomain),
						"NG" => __("Nigeria", $this->pluginDomain),
						"NU" => __("Niue", $this->pluginDomain),
						"NF" => __("Norfolk Island", $this->pluginDomain),
						"MP" => __("Northern Mariana Islands", $this->pluginDomain),
						"NO" => __("Norway", $this->pluginDomain),
						"OM" => __("Oman", $this->pluginDomain),
						"PK" => __("Pakistan", $this->pluginDomain),
						"PW" => __("Palau", $this->pluginDomain),
						"PA" => __("Panama", $this->pluginDomain),
						"PG" => __("Papua New Guinea", $this->pluginDomain),
						"PY" => __("Paraguay", $this->pluginDomain),
						"PE" => __("Peru", $this->pluginDomain),
						"PH" => __("Philippines", $this->pluginDomain),
						"PN" => __("Pitcairn", $this->pluginDomain),
						"PL" => __("Poland", $this->pluginDomain),
						"PT" => __("Portugal", $this->pluginDomain),
						"PR" => __("Puerto Rico", $this->pluginDomain),
						"QA" => __("Qatar", $this->pluginDomain),
						"RE" => __("Reunion", $this->pluginDomain),
						"RO" => __("Romania", $this->pluginDomain),
						"RU" => __("Russian Federation", $this->pluginDomain),
						"RW" => __("Rwanda", $this->pluginDomain),
						"KN" => __("Saint Kitts And Nevis", $this->pluginDomain),
						"LC" => __("Saint Lucia", $this->pluginDomain),
						"VC" => __("Saint Vincent And The Grenadines", $this->pluginDomain),
						"WS" => __("Samoa", $this->pluginDomain),
						"SM" => __("San Marino", $this->pluginDomain),
						"ST" => __("Sao Tome And Principe", $this->pluginDomain),
						"SA" => __("Saudi Arabia", $this->pluginDomain),
						"SN" => __("Senegal", $this->pluginDomain),
						"RS" => __("Serbia", $this->pluginDomain),
						"SC" => __("Seychelles", $this->pluginDomain),
						"SL" => __("Sierra Leone", $this->pluginDomain),
						"SG" => __("Singapore", $this->pluginDomain),
						"SK" => __("Slovakia (Slovak Republic)", $this->pluginDomain),
						"SI" => __("Slovenia", $this->pluginDomain),
						"SB" => __("Solomon Islands", $this->pluginDomain),
						"SO" => __("Somalia", $this->pluginDomain),
						"ZA" => __("South Africa", $this->pluginDomain),
						"GS" => __("South Georgia, South Sandwich Islands", $this->pluginDomain),
						"ES" => __("Spain", $this->pluginDomain),
						"LK" => __("Sri Lanka", $this->pluginDomain),
						"SH" => __("St. Helena", $this->pluginDomain),
						"PM" => __("St. Pierre And Miquelon", $this->pluginDomain),
						"SD" => __("Sudan", $this->pluginDomain),
						"SR" => __("Suriname", $this->pluginDomain),
						"SJ" => __("Svalbard And Jan Mayen Islands", $this->pluginDomain),
						"SZ" => __("Swaziland", $this->pluginDomain),
						"SE" => __("Sweden", $this->pluginDomain),
						"CH" => __("Switzerland", $this->pluginDomain),
						"SY" => __("Syrian Arab Republic", $this->pluginDomain),
						"TW" => __("Taiwan", $this->pluginDomain),
						"TJ" => __("Tajikistan", $this->pluginDomain),
						"TZ" => __("Tanzania, United Republic Of", $this->pluginDomain),
						"TH" => __("Thailand", $this->pluginDomain),
						"TG" => __("Togo", $this->pluginDomain),
						"TK" => __("Tokelau", $this->pluginDomain),
						"TO" => __("Tonga", $this->pluginDomain),
						"TT" => __("Trinidad And Tobago", $this->pluginDomain),
						"TN" => __("Tunisia", $this->pluginDomain),
						"TR" => __("Turkey", $this->pluginDomain),
						"TM" => __("Turkmenistan", $this->pluginDomain),
						"TC" => __("Turks And Caicos Islands", $this->pluginDomain),
						"TV" => __("Tuvalu", $this->pluginDomain),
						"UG" => __("Uganda", $this->pluginDomain),
						"UA" => __("Ukraine", $this->pluginDomain),
						"AE" => __("United Arab Emirates", $this->pluginDomain),
						"GB" => __("United Kingdom", $this->pluginDomain),
						"UM" => __("United States Minor Outlying Islands", $this->pluginDomain),
						"UY" => __("Uruguay", $this->pluginDomain),
						"UZ" => __("Uzbekistan", $this->pluginDomain),
						"VU" => __("Vanuatu", $this->pluginDomain),
						"VE" => __("Venezuela", $this->pluginDomain),
						"VN" => __("Viet Nam", $this->pluginDomain),
						"VG" => __("Virgin Islands (British)", $this->pluginDomain),
						"VI" => __("Virgin Islands (U.S.)", $this->pluginDomain),
						"WF" => __("Wallis And Futuna Islands", $this->pluginDomain),
						"EH" => __("Western Sahara", $this->pluginDomain),
						"YE" => __("Yemen", $this->pluginDomain),
						"YU" => __("Yugoslavia", $this->pluginDomain),
						"ZM" => __("Zambia", $this->pluginDomain),
						"ZW" => __("Zimbabwe", $this->pluginDomain)
						);
// 					if ( $postId || $useDefault ) {
// 						$countryValue = get_post_meta( $postId, '_EventCountry', true );
// 						if( $countryValue ) $defaultCountry = array( array_search( $countryValue, $countries ), $countryValue );
// 						else $defaultCountry = $this->getOption('defaultCountry');
// 						if( $defaultCountry && $defaultCountry[0] != "" ) {
// 							$selectCountry = array_shift( $countries );
// 							asort($countries);
// 							$countries = array($defaultCountry[0] => __($defaultCountry[1], $this->pluginDomain)) + $countries;
// 							$countries = array("" => __($selectCountry, $this->pluginDomain)) + $countries;
// 							array_unique($countries);
// 						}
// 						$this->countries = $countries;
// 					} else {
						$this->countries = $countries;
					//}
		}
		/**
		 * Initializes plugin variables and sets up wordpress hooks/actions.
		 *
		 * @return void
		 */
		function __construct( ) {
			$this->pluginDir		= trailingslashit( basename( dirname(__FILE__) ) );
			$this->pluginPath		= trailingslashit( dirname(__FILE__) );
			$this->pluginUrl 		= WP_PLUGIN_URL.'/'.$this->pluginDir;
			
			register_deactivation_hook( __FILE__, 	array( &$this, 'on_deactivate' ) );
			register_activation_hook( __FILE__, 	array( &$this, 'on_activate' ) );
			$this->addFilters();
			$this->addActions();
		}
		
		public function init() {
			$this->loadTextDomain();
			$this->pluginName = __( 'Events Calendar Pro', $this->pluginDomain );
			$this->rewriteSlug = $this->getOption('eventsSlug', 'events');
			$this->rewriteSlugSingular = $this->getOption('singleEventSlug', 'event');
			$this->taxRewriteSlug = $this->rewriteSlug . '/' . __( 'category', $this->pluginDomain );
			$this->monthSlug = __('month', $this->pluginDomain);
			$this->upcomingSlug = __('upcoming', $this->pluginDomain);
			$this->pastSlug = __('past', $this->pluginDomain);
			$this->postTypeArgs['rewrite']['slug'] = $this->rewriteSlugSingular;
			$this->currentDay = '';
			$this->errors = '';

			$this->registerPostType();

			if(is_array(get_option('rewrite_rules')) && !array_key_exists('archives/'.$this->rewriteSlugSingular.'/[^/]+/([^/]+)/?$',get_option('rewrite_rules')))
				$this->flushRewriteRules();

			$this->states = array(
				"AL" => __("Alabama", $this->pluginDomain),
				"AK" => __("Alaska", $this->pluginDomain),
				"AZ" => __("Arizona", $this->pluginDomain),
				"AR" => __("Arkansas", $this->pluginDomain),
				"CA" => __("California", $this->pluginDomain),
				"CO" => __("Colorado", $this->pluginDomain),
				"CT" => __("Connecticut", $this->pluginDomain),
				"DE" => __("Delaware", $this->pluginDomain),
				"DC" => __("District of Columbia", $this->pluginDomain),
				"FL" => __("Florida", $this->pluginDomain),
				"GA" => __("Georgia", $this->pluginDomain),
				"HI" => __("Hawaii", $this->pluginDomain),
				"ID" => __("Idaho", $this->pluginDomain),
				"IL" => __("Illinois", $this->pluginDomain),
				"IN" => __("Indiana", $this->pluginDomain),
				"IA" => __("Iowa", $this->pluginDomain),
				"KS" => __("Kansas", $this->pluginDomain),
				"KY" => __("Kentucky", $this->pluginDomain),
				"LA" => __("Louisiana", $this->pluginDomain),
				"ME" => __("Maine", $this->pluginDomain),
				"MD" => __("Maryland", $this->pluginDomain),
				"MA" => __("Massachusetts", $this->pluginDomain),
				"MI" => __("Michigan", $this->pluginDomain),
				"MN" => __("Minnesota", $this->pluginDomain),
				"MS" => __("Mississippi", $this->pluginDomain),
				"MO" => __("Missouri", $this->pluginDomain),
				"MT" => __("Montana", $this->pluginDomain),
				"NE" => __("Nebraska", $this->pluginDomain),
				"NV" => __("Nevada", $this->pluginDomain),
				"NH" => __("New Hampshire", $this->pluginDomain),
				"NJ" => __("New Jersey", $this->pluginDomain),
				"NM" => __("New Mexico", $this->pluginDomain),
				"NY" => __("New York", $this->pluginDomain),
				"NC" => __("North Carolina", $this->pluginDomain),
				"ND" => __("North Dakota", $this->pluginDomain),
				"OH" => __("Ohio", $this->pluginDomain),
				"OK" => __("Oklahoma", $this->pluginDomain),
				"OR" => __("Oregon", $this->pluginDomain),
				"PA" => __("Pennsylvania", $this->pluginDomain),
				"RI" => __("Rhode Island", $this->pluginDomain),
				"SC" => __("South Carolina", $this->pluginDomain),
				"SD" => __("South Dakota", $this->pluginDomain),
				"TN" => __("Tennessee", $this->pluginDomain),
				"TX" => __("Texas", $this->pluginDomain),
				"UT" => __("Utah", $this->pluginDomain),
				"VT" => __("Vermont", $this->pluginDomain),
				"VA" => __("Virginia", $this->pluginDomain),
				"WA" => __("Washington", $this->pluginDomain),
				"WV" => __("West Virginia", $this->pluginDomain),
				"WI" => __("Wisconsin", $this->pluginDomain),
				"WY" => __("Wyoming", $this->pluginDomain),
			);
		}
		

		public function query() {
		global $wp_query;
			if ( !is_admin() && (($_GET['post_type'] == self::POSTTYPE || $_GET['sp_events_cat'] != '') || ($wp_query->query_vars['post_type'] == self::POSTTYPE || $wp_query->query_vars['sp_events_cat'] != ''))) 
				$this->addOrderQueryFilters();
		}


		private function addFilters() {
			add_filter( 'post_class', array( $this, 'post_class') );
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_filter( 'template_include', array( $this, 'templateChooser') );
			add_filter( 'query_vars',		array( $this, 'eventQueryVars' ) );
			add_filter( 'admin_body_class', array($this, 'admin_body_class') );
			add_filter( 'the_content', array($this, 'emptyEventContent' ), 1 );
			add_filter( 'wp_title', array($this, 'maybeAddEventTitle' ), 10, 2 );
			add_action( 'sp_events_event_save', array($this, 'save_venue_data' ), 10, 2 );
			add_action( 'sp_events_event_save', array($this, 'save_organizer_data' ), 10, 2 );
			if ( is_admin() && ! $this->getOption('spEventsDebug', false) ) {
				$this->addQueryFilters();
			}
			else if ( $this->getOption('spEventsDebug', false) ) {
				$this->addDebugColumns();
				add_action('admin_footer', array($this, 'debugInfo'));
			}
		}
		
		private function addOrderQueryFilters(){
			if(get_query_var('eventDisplay') == 'upcoming' || get_query_var('eventDisplay') == 'past' || get_query_var('sp_events_cat') != ''){
				add_filter('posts_where', array($this, 'events_ordering_where'));
				add_filter('posts_join', array($this, 'events_ordering_join'));
				add_filter( 'posts_orderby',	array( $this, 'events_ordering_orderby' ) );
			}
		}

		private function addQueryFilters() {
			add_filter( 'posts_join',		array( $this, 'events_search_join' ) );
			add_filter( 'posts_where',		array( $this, 'events_search_where' ) );
			add_filter( 'posts_orderby',	array( $this, 'events_search_orderby' ) );
			add_filter( 'posts_fields',		array( $this, 'events_search_fields' ) );
			add_filter( 'post_limits',		array( $this, 'events_search_limits' ) );
			add_filter( 'manage_posts_columns', array($this, 'column_headers'));
		}
		
		private function addDebugColumns() {
			add_filter( 'manage_posts_columns', array($this, 'debug_column_headers'));
			add_action( 'manage_posts_custom_column', array($this, 'debug_custom_columns'), 10, 2);
		}
		
		private function addActions() {
			add_action( 'init', array( $this, 'init'), 10 );

			add_action( 'parse_query', array( $this, 'query'), 0 );
			//add_action( 'reschedule_event_post', array( $this, 'reschedule') );
			add_action( 'template_redirect',				array( $this, 'loadStyle' ) );
			add_action( 'sp-events-save-more-options', array( $this, 'flushRewriteRules' ) );
			add_action( 'pre_get_posts',	array( $this, 'setOptions' ) );
			add_action( 'admin_menu', 		array( $this, 'addOptionsPage' ) );
			add_action( 'admin_init', 		array( $this, 'checkForOptionsChanges' ) );
			add_action( 'admin_menu', 		array( $this, 'addEventBox' ) );
			add_action( 'save_post',		array( $this, 'addEventMeta' ), 15, 2 );
			add_action( 'save_post',		array( $this, 'save_venue_data' ), 16, 2 );
			add_action( 'save_post',		array( $this, 'save_organizer_data' ), 16, 2 );
			//add_action( 'publish_post',		array( $this, 'addEventMeta' ), 15, 2 );

			add_action( 'sp_events_post_errors', array( 'TEC_Post_Exception', 'displayMessage' ) );
			add_action( 'sp_events_options_top', array( 'TEC_WP_Options_Exception', 'displayMessage') );
		//	add_action( 'init', array( $this, 'registerPostType' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'addAdminScriptsAndStyles' ) );
			add_action( 'plugins_loaded', array( $this, 'accessibleMonthForm'), -10 );
			add_action( 'manage_posts_custom_column', array($this, 'custom_columns'), 10, 2);
		}
		
		public function debugInfo() {
			echo '<h4>Events Calendar Pro Debug Info:</h4>';
			$this->printDebug($this->date, '$this->date');
			$this->printDebug($this->displaying, '$this->displaying');
		}
		
		public function printDebug($data, $title = '') {
			$title = ($title) ? '<strong>' . $title . '</strong> : ' : '';
			echo '<pre style="white-space:pre-wrap;font-size:11px;margin:1em;">';
			echo $title;
			print_r($data);
			echo '</pre>';
		}
		
		public function get_event_taxonomy() {
			return self::TAXONOMY;
		}

		public function maybeAddEventTitle($title, $sep){
			if(get_query_var('eventDisplay') == 'upcoming'){
				$new_title = __("Upcoming Events", $this->pluginDomain). ' '.$sep . ' ' . $title;
			}elseif(get_query_var('eventDisplay') == 'past'){
 				$new_title = __("Past Events", $this->pluginDomain) . ' '. $sep . ' ' . $title;

			}else{
				return $title;
			}

			return $new_title;

		}
		
		public function emptyEventContent( $content ) {
			global $post;
			if ( '' == $content && $post->post_type == self::POSTTYPE ) {
				$content = __('No description has been entered for this event.', $this->pluginDomain);
			}
			return $content;
		}
		
		public function column_headers( $columns ) {
			global $post;

			if ( $post->post_type == self::POSTTYPE ) {

				foreach ( $columns as $key => $value ) {
					$mycolumns[$key] = $value;
					if ( $key =='author' )
						$mycolumns['events-cats'] = __( 'Event Categories', $this->pluginDomain );
				}
				$columns = $mycolumns;

				unset($columns['date']);
				$columns['start-date'] = __( 'Start Date', $this->pluginDomain );
				$columns['end-date'] = __( 'End Date', $this->pluginDomain );
			}
			
			return $columns;
		}
		
		public function custom_columns( $column_id, $post_id ) {
			if ( $column_id == 'events-cats' ) {
				$event_cats = get_the_term_list( $post_id, self::TAXONOMY, '', ', ', '' );
				echo ( $event_cats ) ? strip_tags( $event_cats ) : '—';
			}
			if ( $column_id == 'start-date' ) {
				echo sp_get_start_date($post_id, false);
			}
			if ( $column_id == 'end-date' ) {
				echo sp_get_end_date($post_id, false);
			}
			
		}
		
		public function debug_column_headers( $columns ) {
			global $post;

			if ( $post->post_type == self::POSTTYPE ) {
				$columns['sp-debug'] = __( 'Debug', $this->pluginDomain );
			}
			
			return $columns;
		}
		
		public function debug_custom_columns( $column_id, $post_id ) {
			if ( $column_id == 'sp-debug' ) {
				echo 'EventStartDate: ' . get_post_meta($post_id, '_EventStartDate', true );
				echo '<br />';
				echo 'EventEndDate: ' . get_post_meta($post_id, '_EventEndDate', true );
			}
			
		}

		public function accessibleMonthForm() {
			if ( $_GET['EventJumpToMonth'] && $_GET['EventJumpToYear'] ) {
				$_GET['eventDisplay'] = 'month';
				$_GET['eventDate'] = $_GET['EventJumpToYear'] . '-' . $_GET['EventJumpToMonth'];
			}
		}
		
		public function log( $data = array() ) {
			error_log(print_r($data,1));
		}
		
		public function body_class( $c ) {
			if ( get_query_var('post_type') == self::POSTTYPE ) {
				if ( ! is_single() ) {
					$c[] = 'events-archive';
				}
				else {
					$c[] = 'events-single';
				}
			}
			return $c;
		}
		
		public function post_class( $c ) {
			global $post;
			if ( $post->post_type == self::POSTTYPE && $terms = get_the_terms( $post->ID , self::TAXONOMY ) ) {
				foreach ($terms as $term) {
					$c[] = 'cat_' . sanitize_html_class($term->slug, $term->cat_ID);
				}
			}
			return $c;
		}
		
		public function registerPostType() {
			$this->generatePostTypeLabels();
			register_post_type(self::POSTTYPE, $this->postTypeArgs);
			register_post_type(self::VENUE_POST_TYPE, $this->postVenueTypeArgs);
			register_post_type(self::ORGANIZER_POST_TYPE, $this->postOrganizerTypeArgs);
			
			register_taxonomy( self::TAXONOMY, self::POSTTYPE, array(
				'hierarchical' => true,
				'update_count_callback' => '',
				'rewrite' => array('slug'=> $this->taxRewriteSlug),
				'public' => true,
				'show_ui' => true,
				'labels' => $this->taxonomyLabels
			));
			
			if( $this->getOption('showComments','no') == 'yes' ) {
				add_post_type_support( self::POSTTYPE, 'comments');
			}
			
		}
		
		private function generatePostTypeLabels() {
			$this->postTypeArgs['labels'] = array(
				'name' => __('Events', $this->pluginDomain),
				'singular_name' => __('Event', $this->pluginDomain),
				'add_new' => __('Add New', $this->pluginDomain),
				'add_new_item' => __('Add New Event', $this->pluginDomain),
				'edit_item' => __('Edit Event', $this->pluginDomain),
				'new_item' => __('New Event', $this->pluginDomain),
				'view_item' => __('View Event', $this->pluginDomain),
				'search_items' => __('Search Events', $this->pluginDomain),
				'not_found' => __('No events found', $this->pluginDomain),
				'not_found_in_trash' => __('No events found in Trash', $this->pluginDomain)
			);
			
			$this->postVenueTypeArgs['labels'] = array(
				'name' => __('Venues', $this->pluginDomain),
				'singular_name' => __('Venue', $this->pluginDomain),
				'add_new' => __('Add New', $this->pluginDomain),
				'add_new_item' => __('Add New Venue', $this->pluginDomain),
				'edit_item' => __('Edit Venue', $this->pluginDomain),
				'new_item' => __('New Venue', $this->pluginDomain),
				'view_item' => __('View Venue', $this->pluginDomain),
				'search_items' => __('Search Venues', $this->pluginDomain),
				'not_found' => __('No venue found', $this->pluginDomain),
				'not_found_in_trash' => __('No venues found in Trash', $this->pluginDomain)
			);
			
			$this->postOrganizerTypeArgs['labels'] = array(
				'name' => __('Organizers', $this->pluginDomain),
				'singular_name' => __('Organizer', $this->pluginDomain),
				'add_new' => __('Add New', $this->pluginDomain),
				'add_new_item' => __('Add New Organizer', $this->pluginDomain),
				'edit_item' => __('Edit Organizer', $this->pluginDomain),
				'new_item' => __('New Organizer', $this->pluginDomain),
				'view_item' => __('View Venue', $this->pluginDomain),
				'search_items' => __('Search Organizers', $this->pluginDomain),
				'not_found' => __('No organizer found', $this->pluginDomain),
				'not_found_in_trash' => __('No organizers found in Trash', $this->pluginDomain)
			);
			
			$this->taxonomyLabels = array(
				'name' =>  __( 'Event Categories', $this->pluginDomain ),
				'singular_name' =>  __( 'Event Category', $this->pluginDomain ),
				'search_items' =>  __( 'Search Event Categories', $this->pluginDomain ),
				'all_items' => __( 'All Event Categories', $this->pluginDomain ),
				'parent_item' =>  __( 'Parent Event Category', $this->pluginDomain ),
				'parent_item_colon' =>  __( 'Parent Event Category:', $this->pluginDomain ),
				'edit_item' =>   __( 'Edit Event Category', $this->pluginDomain ),
				'update_item' =>  __( 'Update Event Category', $this->pluginDomain ),
				'add_new_item' =>  __( 'Add New Event Category', $this->pluginDomain ),
				'new_item_name' =>  __( 'New Event Category Name', $this->pluginDomain )
			);
			
		}
		
		public function admin_body_class( $classes ) {
			global $current_screen;			
			if ( $current_screen->post_type == self::POSTTYPE || $current_screen->id == 'settings_page_the-events-calendar.class' ) {
				$classes .= ' events-cal ';
			}
			return $classes;
		}
		
		public function addAdminScriptsAndStyles() {
			// always load style. need for icon in nav.
			wp_enqueue_style( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.css' );
			
			global $current_screen;			
			if ( $current_screen->post_type == self::POSTTYPE || $current_screen->id == 'settings_page_the-events-calendar.class' ) {
				wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
				wp_enqueue_script( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js', array('jquery-ui-datepicker'), '', true );
				// calling our own localization because wp_localize_scripts doesn't support arrays or objects for values, which we need.
				add_action('admin_footer', array($this, 'printLocalizedAdmin') );
			}elseif( $current_screen->post_type == self::VENUE_POST_TYPE){

				wp_enqueue_script( self::VENUE_POST_TYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js');
			}elseif( $current_screen->post_type == self::ORGANIZER_POST_TYPE){

				wp_enqueue_script( self::ORGANIZER_POST_TYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js');
			}
			

		}
		
		public function localizeAdmin() {
			$dom = $this->pluginDomain;
			
			$bits = array(
				'dayNames' => $this->daysOfWeek,
				'dayNamesShort' => $this->daysOfWeekShort,
				'dayNamesMin' => $this->daysOfWeekMin,
				'monthNames' => array_values( $this->monthNames() ),
				'monthNamesShort' => array_values( $this->monthNames( true ) ),
				'nextText' => __( 'Next', $dom ),
				'prevText' => __( 'Prev', $dom ),
				'currentText' => __( 'Today', $dom ),
				'closeText' => __( 'Done', $dom )
			);
			return $bits;
		}
		
		public function printLocalizedAdmin() {
			$object_name = 'TEC';
			$vars = $this->localizeAdmin();
			
			$data = "var $object_name = {\n";
			$eol = '';
			foreach ( $vars as $var => $val ) {
				
				if ( gettype($val) == 'array' || gettype($val) == 'object' ) {
					$val = json_encode($val);
				}
				else {
					$val = '"' . esc_js( $val ) . '"';
				} 
				
				$data .= "$eol\t$var: $val";
				$eol = ",\n";
			}
			$data .= "\n};\n";
			
			echo "<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo $data;
			echo "/* ]]> */\n";
			echo "</script>\n";
			
		}
		
		public function addOptionsPage() {
    		add_options_page($this->pluginName, $this->pluginName, 'administrator', $this->pluginDomain, array($this,'optionsPageView'));
		}
		
		public function optionsPageView() {
			include( $this->pluginPath . 'views/events-options.php' );
			// every visit to ECP Settings = flush rules.
			$this->flushRewriteRules();
		}
		
		public function checkForOptionsChanges() {
			
			if ( isset($_POST['upgradeEventsCalendar']) && check_admin_referer('upgradeEventsCalendar') ) {
				$this->upgradeData();
			}
			
			if ( isset($_POST['saveEventsCalendarOptions']) && check_admin_referer('saveEventsCalendarOptions') ) {
                $options = $this->getOptions();
				$options['viewOption'] = $_POST['viewOption'];
				if($_POST['defaultCountry']) {
					$this->constructCountries();
					$defaultCountryKey = array_search( $_POST['defaultCountry'], $this->countries );
					$options['defaultCountry'] = array( $defaultCountryKey, $_POST['defaultCountry'] );
				}

				if( $_POST['embedGoogleMapsHeight'] ) {
					$options['embedGoogleMapsHeight'] = $_POST['embedGoogleMapsHeight'];
					$options['embedGoogleMapsWidth'] = $_POST['embedGoogleMapsWidth'];
				}
				
				// single event cannot be same as plural. Or empty.
				if ( $_POST['singleEventSlug'] === $_POST['eventsSlug'] || empty($_POST['singleEventSlug']) ) {
					$_POST['singleEventSlug'] = 'event';
				}
				
				// Events slug can't be empty
				if ( empty( $_POST['eventsSlug'] ) ) {
					$_POST['eventsSlug'] = 'events';
				}
				
				$opts = array('embedGoogleMaps', 'showComments', 'displayEventsOnHomepage', 'resetEventPostDate', 'useRewriteRules', 'spEventsDebug', 'eventsSlug', 'singleEventSlug','spEventsAfterHTML','spEventsBeforeHTML','spEventsCountries','defaultValueReplace','eventsDefaultVenue','eventsDefaultState','eventsDefaultAddress','eventsDefaultCity','eventsDefaultZip','eventsDefaultPhone','eventsDefaultCost' );
				foreach ($opts as $opt) {
					$options[$opt] = $_POST[$opt];
				}
				
				// events slug happiness
				$slug = $options['eventsSlug'];
				$slug = sanitize_title_with_dashes($slug);
				$slug = str_replace('/',' ',$slug);
				$options['eventsSlug'] = $slug;
				$this->rewriteSlug = $slug;
				
				
				if ( $options['useRewriteRules'] == 'on' || isset( $options['eventsSlug']) ) {
					$this->flushRewriteRules();
				}
				
				try {
					do_action( 'sp-events-save-more-options' );
					if ( !$this->optionsExceptionThrown ) $options['error'] = "";
				} catch( TEC_WP_Options_Exception $e ) {
					$this->optionsExceptionThrown = true;
					$options['error'] .= $e->getMessage();
				}
				$this->saveOptions($options);
				$this->latestOptions = $options; //XXX ? duplicated in saveOptions() ?
			} // end if
		}
		/**
		 * Will upgrade data from old free plugin to new plugin
		 *
		 */
		
		private function upgradeData() {
			$posts = $this->getLegacyEvents();
			
			// we don't want the old event category
			$eventCat = get_term_by('name', self::CATEGORYNAME, 'category' );
			// existing event cats
			$existingEventCats = (array) get_terms(self::TAXONOMY, array('fields' => 'names'));
			// store potential new event cats;
			$newEventCats = array();
			
			// first create log needed new event categories
			foreach ($posts as $key => $post) {
				// we don't want the old Events category
				$cats = $this->removeEventCat( get_the_category($post->ID), $eventCat );
				// see what new ones we need
				$newEventCats = $this->mergeCatList( $cats, $newEventCats );
				// store on the $post to keep from re-querying
				$posts[$key]->cats = $this->getCatNames( $cats );
			}
			// dedupe
			$newEventCats = array_unique($newEventCats);

			// let's create new events cats
			foreach ( $newEventCats as $cat ) {
				// leave alone existing ones
				if ( in_array( $cat, $existingEventCats ) )
					continue;
					
				// make 'em!
				wp_insert_term( $cat, self::TAXONOMY );
			}
			// now we know what we're in for
			$masterCats = get_terms( self::TAXONOMY, array('hide_empty'=>false) );

			// let's convert those posts
			foreach ( $posts as $post ) {
				// new post_type sir
				set_post_type( $post->ID, self::POSTTYPE );
				// set new events cats. we stored the array above, remember?
				if ( ! empty($post->cats) )
					wp_set_object_terms( $post->ID, $post->cats, self::TAXONOMY );
			}
		}
		
		private function getLegacyEvents( $number = -1 ) {
			$query = new WP_Query( array(
				'post_status' => 'publish',
				'posts_per_page' => $number,
				'meta_key' => '_EventStartDate',
				'category_name' => self::CATEGORYNAME
			));
			return $query->posts;
		}
		
		private function getCatNames( $cats ) {
			foreach ( $cats as $cat ) {
				$r[] = $cat->name;
			}
			return $r;
		}
		
		private function mergeCatList ( $new, $old ) {
			$r = (array) $this->getCatNames( $new );
			return array_merge( $r, $old );
		}
		
		private function removeEventCat( $cats, $removeCat ) {
			
			foreach ( $cats as $k => $cat ) {
				if ( $cat->term_id == $removeCat->term_id ) {
					unset($cats[$k]);
				}
			}
			return $cats;
		}

		public function events_ordering_join($extraJoin){
			global $wpdb;

			$extraJoin .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id ) ";
			$extraJoin .= " LEFT JOIN {$wpdb->postmeta} as eventEnd ON( {$wpdb->posts}.ID = eventEnd.post_id ) ";

			return $extraJoin;
		}


		public function events_ordering_orderby($orderby){
			global $wpdb;
			$orderby = 'DATE(eventStart.meta_value) '.$this->order.', TIME(eventStart.meta_value) '.$this->order;

		return $orderby;
		}


		public function events_ordering_where($whereClause){
			global $wpdb; 
				$date = explode(' ', $this->date);

				$whereClause .= ' AND ( eventStart.meta_key = "_EventStartDate" AND eventEnd.meta_key = "_EventEndDate" ) ';

				$whereClause .= $wpdb->prepare(" AND (eventStart.meta_value ".$this->startOperator." %s || ( DATE(eventStart.meta_value) = %s && TIME(eventStart.meta_value) ".$this->startOperator." %s) || eventEnd.meta_value ".$this->startOperator." %s)  \n", $this->date	, $date[0]	, $date[1], $this->date	 );

				if( sp_is_past( ) ) { //Don't show ongoing events. Only show ended events.
					$whereClause .= ' AND  eventEnd.meta_value <= "'.$this->date.'" ';
				}

			return $whereClause;
		}
		
				/**
		 * fields filter for standard wordpress templates.  Adds the start and end date to queries in the
		 * events category
		 *
		 * @param string fields
		 * @param string modified fields for events queries
		 */
		public function events_search_fields( $fields ) {
			if ( get_query_var('post_type') != self::POSTTYPE ) { 
				return $fields;
			}
			global $wpdb;
			$fields .= ', eventStart.meta_value as EventStartDate, eventEnd.meta_value as EventEndDate ';
			return $fields;

		}
		/**
		 * join filter for admin quries
		 *
		 * @param string join clause
		 * @return string modified join clause 
		 */
		public function events_search_join( $join ) {
			global $wpdb;
			if ( get_query_var('post_type') != self::POSTTYPE ) { 
				return $join;
			}
			$join .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id ) ";
			$join .= " LEFT JOIN {$wpdb->postmeta} as eventEnd ON( {$wpdb->posts}.ID = eventEnd.post_id ) ";
			return $join;
		}
		/**
		 * where filter for admin queries
		 *
		 * @param string where clause
		 * @return string modified where clause
		 */
		public function events_search_where( $where ) {
			if ( get_query_var('post_type') != self::POSTTYPE ) { 
				return $where;
			}
			$where .= ' AND ( eventStart.meta_key = "_EventStartDate" AND eventEnd.meta_key = "_EventEndDate" ) ';

			if( sp_is_upcoming( ) ) {	
				// Is the start date in the future?
				$where .= ' AND ( eventStart.meta_value > "'.$this->date.'" ';
				// Or is the start date in the past but the end date in the future? (meaning the event is currently ongoing)
				$where .= ' OR ( eventStart.meta_value < "'.$this->date.'" AND eventEnd.meta_value > "'.$this->date.'" ) ) ';
			}
			if( sp_is_past( ) ) {
				// Is the start date in the past?
				$where .= ' AND  eventStart.meta_value < "'.$this->date.'" ';
			}
			return $where;
		}

		/**
		 * orderby filter for standard admin queries
		 *
		 * @param string orderby
		 * @return string modified orderby clause
		 */
		public function events_search_orderby( $orderby ) {
			if ( get_query_var('post_type') != self::POSTTYPE ) { 
				return $orderby;
			}
			$orderby = ' eventStart.meta_value '.$this->order;
			return $orderby;
		}
		/**
		 * limit filter for admin queries
		 *
		 * @param string limits clause
		 * @return string modified limits clause
		 */
		public function events_search_limits( $limits ) { 
			if ( get_query_var('post_type') != self::POSTTYPE ) { 
				return $limits;
			}
			global $current_screen;
			$paged = (int) get_query_var('paged');
			if (empty($paged)) {
					$paged = 1;
			}
			if ( is_admin() ) {
				$option = str_replace( '-', '_', "{$current_screen->id}_per_page" );
				$per_page = get_user_option( $option );
				$per_page = ( $per_page ) ? (int) $per_page : 20; // 20 is default in backend
			}
			else {
				$per_page = intval( get_option('posts_per_page') );
			}

			$page_start = ( $paged - 1 ) * $per_page;
			$limits = 'LIMIT ' . $page_start . ', ' . $per_page;
			return $limits;
		}
		
		/// OPTIONS DATA
        public function getOptions() {
            if ('' === $this->defaultOptions) {
                $this->defaultOptions = get_option(Events_Calendar_Pro::OPTIONNAME, array());
            }
            return $this->defaultOptions;
        }
		
	public function getOption($optionName, $default = '') {
		if( ! $optionName )
			return null;
		
		if( $this->latestOptions ) 
			return $this->latestOptions[$optionName];

		$options = $this->getOptions();
		return ( $options[$optionName] ) ? $options[$optionName] : $default;
		
	}
		
        private function saveOptions($options) {
            if (!is_array($options)) {
                return;
            }
            if ( update_option(Events_Calendar_Pro::OPTIONNAME, $options) ) {
				$this->latestOptions = $options;
			} else {
				$this->latestOptions = $this->getOptions();
			}
        }
        
        public function deleteOptions() {
            delete_option(Events_Calendar_Pro::OPTIONNAME);
        }

		public function templateChooser($template) {
			
			// hijack to iCal template
			if ( get_query_var('ical') || isset($_GET['ical']) ) {
				global $wp_query;
				if ( is_single() ) {
					$post_id = $wp_query->post->ID;
					$this->iCalFeed($post_id);
				}
				else if ( is_tax( self::TAXONOMY) ) {
					$this->iCalFeed( null, get_query_var( self::TAXONOMY ) );
				}
				else {
					$this->iCalFeed();
				}
				die;
			}

			// no non-events need apply
			if ( get_query_var( 'post_type' ) != self::POSTTYPE && ! is_tax( self::TAXONOMY ) ) {
				return $template;
			}

			//is_home fixer
			global $wp_query;
			$wp_query->is_home = false;
			
			if ( is_tax( self::TAXONOMY) ) {
				if ( sp_is_upcoming() || sp_is_past() )
					return $this->getTemplateHierarchy('list');
				else
					return $this->getTemplateHierarchy('gridview');
			}
			// single event
			if ( is_single() ) {
				return $this->getTemplateHierarchy('single');
			}
			// list view
			elseif ( sp_is_upcoming() || sp_is_past() ) {
				return $this->getTemplateHierarchy('list');
			}
			// grid view
			else {
				return $this->getTemplateHierarchy('gridview');
	        }
		}
		
		/**
		 * Loads theme files in appropriate hierarchy: 1) child theme, 
		 * 2) parent template, 3) plugin resources. will look in the events/
		 * directory in a theme and the views/ directory in the plugin
		 *
		 * @param string $template template file to search for
		 * @return template path
		 * @author Matt Wiebe
		 **/

		public function getTemplateHierarchy($template) {
			// whether or not .php was added
			$template_slug = rtrim($template, '.php');
			$template = $template_slug . '.php';
			
			if ( $theme_file = locate_template(array('events/'.$template)) ) {
				$file = $theme_file;
			}
			else {
				$file = $this->pluginPath . 'views/' . $template;
			}
			return apply_filters( 'sp_events_template_'.$template, $file);
		}
		
		public function truncate($text, $excerpt_length = 44) {

			$text = strip_shortcodes( $text );

			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]&gt;', $text);
			$text = strip_tags($text);

			$words = explode(' ', $text, $excerpt_length + 1);
			if (count($words) > $excerpt_length) {
				array_pop($words);
				$text = implode(' ', $words);
				$text = rtrim($text);
				$text .= '&hellip;';
				}

			return $text;
		}
		
		public function loadTextDomain() {
			load_plugin_textdomain( $this->pluginDomain, false, $this->pluginDir . 'lang/');
			$this->constructDaysOfWeek();
			$this->initMonthNames();
		}
		
		public function loadStyle() {
			
			$eventsURL = trailingslashit( $this->pluginUrl ) . 'resources/';
			wp_enqueue_script('sp-events-calendar-script', $eventsURL.'events.js', array('jquery') );
			// is there an events.css file in the theme?
			if ( $user_style = locate_template(array('events/events.css')) ) {
				$styleUrl = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $user_style );
			}
			else {
				$styleUrl = $eventsURL.'events.css';
			}
			$styleUrl = apply_filters( 'sp_events_stylesheet_url', $styleUrl );
			
			if ( $styleUrl )
				wp_enqueue_style('sp-events-calendar-style', $styleUrl);
		}
	
		/**
		 * Helper method to return an array of 1-12 for months
		 */
		public function months( ) {
			$months = array();
			foreach( range( 1, 12 ) as $month ) {
				$months[ $month ] = $month;
			}
			return $months;
		}

		private function initMonthNames() {
			global $wp_locale;
			$this->monthsFull = array( 
				'January' => $wp_locale->get_month('01'), 
				'February' => $wp_locale->get_month('02'), 
				'March' => $wp_locale->get_month('03'), 
				'April' => $wp_locale->get_month('04'), 
				'May' => $wp_locale->get_month('05'), 
				'June' => $wp_locale->get_month('06'), 
				'July' => $wp_locale->get_month('07'), 
				'August' => $wp_locale->get_month('08'), 
				'September' => $wp_locale->get_month('09'), 
				'October' => $wp_locale->get_month('10'), 
				'November' => $wp_locale->get_month('11'), 
				'December' => $wp_locale->get_month('12') 
			);
			// yes, it's awkward. easier this way than changing logic elsewhere.
			$this->monthsShort = $months = array( 
				'Jan' => $wp_locale->get_month_abbrev( $wp_locale->get_month('01') ), 
				'Feb' => $wp_locale->get_month_abbrev( $wp_locale->get_month('02') ), 
				'Mar' => $wp_locale->get_month_abbrev( $wp_locale->get_month('03') ), 
				'Apr' => $wp_locale->get_month_abbrev( $wp_locale->get_month('04') ), 
				'May' => $wp_locale->get_month_abbrev( $wp_locale->get_month('05') ), 
				'Jun' => $wp_locale->get_month_abbrev( $wp_locale->get_month('06') ), 
				'Jul' => $wp_locale->get_month_abbrev( $wp_locale->get_month('07') ), 
				'Aug' => $wp_locale->get_month_abbrev( $wp_locale->get_month('08') ), 
				'Sep' => $wp_locale->get_month_abbrev( $wp_locale->get_month('09') ), 
				'Oct' => $wp_locale->get_month_abbrev( $wp_locale->get_month('10') ), 
				'Nov' => $wp_locale->get_month_abbrev( $wp_locale->get_month('11') ), 
				'Dec' => $wp_locale->get_month_abbrev( $wp_locale->get_month('12') )
			); 
		}

		/**
		 * Helper method to return an array of translated month names or short month names
		 * @return Array translated month names
		 */
		public function monthNames( $short = false ) {
			if ($short)
				return $this->monthsShort;
			return $this->monthsFull;
		}

		/**
		 * Helper method to return an array of 1-31 for days
		 */
		public function days( $totalDays ) {
			$days = array();
			foreach( range( 1, $totalDays ) as $day ) {
				$days[ $day ] = $day;
			}
			return $days;
		}

		/**
		 * Helper method to return an array of years, back 2 and forward 5
		 */
		public function years( ) {
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
		* This function is scheduled to run at midnight.  If any posts are set with EventStartDate
		* to today, update the post so that it was posted today.   This will force the event to be
		* displayed in the main loop on the homepage.
		* 
		* @return void
		*/	
		public function reschedule( ) {
			$resetEventPostDate = $this->getOption('resetEventPostDate', 'off');
			if( $resetEventPostDate == 'off' ){
				return;
			}
			global $wpdb;
			$query = "
				SELECT * FROM $wpdb->posts
				LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)
				WHERE 
				$wpdb->postmeta.meta_key = '_EventStartDate' 
				AND $wpdb->postmeta.meta_value = CURRENT_DATE()";
			$return = $wpdb->get_results($query, OBJECT);
			if ( is_array( $return ) && count( $return ) ) {
				foreach ( $return as $row ) {
					$updateQuery = "UPDATE $wpdb->posts SET post_date = NOW() WHERE $wpdb->posts.ID = " . $row->ID;
					$wpdb->query( $updateQuery );
				}
			}
		}
		/**
	     * Gets the Category id to use for an Event
		 * Deprecated, but keeping in for legacy users for now.
	     * @return int|false Category id to use or false is none is set
	     */
	    static function eventCategory() {
			return get_cat_id( Events_Calendar_Pro::CATEGORYNAME );
	    }
		/**
		 * Flush rewrite rules to support custom links
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 */
		public function flushRewriteRules() {
			global $wp_rewrite; 
			$wp_rewrite->flush_rules();
			// in case this was called too early, let's get it in the end.
			add_action('shutdown', array($this, 'flushRewriteRules'));
		}		
		/**
		 * Adds the event specific query vars to Wordpress
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 * @return mixed array of query variables that this plugin understands
		 */
		public function eventQueryVars( $qvars ) {
			$qvars[] = 'eventDisplay';
			$qvars[] = 'eventDate';
			$qvars[] = 'ical';
			return $qvars;		  
		}
		/**
		 * Adds Event specific rewrite rules.
		 *
		 *	events/				=>	/?post_type=sp_events
		 *  events/month		=>  /?post_type=sp_events&eventDisplay=month
		 *	events/upcoming		=>	/?post_type=sp_events&eventDisplay=upcoming
		 *	events/past			=>	/?post_type=sp_events&eventDisplay=past
		 *	events/2008-01/#15	=>	/?post_type=sp_events&eventDisplay=bydate&eventDate=2008-01-01
		 * events/category/some-events-category => /?post_type=sp_events&sp_event_cat=some-events-category
		 *
		 * @return void
		 */
		public function filterRewriteRules( $wp_rewrite ) {
			if ( '' == get_option('permalink_structure') || 'off' == $this->getOption('useRewriteRules','on') ) {
				
			}

			$base = trailingslashit( $this->rewriteSlug );
			$baseSingle = trailingslashit( $this->rewriteSlugSingular );
			$baseTax = trailingslashit( $this->taxRewriteSlug );
			
			$month = $this->monthSlug;
			$upcoming = $this->upcomingSlug;
			$past = $this->pastSlug;
			
			$newRules[$base . 'page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . 'ical'] = 'index.php?post_type=' . self::POSTTYPE . '&ical=1';
			$newRules[$base . '(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&feed=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $month] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=month';
			$newRules[$base . $upcoming . '/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $upcoming] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming';
			$newRules[$base . $past . '/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $past] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=past';
			$newRules[$base . '(\d{4}-\d{2})$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(1);
			$newRules[$base . 'feed/?$'] = 'index.php?eventDisplay=upcoming&post_type=' . self::POSTTYPE . '&feed=rss2';
			$newRules[$base . '?$']						= 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=' . $this->getOption('viewOption','month');

			// single ical
			$newRules[$baseSingle . '([^/]+)/ical/?$' ] = 'index.php?post_type=' . self::POSTTYPE . '&name=' . $wp_rewrite->preg_index(1) . '&ical=1';
			
			// taxonomy rules.
			$newRules[$baseTax . '([^/]+)/' . $month] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(1) . '&post_type=' . self::POSTTYPE . '&eventDisplay=month';
			$newRules[$baseTax . '([^/]+)/' . $upcoming . '/page/(\d+)'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(1) . '&post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(2);
			$newRules[$baseTax . '([^/]+)/' . $upcoming] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(1) . '&post_type=' . self::POSTTYPE . '&eventDisplay=upcoming';
			$newRules[$baseTax . '([^/]+)/' . $past . '/page/(\d+)'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(1) . '&post_type=' . self::POSTTYPE . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(2);
			$newRules[$baseTax . '([^/]+)/' . $past] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(1) . '&post_type=' . self::POSTTYPE . '&eventDisplay=past';
			$newRules[$baseTax . '([^/]+)/(\d{4}-\d{2})$'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(1) . '&post_type=' . self::POSTTYPE . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(2);
			$newRules[$baseTax . '([^/]+)/feed/?$'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(1) . '&eventDisplay=upcoming&post_type=' . self::POSTTYPE . '&feed=rss2';
			$newRules[$baseTax . '([^/]+)/?$'] = 'index.php?sp_events_cat=' . $wp_rewrite->preg_index(1) . '&post_type=' . self::POSTTYPE . '&eventDisplay=' . $this->getOption('viewOption','month');
			$newRules[$baseTax . '([^/]+)/ical/?$'] = 'index.php?post_type= ' . self::POSTTYPE . 'eventDisplay=upcoming&sp_events_cat=' . $wp_rewrite->preg_index(1) . '&ical=1';
			$newRules[$baseTax . '([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?post_type= ' . self::POSTTYPE . 'sp_events_cat=' . $wp_rewrite->preg_index(1) . '&feed=' . $wp_rewrite->preg_index(2);
			$newRules[$baseTax . '([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?post_type= ' . self::POSTTYPE . 'sp_events_cat=' . $wp_rewrite->preg_index(1) . '&paged=' . $wp_rewrite->preg_index(2);
			$newRules[$baseTax . '([^/]+)/?$'] = 'index.php?post_type= ' . self::POSTTYPE . 'eventDisplay=upcoming&sp_events_cat=' . $wp_rewrite->preg_index(1);
			
			$wp_rewrite->rules = $newRules + $wp_rewrite->rules;	
		  
		
		
		}
		
		/**
		 * returns various internal events-related URLs
		 * @param string $type type of link. See switch statement for types.
		 * @param string $secondary for $type = month, pass a YYYY-MM string for a specific month's URL
		 */
		
		public function getLink( $type = 'home', $secondary = false ) {

			// if permalinks are off or user doesn't want them: ugly.
			if( '' == get_option('permalink_structure') || 'off' == $this->getOption('useRewriteRules','on') ) {
				return $this->uglyLink($type, $secondary);
			}

			$eventUrl = trailingslashit( home_url() . '/' . $this->rewriteSlug );
			
			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = trailingslashit( get_term_link( get_query_var('term'), self::TAXONOMY ) );
			}
			
			switch( $type ) {
				
				case 'home':
					return $eventUrl;
				case 'month':
					if ( $secondary ) {
						return $eventUrl . $secondary;
					}
					return $eventUrl . 'month/';
				case 'upcoming':
					return $eventUrl . 'upcoming/';
				case 'past':
					return $eventUrl . 'past/';
				case 'dropdown':
					return $eventUrl;
				case 'ical':
					if ( $secondary == 'single' )
						$eventUrl = trailingslashit(get_permalink());
					return $eventUrl . 'ical/';
				default:
					return $eventUrl;
			}
			
		}
		
		private function uglyLink( $type = 'home', $secondary = false ) {
			
			$eventUrl = add_query_arg('post_type', self::POSTTYPE, home_url() );
			
			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = add_query_arg( self::TAXONOMY, get_query_var('term'), $eventUrl );
			}
			
			switch( $type ) {
				
				case 'home':
					return $eventUrl;
				case 'month':
					$month = add_query_arg( array( 'eventDisplay' => 'month'), $eventUrl );
					if ( $secondary )
						$month = add_query_arg( array( 'eventDate' => $secondary ), $month );
					return $month;
				case 'upcoming':
					return add_query_arg( array( 'eventDisplay' => 'upcoming'), $eventUrl );
				case 'past':
					return add_query_arg( array( 'eventDisplay' => 'past'), $eventUrl );
				case 'dropdown':
					$dropdown = add_query_arg( array( 'eventDisplay' => 'month', 'eventDate' => ' '), $eventUrl );
					return rtrim($dropdown); // tricksy
				case 'ical':
					if ( $secondary == 'single' ) {
						return add_query_arg('ical', '1', get_permalink() );
					}
					return home_url() . '/?ical';
				default:
					return $eventUrl;
			}
		}
		
		/**
		 * Returns a link to google maps for the given event
		 *
		 * @param string $postId 
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function get_google_maps_args() {

			$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
			$toUrlEncode = "";
			$languageCode = substr( get_bloginfo( 'language' ), 0, 2 );
			foreach( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func('sp_get_'.$val);
				if ( $metaVal ) 
					$toUrlEncode .= $metaVal . " ";
			}
			if ( $toUrlEncode ) 
				return 'f=q&amp;source=embed&amp;hl=' . $languageCode . '&amp;geocode=&amp;q='. urlencode( trim( $toUrlEncode ) );
			return "";
			
		}
		
		/**
		 * Returns a link to google maps for the given event
		 *
		 * @param string $postId 
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function googleMapLink( $postId = null ) {
			if ( $postId === null || !is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			
			$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
			$toUrlEncode = "";
			foreach( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func('sp_get_'.$val);
				if ( $metaVal ) 
					$toUrlEncode .= $metaVal . " ";
			}
			if ( $toUrlEncode ) 
				return "http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=" . urlencode( trim( $toUrlEncode ) );
			return "";
			
		}
		
		/**
		 * This plugin does not have any deactivation functionality. Any events, categories, options and metadata are
		 * left behind.
		 * 
		 * @return void
		 */
		public function on_deactivate( ) { 
		//	wp_clear_scheduled_hook('reschedule_event_post');

			//remove_filter( 'generate_rewrite_rules', array( $this, 'filterRewriteRules' ) );
			$this->flushRewriteRules();
		}


		/**
		 * Creates the category and sets up the theme resource folder with sample config files.
		 * 
		 * @return void
		 */
		public function on_activate( ) {
			$now = time();
			$firstTime = $now - ($now % 66400);
			//wp_schedule_event( $firstTime, 'daily', 'reschedule_event_post'); // schedule this for midnight, daily
			
		}
		/**
		 * Converts a set of inputs to YYYY-MM-DD HH:MM:SS format for MySQL
		 */
		public function dateToTimeStamp( $date, $hour, $minute, $meridian ) {
			if ( preg_match( '/(PM|pm)/', $meridian ) && $hour < 12 ) $hour += "12";
			if ( preg_match( '/(AM|am)/', $meridian ) && $hour == 12 ) $hour = "00";
			$date = $this->dateHelper($date);
			return "$date $hour:$minute:00";
		}
		public function getTimeFormat( $dateFormat = self::DATEONLYFORMAT ) {
			return $dateFormat . ' ' . get_option( 'time_format', self::TIMEFORMAT );
		}
		/*
		 * ensures date follows proper YYYY-MM-DD format
		 * converts /, - and space chars to -
		**/
		private function dateHelper( $date ) {

			if($date == '')
				return date(self::DBDATEFORMAT);

			$date = str_replace( array('-','/',' ',':','–','—','-'), '-', $date );
			// ensure no extra bits are added
			list($year, $month, $day) = explode('-', $date);
			
			if ( ! checkdate($month, $day, $year) )
				$date = date(self::DBDATEFORMAT); // today's date if error
			else
				$date = $year . '-' . $month . '-' . $day;
	
			return $date;
		}
		
		public function dateOnly( $date ) {
			$date = explode(' ', $date);
			return $date[0];
		}
		
		/**
		 * 
			Adds an alias for get_post_meta so we can do extra stuff to the plugin values.
			If you need the raw unfiltered data, use get_post_meta directly. 
			This is mainly for templates.

		***/
		public function getEventMeta( $id, $meta, $single = true ){
			$use_def_if_empty = sp_get_option('defaultValueReplace');
			if($use_def_if_empty){
				$cleaned_tag = str_replace('_Event','',$meta);
				$default = sp_get_option('eventsDefault'.$cleaned_tag);
				$default = apply_filters('filter_eventsDefault'.$cleaned_tag,$default);
				return (get_post_meta( $id, $meta, $single )) ? get_post_meta( $id, $meta, $single ) : $default;
			}else{
				return get_post_meta( $id, $meta, $single );
			}

		}
		/**
		 * Adds / removes the event details as meta tags to the post.
		 *
		 * @param string $postId 
		 * @return void
		 */
		public function addEventMeta( $postId, $post ) {


			// only continue if it's an event post
			if ( $post->post_type != self::POSTTYPE ) {
				return;
			}
			// don't do anything on autosave or auto-draft either or massupdates
			if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' ) {
				return;
			}
			
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );

			if( $_POST['EventAllDay'] == 'yes' ) {
				$_POST['EventStartDate'] = $this->dateToTimeStamp( $_POST['EventStartDate'], "12", "00", "AM" );
				$_POST['EventEndDate'] = $this->dateToTimeStamp( $_POST['EventEndDate'], "11", "59", "PM" );
			} else {
				delete_post_meta( $postId, '_EventAllDay' );
				$_POST['EventStartDate'] = $this->dateToTimeStamp( $_POST['EventStartDate'], $_POST['EventStartHour'], $_POST['EventStartMinute'], $_POST['EventStartMeridian'] );
				$_POST['EventEndDate'] = $this->dateToTimeStamp( $_POST['EventEndDate'], $_POST['EventEndHour'], $_POST['EventEndMinute'], $_POST['EventEndMeridian'] );
			}
			
			// sanity check that start date < end date
			$startTimestamp = strtotime( $_POST['EventStartDate'] );
			$endTimestamp 	= strtotime( $_POST['EventEndDate'] );
			if ( $startTimestamp > $endTimestamp ) {
				$_POST['EventEndDate'] = $_POST['EventStartDate'];
			}
			if( !isset( $_POST['EventShowMapLink'] ) ) update_post_meta( $postId, '_EventShowMapLink', 'false' );
			if( !isset( $_POST['EventShowMap'] ) ) update_post_meta( $postId, '_EventShowMap', 'false' );
			// give add-on plugins a chance to cancel this meta update


			$_POST['EventOrganizerID'] = $this->save_organizer_data();
			$_POST['EventVenueID'] = $this->save_venue_data();

			try {
				do_action( 'sp_events_event_save', $postId );
				if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
			} catch ( TEC_Post_Exception $e ) {
				$this->postExceptionThrown = true;
				update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
			}

			//update meta fields		
			foreach ( $this->metaTags as $tag ) {
				$htmlElement = ltrim( $tag, '_' );
				if ( isset( $_POST[$htmlElement] ) && $tag != self::EVENTSERROROPT ) {
					if ( is_string($_POST[$htmlElement]) )
						$_POST[$htmlElement] = filter_var($_POST[$htmlElement], FILTER_SANITIZE_STRING);
					
					update_post_meta( $postId, $tag, $_POST[$htmlElement] );
				}
			}
			try {
				do_action( 'sp_events_update_meta', $postId );
				if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
			} catch( TEC_Post_Exception $e ) {
				$this->postExceptionThrown = true;
				update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
			}
		//	update_post_meta( $postId, '_EventCost', sp_get_cost( $postId ) ); // XXX eventbrite cost field

		}
		
		
		//** If you are saving a new venue along with the event, we will do this:
		public function save_venue_data( $postID = null, $post=null ) {
			global $_POST;
		
			// don't do anything on autosave or auto-draft either or massupdates
			// Or inline saves, or data being posted without a venue Or
			// finally, called from the save_post action, but on save_posts that
			// are not venue posts
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' || !$_POST['venue'] ||  ($post->post_type != self::VENUE_POST_TYPE && $postID)) {
				return;
			}

			//There is a possibility to get stuck in an infinite loop. 
			//That would be bad.
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );

			$data = stripslashes_deep($_POST['venue']);

			if($data['VenueID'])
				return $data['VenueID'];

			if ( $post->post_type == self::VENUE_POST_TYPE && $postID) {
				$data['VenueID'] = $postID;
			}

			// make state and province mutually exclusive
			$data['StateProvince'] = ( $data['Country'] != 'United States' )? $data['Province'] : $data['State'];

			//google map checkboxes
			$postdata = array(
				'post_title' => $data['Venue'],
				'post_type' => self::VENUE_POST_TYPE,
				'post_status' => 'publish',
				'ID' => $data['VenueID']
			);

			$venue_id = wp_insert_post($postdata, true);

			foreach ($data as $key => $var) {
				update_post_meta($venue_id, '_Venue'.$key, $var);
			}

			//do_action( 'sp_events_venue_save', $venue_id );

			return $venue_id;
		}

		function get_venue_info($p = null){
			$r = new WP_Query(array('post_type' => self::VENUE_POST_TYPE, 'nopaging' => 1, 'post_status' => 'publish', 'caller_get_posts' => 1,'orderby'=>'post_title','p' => $p));
			if ($r->have_posts()) :
				return $r->posts;
			endif;
			return false;
		}

		function saved_venues_dropdown($current = null){
			$venues = $this->get_venue_info();
			if($venues){
				echo '<select name="venue[VenueID]" id="saved_venue">';
					echo '<option value="0">Use New Venue</option>';
				foreach($venues as $venue){
					$selected = ($current == $venue->ID) ? 'selected="selected"' : '';
					echo "<option value='{$venue->ID}' $selected>{$venue->post_title}</option>";
				}
				echo '</select>';
			}else{
				echo '<p class="nosaved">'.__('No saved venues yet.',$this->lion).'</p>';
			}
		}


		//** If you are saving a new organizer along with the event, we will do this:
		public function save_organizer_data( $postID = null, $post=null ) {
			global $_POST;
		
			// don't do anything on autosave or auto-draft either or massupdates
			// Or inline saves, or data being posted without a organizer Or
			// finally, called from the save_post action, but on save_posts that
			// are not organizer posts
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || $_REQUEST['action'] == 'inline-save' || !$_POST['organizer'] ||  ($post->post_type != self::ORGANIZER_POST_TYPE && $postID)) {
				return;
			}
			//There is a possibility to get stuck in an infinite loop. 
			//That would be bad.
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );

			$data = stripslashes_deep($_POST['organizer']);

			if($data['OrganizerID'])
				return $data['OrganizerID'];

			if ( $post->post_type == self::ORGANIZER_POST_TYPE && $postID) {
				$data['OrganizerID'] = $postID;
			}

			//google map checkboxes
			$postdata = array(
				'post_title' => $data['Organizer'],
				'post_type' => self::ORGANIZER_POST_TYPE,
				'post_status' => 'publish',
				'ID' => $data['OrganizerID']
			);

			$organizer_id = wp_insert_post($postdata, true);

			foreach ($data as $key => $var) {
				update_post_meta($organizer_id, '_Organizer'.$key, $var);
			}

			//do_action( 'sp_events_organizer_save', $organizer_id, $data['Organizer']);

			return $organizer_id;
		}

		function get_organizer_info($p = null){
			$r = new WP_Query(array('post_type' => self::ORGANIZER_POST_TYPE, 'nopaging' => 1, 'post_status' => 'publish', 'caller_get_posts' => 1,'orderby'=>'post_title','p' => $p));
			if ($r->have_posts()) :
				return $r->posts;
			endif;
			return false;
		}

		function saved_organizers_dropdown($current = null){
			$organizers = $this->get_organizer_info();
			if($organizers){
				echo '<select name="organizer[OrganizerID]" id="saved_organizer">';
					echo '<option value="0">Use New Organizer</option>';
				foreach($organizers as $organizer){
					$selected = ($current == $organizer->ID) ? 'selected="selected"' : '';
					echo "<option value='{$organizer->ID}' $selected>{$organizer->post_title}</option>";
				}
				echo '</select>';
			}else{
				echo '<p class="nosaved_organizer">'.__('No saved organizers yet.',$this->lion).'</p>';
			}
		}

		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function EventsChooserBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			foreach ( $this->metaTags as $tag ) {
				if ( $postId && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
					$$tag = get_post_meta( $postId, $tag, true );
				} else {
					$cleaned_tag = str_replace('_Event','',$tag);
					$$tag = sp_get_option('eventsDefault'.$cleaned_tag);
				}
			}
			if($_EventVenueID){
				foreach($this->venueTags as $tag)
					$$tag = get_post_meta($_EventVenueID, $tag, true );

			}else{
				foreach ( $this->legacyVenueTags as $tag ) {
					if ( $postId && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
						$cleaned_tag = str_replace('_Event','_Venue',$tag);
						$$cleaned_tag = get_post_meta( $postId, $tag, true );
					} else {
						$cleaned_tag = str_replace('_Event','',$tag);
						${'_Venue'.$cleaned_tag} = sp_get_option('eventsDefault'.$cleaned_tag);
					}
				}
			}
	/*
			foreach($this->organizerTags as $tag)
				$$tag = get_post_meta($_EventOrganizerID, $tag, true );*/

			$isEventAllDay = ( $_EventAllDay == 'yes' || ! $this->dateOnly( $_EventStartDate ) ) ? 'checked="checked"' : ''; // default is all day for new posts
			
			$startDayOptions       	= array(
										31 => $this->getDayOptions( $_EventStartDate, 31 ),
										30 => $this->getDayOptions( $_EventStartDate, 30 ),
										29 => $this->getDayOptions( $_EventStartDate, 29 ),
										28 => $this->getDayOptions( $_EventStartDate, 28 )
									  );
			$endDayOptions			= array(
										31 => $this->getDayOptions( $_EventEndDate, 31 ),
										30 => $this->getDayOptions( $_EventEndDate, 30 ),
										29 => $this->getDayOptions( $_EventEndDate, 29 ),
										28 => $this->getDayOptions( $_EventEndDate, 28 )
									  );
			$startMonthOptions 		= $this->getMonthOptions( $_EventStartDate );
			$endMonthOptions 		= $this->getMonthOptions( $_EventEndDate );
			$startYearOptions 		= $this->getYearOptions( $_EventStartDate );
			$endYearOptions		 	= $this->getYearOptions( $_EventEndDate );
			$startMinuteOptions 	= $this->getMinuteOptions( $_EventStartDate );
			$endMinuteOptions 		= $this->getMinuteOptions( $_EventEndDate );
			$startHourOptions	 	= $this->getHourOptions( $_EventStartDate, true );
			$endHourOptions		 	= $this->getHourOptions( $_EventEndDate );
			$startMeridianOptions	= $this->getMeridianOptions( $_EventStartDate, true );
			$endMeridianOptions		= $this->getMeridianOptions( $_EventEndDate );
			
			$start = $this->dateOnly($_EventStartDate);
			$EventStartDate = ( $start ) ? $start : date('Y-m-d');
			
			$end = $this->dateOnly($_EventEndDate);
			$EventEndDate = ( $end ) ? $end : date('Y-m-d', strtotime('tomorrow') );

			include( $this->pluginPath . 'views/events-meta-box.php' );
		}
		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function VenueMetaBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			if($post->post_type == self::VENUE_POST_TYPE){
					
				foreach ( $this->venueTags as $tag ) {
					if ( $postId && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
						$$tag = get_post_meta( $postId, $tag, true );
					} else {
						$cleaned_tag = str_replace('_Venue','',$tag);
						$$tag = sp_get_option('eventsDefault'.$cleaned_tag);
					}
				}
			}
			?>
				<style type="text/css">
						#EventInfo {border:none;}
				</style>
				<div id='eventDetails' class="inside eventForm">	
					<table cellspacing="0" cellpadding="0" id="EventInfo" class="VenueInfo">
					<?php
					include( $this->pluginPath . 'views/venue-meta-box.php' );
					?>
					</table>
				</div>
			<?php
		}		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function OrganizerMetaBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			if($post->post_type == self::ORGANIZER_POST_TYPE){
					
				foreach ( $this->organizerTags as $tag ) {
					if ( $postId && $_GET['post'] ) { //if there is a post AND the post has been saved at least once.
						$$tag = get_post_meta( $postId, $tag, true );
					}
				}
			}
			?>
				<style type="text/css">
						#EventInfo {border:none;}
				</style>
				<div id='eventDetails' class="inside eventForm">	
					<table cellspacing="0" cellpadding="0" id="EventInfo" class="OrganizerInfo">
					<?php
					include( $this->pluginPath . 'views/organizer-meta-box.php' );
					?>
					</table>
				</div>
			<?php
		}
		
		/**
		 * Given a date (YYYY-MM-DD), returns the first of the next month
		 *
		 * @param date
		 * @return date
		 */
		public function nextMonth( $date ) {
			$dateParts = split( '-', $date );
			if ( $dateParts[1] == 12 ) {
				$dateParts[0]++;
				$dateParts[1] = "01";
				$dateParts[2] = "01";
			} else {
				$dateParts[1]++;
				$dateParts[2] = "01";
			}
			if ( $dateParts[1] < 10 && strlen( $dateParts[1] ) == 1 ) {
				$dateParts[1] = "0" . $dateParts[1];
			}
			$return =  $dateParts[0] . '-' . $dateParts[1];
			return $return;
		}
		/**
		 * Given a date (YYYY-MM-DD), return the first of the previous month
		 *
		 * @param date
		 * @return date
		 */
		public function previousMonth( $date ) {
			$dateParts = split( '-', $date );

			if ( $dateParts[1] == 1 ) {
				$dateParts[0]--;
				$dateParts[1] = "12";
				$dateParts[2] = "01";
			} else {
				$dateParts[1]--;
				$dateParts[2] = "01";
			}
			if ( $dateParts[1] < 10 ) {
				$dateParts[1] = "0" . $dateParts[1];
			}
			$return =  $dateParts[0] . '-' . $dateParts[1];

			return $return;
		}

		/**
		 * Callback for adding the Meta box to the admin page
		 * @return void
		 */
		public function addEventBox( ) {
			add_meta_box( 'Event Details', $this->pluginName, array( $this, 'EventsChooserBox' ), self::POSTTYPE, 'normal', 'high' );
			add_meta_box( 'Venue Details', 'Venue Information', array( $this, 'VenueMetaBox' ), self::VENUE_POST_TYPE, 'normal', 'high' );
			add_meta_box( 'Organizer Details', 'Organizer Information', array( $this, 'OrganizerMetaBox' ), self::ORGANIZER_POST_TYPE, 'normal', 'high' );
		}
		/** 
		 * Builds a set of options for diplaying a meridian chooser
		 *
		 * @param string YYYY-MM-DD HH:MM:SS to select (optional)
		 * @return string a set of HTML options with all meridians 
		 */
		public function getMeridianOptions( $date = "", $isStart = false ) {
			if( strstr( get_option( 'time_format', self::TIMEFORMAT ), 'A' ) ) {
				$a = 'A';
				$meridians = array( "AM", "PM" );
			} else {
				$a = 'a';
				$meridians = array( "am", "pm" );
			}
			if ( empty( $date ) ) {
				$meridian = ( $isStart ) ? $meridians[0] : $meridians[1];
			} else {
				$meridian = date($a, strtotime( $date ) );
			}
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
		 * @param string the current date to select  (optional)
		 * @return string a set of HTML options with all months (current month selected)
		 */
		public function getMonthOptions( $date = "" ) {
			$months = $this->monthNames();
			$options = '';
			if ( empty( $date ) ) {
				$month = ( date_i18n( 'j' ) == date_i18n( 't' ) ) ? date( 'F', time() + 86400 ) : date_i18n( 'F' );
			} else {
				$month = date( 'F', strtotime( $date ) );
			}
			$monthIndex = 1;
			foreach ( $months as $englishMonth => $monthText ) {
				if ( $monthIndex < 10 ) { 
					$monthIndex = "0" . $monthIndex;  // need a leading zero in the month
				}
				if ( $month == $englishMonth ) {
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
		public function getDayOptions( $date = "", $totalDays = 31 ) {
			$days = $this->days( $totalDays );
			$options = '';
			if ( empty ( $date ) ) {
				$day = date_i18n( 'j' );
				if( $day == date_i18n( 't' ) ) $day = '01';
				elseif ( $day < 9 ) $day = '0' . ( $day + 1 );
				else $day++;
			} else {
				$day = date( 'd', strtotime( $date) );
			}
			foreach ( $days as $dayText ) {
				if ( $dayText < 10 ) { 
					$dayText = "0" . $dayText;  // need a leading zero in the day
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
		 * @param string the current date (optional)
		 * @return string a set of HTML options with adjacent years (current year selected)
		 */
		public function getYearOptions( $date = "" ) {
			$years = $this->years();
			$options = '';
			if ( empty ( $date ) ) {
				$year = date_i18n( 'Y' );
				if( date_i18n( 'n' ) == 12 && date_i18n( 'j' ) == 31 ) $year++;
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
		 * Builds a set of options for displaying an hour chooser
		 * @param string the current date (optional)
		 * @return string a set of HTML options with hours (current hour selected)
		 */
		public function getHourOptions( $date = "", $isStart = false ) {
			$hours = $this->hours();
			if( count($hours) == 12 ) $h = 'h';
			else $h = 'H';
			$options = '';
			if ( empty ( $date ) ) {
				$hour = ( $isStart ) ? '08' : '05';
			} else {
				$timestamp = strtotime( $date );
				$hour = date( $h, $timestamp );
				// fix hours if time_format has changed from what is saved
				if( preg_match('(pm|PM)', $timestamp) && $h == 'H') $hour = $hour + 12;
				if( $hour > 12 && $h == 'h' ) $hour = $hour - 12;
				
			}
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
		 * @param string the current date (optional)
		 * @return string a set of HTML options with minutes (current minute selected)
		 */
		public function getMinuteOptions( $date = "" ) {
			$minutes = $this->minutes();
			$options = '';
			if ( empty ( $date ) ) {
				$minute = '00';
			} else {
				$minute = date( 'i', strtotime( $date ) ); 
			}
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
	     */
	    public function hours() {
	      $hours = array();
		  $rangeMax = ( strstr( get_option( 'time_format', self::TIMEFORMAT ), 'H' ) ) ? 23 : 12;
	      foreach(range(1,$rangeMax) as $hour) {
			if ( $hour < 10 ) {
				$hour = "0".$hour;
			}
	        $hours[$hour] = $hour;
	      }
	      return $hours;
	    }
		/**
	     * Helper method to return an array of 00-59 for minutes
	     */
	    public static function minutes( ) {
	      $minutes = array();
	      for($minute=0; $minute < 60; $minute+=5) {
					if ($minute < 10) {
						$minute = "0" . $minute;
					}
	        $minutes[$minute] = $minute;
	      }
	      return $minutes;
	    }
		/**
		 * Sets event options based on the current query string
		 *
		 * @return void
		 */
		public function setOptions( ) {
			global $wp_query;
			if ( is_admin() ) {
				$display = 'admin';
			}
			else {
				$display = ( isset( $wp_query->query_vars['eventDisplay'] ) ) ? $wp_query->query_vars['eventDisplay'] : $this->getOption('viewOption','month');
			}
			
			switch ( $display ) {
				case "past":
					$this->displaying		= "past";
					$this->startOperator	= "<=";
					$this->order			= "DESC";
					$this->date				= date_i18n( self::DBDATETIMEFORMAT );
					break;
				case "upcoming":
					$this->displaying		= "upcoming";					
					$this->startOperator	= ">=";
					$this->order			= "ASC";
					$this->date				= date_i18n( self::DBDATETIMEFORMAT );
					break;					
				case "month":
					$this->displaying		= "month";
					$this->startOperator	= ">=";
					$this->order			= "ASC";
					// TODO date set to YYYY-MM
					// TODO store DD as an anchor to the URL
					if ( isset ( $wp_query->query_vars['eventDate'] ) ) {
						$this->date = $wp_query->query_vars['eventDate'] . "-01";
					} else {
						$date = date_i18n( self::DBDATEFORMAT );
						$this->date = substr_replace( $date, '01', -2 );
					}
					break;
				default:
					$this->displaying		= "month";
					$this->startOperator	= ">=";
					$this->order			= "DESC";
					// TODO date set to YYYY-MM
					// TODO store DD as an anchor to the URL
					if ( isset ( $wp_query->query_vars['eventDate'] ) ) {
						$this->date = $wp_query->query_vars['eventDate'] . "-01";
					} else {
						$date = date_i18n( self::DBDATEFORMAT );
						$this->date = substr_replace( $date, '01', -2 );
					}
			}
		}
		public function getDateString( $date ) {
			$monthNames = $this->monthNames();
			$dateParts = split( '-', $date );
			$timestamp = mktime( 0, 0, 0, $dateParts[1], 1, $dateParts[0] );
			return $monthNames[date( "F", $timestamp )] . " " . $dateParts[0];
		}
		/**
	     * echo the next tab index
		 * @return void
		 */
		public function tabIndex() {
			echo $this->tabIndexStart;
			$this->tabIndexStart++;
		}
		
		/**
		 * Call this function in a template to query the events
		 *
		 * @param int numResults number of results to display for upcoming or past modes (default 10)
		 * @param string|int eventCat Event Category: use int for term ID, string for name.
		 * @param string metaKey A meta key to query. Useful for sorting by country, venue, etc. metaValue must also be set to use.
		 * @param string metaValue The value of the queried metaKey, which also must be set.
		 * @return array results
		 * @uses $wpdb
		 * @uses $wp_query
		 * @return array results
		 */
		
		public function getEvents( $args = '' ) {
			$defaults = array(
				'numResults' => get_option( 'posts_per_page', 10 ),
				'eventCat' => null,
				'metaKey' => null,
				'metaValue' => null,
				'time_order' => $this->order
			);
			$args = wp_parse_args( $args, $defaults);
			extract( $args );
			global $wpdb;
			$this->setOptions();

			$extraSelectClause ='';
			$extraJoin ='';
			if ( sp_is_month() ) {
				$extraSelectClause	= ", d2.meta_value as EventEndDate ";
				$extraJoin	 = " LEFT JOIN $wpdb->postmeta  as d2 ON($wpdb->posts.ID = d2.post_id) ";
				$whereClause = " AND d1.meta_key = '_EventStartDate' AND d2.meta_key = '_EventEndDate' ";
				// does this event start in this month?
				$whereClause .= " AND ((d1.meta_value >= '".$this->date."'  AND  d1.meta_value < '".$this->nextMonth( $this->date )."')  ";
				// Or does it end in this month?
				$whereClause .= " OR (d2.meta_value  >= '".$this->date."' AND d2.meta_value < '".$this->nextMonth( $this->date )."' ) ";
				// Or does the event start sometime in the past and end sometime in the distant future?
				$whereClause .= " OR (d1.meta_value  <= '".$this->date."' AND d2.meta_value > '".$this->nextMonth( $this->date )."' ) ) ";
				$numResults = 999999999;
			}
			if ( sp_is_upcoming() ) {
				$extraSelectClause	= ", d2.meta_value as EventEndDate ";
				$extraJoin	 = " LEFT JOIN $wpdb->postmeta  as d2 ON($wpdb->posts.ID = d2.post_id) ";
				$whereClause = " AND d1.meta_key = '_EventStartDate' AND d2.meta_key = '_EventEndDate' ";
				// Is the start date in the future?
				$whereClause .= ' AND ( d1.meta_value > "'.$this->date.'" ';
				// Or is the start date in the past but the end date in the future? (meaning the event is currently ongoing)
				$whereClause .= ' OR ( d1.meta_value < "'.$this->date.'" AND d2.meta_value > "'.$this->date.'" ) ) ';
			}

			// we have an event cat. what is it?
			if ( $eventCat ) {
				if ( is_int($eventCat) )
					$cat = get_term_by('id', $eventCat, self::TAXONOMY );
				else if ( is_string($eventCat) )
					$cat = get_term_by('name', $eventCat, self::TAXONOMY );
			}

			// we really have an event cat. query it.
			if ( $cat && ! is_wp_error($cat) ) {
				$extraJoin .= " LEFT JOIN {$wpdb->term_relationships} as r2 ON ($wpdb->posts.ID = r2.object_ID) ";
				$extraJoin .= " LEFT JOIN {$wpdb->term_taxonomy} as t2 ON (r2.term_taxonomy_id = t2.term_taxonomy_id) ";
				$extraJoin .= " LEFT JOIN {$wpdb->terms} as tax ON (t2.term_id = tax.term_id) ";
				// don't need to bother WHERE'ing the taxonomy type, since we wouldn't be this far if it didn't fit anyway
				$whereClause .= $wpdb->prepare(" AND t2.term_id = %s ", $cat->term_id );
			}
			
			// query some meta values
			if ( $metaKey && $metaValue ) {
				$extraJoin .= " LEFT JOIN $wpdb->postmeta as p2 ON ($wpdb->posts.ID = p2.post_id) \n";
				$whereClause .= $wpdb->prepare(" AND p2.meta_key = %s \n", $metaKey );
				$whereClause .= $wpdb->prepare(" AND p2.meta_value = %s \n", $metaValue );
			}
			
			$eventsQuery = "
				SELECT $wpdb->posts.*, d1.meta_value as EventStartDate
					$extraSelectClause
				 	FROM $wpdb->posts 
				LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id)
				$extraJoin
				WHERE $wpdb->posts.post_type = '" . self::POSTTYPE . "'
				AND $wpdb->posts.post_status = 'publish'
				$whereClause
				ORDER BY DATE(d1.meta_value) ".$this->order.", TIME(d1.meta_value) $time_order
				LIMIT $numResults";
			$results = $wpdb->get_results($eventsQuery, OBJECT);
			return $results;
		}
		
		public function isEvent( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::POSTTYPE ) {
				return true;
			}
			return false;
		}

		/**
		** Get a "previous/next post" link for events. Ordered by start date instead of ID.
		**/

		public function get_event_link($id, $mode = 'next',$anchor = 'Next Event'){
			global $wpdb;

			if($mode == 'previous'){
				$order = 'DESC';
				$sign = '<';
			}else{
				$order = 'ASC';
				$sign = '>';
			}

			$date = get_post_meta($id,'_EventStartDate',true);
			$eventsQuery = "
				SELECT $wpdb->posts.ID, post_title, d1.meta_value as EventStartDate
				FROM $wpdb->posts 
				LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id)
				WHERE $wpdb->posts.post_type = '".self::POSTTYPE."'
				AND d1.meta_key = '_EventStartDate'
				AND ((d1.meta_value = '" .$date . "' AND ID $sign ".$id.") OR
					d1.meta_value $sign '" .$date . "')
				AND $wpdb->posts.post_status = 'publish'
				ORDER BY TIMESTAMP(d1.meta_value) $order, ID $order
				LIMIT 1";
				$results = $wpdb->get_row($eventsQuery, OBJECT);

			echo '<a href='.get_permalink($results->ID).'>'.$anchor.'</a>';
		}
		
		/**
		 * build an ical feed from events posts
		 */
		public function iCalFeed( $postId = null, $eventCatSlug = null ) {
		    $getstring = $_GET['ical'];
			$wpTimezoneString = get_option("timezone_string");
			$postType = self::POSTTYPE;
			$events = "";
			$lastBuildDate = "";
			$eventsTestArray = array();
			$blogHome = get_bloginfo('home');
			$blogName = get_bloginfo('name');
			$includePosts = ( $postId ) ? '&include=' . $postId : '';
			$eventsCats = ( $eventCatSlug ) ? '&'.self::TAXONOMY.'='.$eventCatSlug : '';
			
			$eventPosts = get_posts( 'numberposts=-1&post_type=' . $postType . $includePosts . $eventsCats );
			foreach( $eventPosts as $eventPost ) {
				// convert 2010-04-08 00:00:00 to 20100408T000000 or YYYYMMDDTHHMMSS
				$startDate = str_replace( array("-", " ", ":") , array("", "T", "") , get_post_meta( $eventPost->ID, "_EventStartDate", true) );
				$endDate = str_replace( array("-", " ", ":") , array("", "T", "") , get_post_meta( $eventPost->ID, "_EventEndDate", true) );
				if( get_post_meta( $eventPost->ID, "_EventAllDay", true ) == "yes" ) {
					$startDate = substr( $startDate, 0, 8 );
					$endDate = substr( $endDate, 0, 8 );
					// endDate bumped ahead one day to counter iCal's off-by-one error
					$endDateStamp = strtotime($endDate);
					$endDate = date( 'Ymd', $endDateStamp + 86400 );
				}
				$description = preg_replace("/[\n\t\r]/", " ", strip_tags( $eventPost->post_content ) );
				//$cost = get_post_meta( $eventPost->ID, "_EventCost", true);
				//if( $cost ) $description .= " Cost: " . $cost;
				// add fields to iCal output
				$events .= "BEGIN:VEVENT\n";
				$events .= "DTSTART;VALUE=DATE:" . $startDate . "\n";
				$events .= "DTEND;VALUE=DATE:" . $endDate . "\n";
				$events .= "DTSTAMP:" . date("Ymd\THis", time()) . "\n";
				$events .= "CREATED:" . str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_date ) . "\n";
				$events .= "LAST-MODIFIED:". str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_modified ) . "\n";
		        $events .= "UID:" . $eventPost->ID . "@" . $blogHome . "\n";
		        $events .= "SUMMARY:" . $eventPost->post_title . "\n";				
		        $events .= "DESCRIPTION:" . $description . "\n";
				$events .= "LOCATION:" . sp_get_address( $eventPost->ID ) . "\n";
				$events .= "URL:" . get_permalink( $eventPost->ID ) . "\n";
		        $events .= "END:VEVENT\n";
			}
	        header('Content-type: text/calendar');
	        header('Content-Disposition: attachment; filename="iCal-Events_Calendar_Pro.ics"');
			$content = "BEGIN:VCALENDAR\n";
			$content .= "VERSION:2.0\n";
			$content .= "PRODID:-//" . $blogName . "//NONSGML v1.0//EN\n";
			$content .= "CALSCALE:GREGORIAN\n";
			$content .= "METHOD:PUBLISH\n";
			$content .= "X-WR-CALNAME:" . $blogName . "\n";
			$content .= "X-ORIGINAL-URL:" . $blogHome . "\n";
			$content .= "X-WR-CALDESC:Events for " . $blogName . "\n";
			if( $wpTimezoneString ) $content .= "X-WR-TIMEZONE:" . $wpTimezoneString . "\n";
			$content .= $events;
			$content .= "END:VCALENDAR";
			echo $content;
			exit;
		}
		public function setPostExceptionThrown( $thrown ) {
			$this->postExceptionThrown = $thrown;
		}
		public function getPostExceptionThrown() {
			return $this->postExceptionThrown;
		}
	} // end Events_Calendar_Pro class
	global $sp_ecp;
	$sp_ecp = new Events_Calendar_Pro();
	add_filter('generate_rewrite_rules', array(&$sp_ecp,'filterRewriteRules'));
} // end if !class_exists Events_Calendar_Pro