<?php
if ( !class_exists( 'The_Events_Calendar' ) ) {
	/**
	 * Main plugin
	 */
	class The_Events_Calendar {
		const EVENTSERROROPT		= '_tec_events_errors';
		const CATEGORYNAME	 		= 'Events';
		const OPTIONNAME 			= 'sp_events_calendar_options';
		// default formats, they are overridden by WP options or by arguments to date methods
		const DATEONLYFORMAT 		= 'F j, Y';
		const TIMEFORMAT			= 'g:i A';
		const DBDATEFORMAT	 		= 'Y-m-d';
		const DBDATETIMEFORMAT 		= 'Y-m-d G:i:s';

		private $defaultOptions = '';
		public $latestOptions;
		private $postExceptionThrown = false;
		private $optionsExceptionThrown = false;
		public $displaying;
		public $pluginDir;
		public $pluginUrl;
		public $pluginDomain = 'the-events-calendar';
		private $tabIndexStart = 2000;

		public $metaTags = array(
					'_isEvent',
					'_EventAllDay',
					'_EventStartDate',
					'_EventEndDate',
					'_EventVenue',
					'_EventCountry',
					'_EventAddress',
					'_EventCity',
					'_EventState',
					'_EventProvince',
					'_EventZip',
					'_EventShowMapLink',
					'_EventShowMap',
					'_EventCost',
					'_EventPhone',
					self::EVENTSERROROPT
				);
				
		public $currentPostTimestamp;
		public $daysOfWeekShort;
		public $daysOfWeek;
		private function constructDaysOfWeek() {
			$this->daysOfWeekShort = array( __( 'Sun', $this->pluginDomain ), __( 'Mon', $this->pluginDomain ), __( 'Tue', $this->pluginDomain ), __( 'Wed', $this->pluginDomain ), __( 'Thu', $this->pluginDomain ), __( 'Fri', $this->pluginDomain ), __( 'Sat', $this->pluginDomain ) );
			$this->daysOfWeek = array( __( 'Sunday', $this->pluginDomain ), __( 'Monday', $this->pluginDomain ), __( 'Tuesday', $this->pluginDomain ), __( 'Wednesday', $this->pluginDomain ), __( 'Thursday', $this->pluginDomain ), __( 'Friday', $this->pluginDomain ), __( 'Saturday', $this->pluginDomain ) );
		}
		
		private $countries;
		private function constructCountries( $postId = "", $useDefault = true ) {
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
					if ( $postId || $useDefault ) {
						$countryValue = get_post_meta( $postId, '_EventCountry', true );
						if( $countryValue ) $defaultCountry = array( array_search( $countryValue, $countries ), $countryValue );
						else $defaultCountry = eventsGetOptionValue('defaultCountry');
						if( $defaultCountry && $defaultCountry[0] != "" ) {
							$selectCountry = array_shift( $countries );
							asort($countries);
							$countries = array($defaultCountry[0] => __($defaultCountry[1], $this->pluginDomain)) + $countries;
							$countries = array("" => __($selectCountry, $this->pluginDomain)) + $countries;
							array_unique($countries);
						}
						$this->countries = $countries;
					} else {
						$this->countries = $countries;
					}
		}
		/**
		 * Initializes plugin variables and sets up wordpress hooks/actions.
		 *
		 * @return void
		 */
		function __construct( ) {
			$this->currentDay		= '';
			$this->pluginDir		= basename(dirname(__FILE__));
			$this->pluginUrl 		= WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			$this->errors			= '';
			register_deactivation_hook( __FILE__, 	array( &$this, 'on_deactivate' ) );
			add_action( 'reschedule_event_post', array( $this, 'reschedule') );
			add_action( 'init',				array( $this, 'loadDomainStylesScripts' ) );
			add_action( 'sp-events-save-more-options', array( $this, 'flushRewriteRules' ) );
			add_action( 'pre_get_posts',	array( $this, 'setOptions' ) );
			add_action( 'admin_menu', 		array( $this, 'addOptionsPage' ) );
			add_action( 'admin_init', 		array( $this, 'checkForOptionsChanges' ) );
			add_action( 'wp_ajax_hideDonate', array( $this, 'storeHideDonate'));
			add_action( 'admin_menu', 		array( $this, 'addEventBox' ) );
			add_action( 'save_post',		array( $this, 'addEventMeta' ), 15 );
			add_action( 'publish_post',		array( $this, 'addEventMeta' ), 15 );
			add_filter( 'generate_rewrite_rules', array( $this, 'filterRewriteRules' ) );
			add_filter( 'query_vars',		array( $this, 'eventQueryVars' ) );			
			add_filter( 'posts_join',		array( $this, 'events_search_join' ) );
			add_filter( 'posts_where',		array( $this, 'events_search_where' ) );
			add_filter( 'posts_orderby',	array( $this, 'events_search_orderby' ) );
			add_filter( 'posts_fields',		array( $this, 'events_search_fields' ) );
			add_filter( 'post_limits',		array( $this, 'events_search_limits' ) );
			add_action( 'template_redirect',array($this, 'templateChooser' ), 1 );
			add_action( 'pre_get_posts',		array( $this, 'events_home_cat_excluder' ) );
			add_action( 'sp_events_post_errors', array( 'TEC_Post_Exception', 'displayMessage' ) );
			add_action( 'sp_events_options_top', array( 'TEC_WP_Options_Exception', 'displayMessage') );
		}
		
		public function addOptionsPage() {
    		add_options_page('The Events Calendar', 'The Events Calendar', 'administrator', basename(__FILE__), array($this,'optionsPageView'));		
		}
		
		public function optionsPageView() {
			include( dirname( __FILE__ ) . '/views/events-options.php' );
		}
		
		public function checkForOptionsChanges() {
			if (isset($_POST['saveEventsCalendarOptions']) && check_admin_referer('saveEventsCalendarOptions')) {
                $options = $this->getOptions();
				$options['viewOption'] = $_POST['viewOption'];
				if($_POST['defaultCountry']) {
					$this->constructCountries();
					$defaultCountryKey = array_search($_POST['defaultCountry'],$this->countries);
					$options['defaultCountry'] = array($defaultCountryKey,$_POST['defaultCountry']);					
				}
				
				$options['embedGoogleMaps'] = $_POST['embedGoogleMaps'];
				if($_POST['embedGoogleMapsHeight']) {
					$options['embedGoogleMapsHeight'] = $_POST['embedGoogleMapsHeight'];
					$options['embedGoogleMapsWidth'] = $_POST['embedGoogleMapsWidth'];
				}
				
				$options['showComments'] = $_POST['showComments'];
				$options['displayEventsOnHomepage'] = $_POST['displayEventsOnHomepage'];
				$options['resetEventPostDate'] = $_POST['resetEventPostDate'];
				$options['useRewriteRules'] = $_POST['useRewriteRules'];
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
		
		public function storeHideDonate() {
			if ( $_POST['donateHidden'] ) {
                $options = $this->getOptions();
				$options['donateHidden'] = true;
				
				$this->saveOptions($options);
			} // end if
		}
		
		/// OPTIONS DATA
        public function getOptions() {
            if ('' === $this->defaultOptions) {
                $this->defaultOptions = get_option(The_Events_Calendar::OPTIONNAME, array());
            }
            return $this->defaultOptions;
        }
		        
        private function saveOptions($options) {
            if (!is_array($options)) {
                return;
            }
            if ( update_option(The_Events_Calendar::OPTIONNAME, $options) ) {
				$this->latestOptions = $options;
			} else {
				$this->latestOptions = $this->getOptions();
			}
        }
        
        public function deleteOptions() {
            delete_option(The_Events_Calendar::OPTIONNAME);
        }

		public function templateChooser() {
			if( is_feed() ) {
				return;
			}
			$this->constructDaysOfWeek();
			// list view
			if ( $this->in_event_category() && ( events_displaying_upcoming() || events_displaying_past() ) ) {
				if( '' == locate_template( array( 'events/list.php' ), true ) ) {
					load_template( dirname( __FILE__ ) . '/views/list.php' );
				}
				exit;
	        }    
	        // grid view
			if ( $this->in_event_category() ) {
				if( '' == locate_template( array( 'events/gridview.php' ), true ) ) {
					load_template( dirname( __FILE__ ) . '/views/gridview.php' );
				}
				exit;
	        }    
			// single event
			if (is_single() && in_category( $this->eventCategory() ) ) {
				if( '' == locate_template( array( 'events/single.php' ), true ) ) {
					load_template( dirname( __FILE__ ) . '/views/single.php' );
				}
				exit;
			}
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
		
		public function loadDomainStylesScripts() {
			load_plugin_textdomain( $this->pluginDomain, false, basename(dirname(__FILE__)) . '/lang/');
			$eventsURL = trailingslashit( WP_PLUGIN_URL ) . trailingslashit( plugin_basename( dirname( __FILE__ ) ) ) . 'resources/';
			wp_enqueue_script('sp-events-calendar-script', $eventsURL.'events.js', array('jquery') );
			wp_enqueue_style('sp-events-calendar-style', $eventsURL.'events.css');
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

		/**
		 * Helper method to return an array of translated month names or short month names
		 * @return Array translated month names
		 */
		public function monthNames( $short = false ) {
			if($short) {
				$months = array( 'Jan'	=> __('Jan', $this->pluginDomain), 
							  	 'Feb' 	=> __('Feb', $this->pluginDomain), 
							     'Mar' 	=> __('Mar', $this->pluginDomain), 
							     'Apr' 	=> __('Apr', $this->pluginDomain), 
							     'May'  => __('May', $this->pluginDomain), 
							     'Jun' 	=> __('Jun', $this->pluginDomain), 
							     'Jul'	=> __('Jul', $this->pluginDomain), 
							     'Aug' 	=> __('Aug', $this->pluginDomain), 
							     'Sep' 	=> __('Sep', $this->pluginDomain), 
							     'Oct' 	=> __('Oct', $this->pluginDomain), 
							     'Nov' 	=> __('Nov', $this->pluginDomain), 
							     'Dec' 	=> __('Dec', $this->pluginDomain) 
						     );
			} else {
				$months = array( 'January' 	    => __('January', $this->pluginDomain), 
							  	 'February' 	=> __('February', $this->pluginDomain), 
							     'March' 		=> __('March', $this->pluginDomain), 
							     'April' 		=> __('April', $this->pluginDomain), 
							     'May' 		    => __('May', $this->pluginDomain), 
							     'June' 		=> __('June', $this->pluginDomain), 
							     'July'	        => __('July', $this->pluginDomain), 
							     'August' 		=> __('August', $this->pluginDomain), 
							     'September' 	=> __('September', $this->pluginDomain), 
							     'October' 	    => __('October', $this->pluginDomain), 
							     'November' 	=> __('November', $this->pluginDomain), 
							     'December' 	=> __('December', $this->pluginDomain) 
						     );
			}
			return $months;
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
		 * Creates the category and sets up the theme resource folder with sample config files.
		 * 
		 * @return void
		 */
		public function on_activate( ) {
			$now = time();
			$firstTime = $now - ($now % 66400);
			wp_schedule_event( $firstTime, 'daily', 'reschedule_event_post'); // schedule this for midnight, daily
			$this->create_category_if_not_exists( );	
			$this->flushRewriteRules();
		}
		/**
		* This function is scheduled to run at midnight.  If any posts are set with EventStartDate
		* to today, update the post so that it was posted today.   This will force the event to be
		* displayed in the main loop on the homepage.
		* 
		* @return void
		*/	
		public function reschedule( ) {
			$resetEventPostDate = eventsGetOptionValue('resetEventPostDate', 'off');
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
		 * fields filter for standard wordpress templates.  Adds the start and end date to queries in the
		 * events category
		 *
		 * @param string fields
		 * @param string modified fields for events queries
		 */
		public function events_search_fields( $fields ) {
			if( !$this->in_event_category() ) { 
				return $fields;
			}
			global $wpdb;
			$fields .= ', eventStart.meta_value as EventStartDate, eventEnd.meta_value as EventEndDate ';
			return $fields;

		}
		/**
		 * join filter for standard wordpress templates.  Adds the postmeta tables for start and end queries
		 *
		 * @param string join clause
		 * @return string modified join clause 
		 */
		public function events_search_join( $join ) {
			global $wpdb;
			if( !$this->in_event_category() ) { 
				return $join;
			}
			$join .= "LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id ) ";
			$join .= "LEFT JOIN {$wpdb->postmeta} as eventEnd ON( {$wpdb->posts}.ID = eventEnd.post_id ) ";
			return $join;
		}
		/**
		 * where filter for standard wordpress templates. Inspects the event options and filters
		 * event posts for upcoming or past event loops
		 *
		 * @param string where clause
		 * @return string modified where clause
		 */
		public function events_search_where( $where ) {
			if( !$this->in_event_category() ) { 
				return $where;
			}
			$where .= ' AND ( eventStart.meta_key = "_EventStartDate" AND eventEnd.meta_key = "_EventEndDate" ) ';
			if( events_displaying_month( ) ) {}
			if( events_displaying_upcoming( ) ) {	
				// Is the start date in the future?
				$where .= ' AND ( eventStart.meta_value > "'.$this->date.'" ';
				// Or is the start date in the past but the end date in the future? (meaning the event is currently ongoing)
				$where .= ' OR ( eventStart.meta_value < "'.$this->date.'" AND eventEnd.meta_value > "'.$this->date.'" ) ) ';
			}
			if( events_displaying_past( ) ) {
				// Is the start date in the past?
				$where .= ' AND  eventStart.meta_value < "'.$this->date.'" ';
			}
			return $where;
		}
		/**
		 * Removes event posts from the homepage loop.  This uses a standard wordpress pre_get_posts
		 */
		public function events_home_cat_excluder( $query ) {
			if( is_home() && eventsGetOptionValue( 'displayEventsOnHomepage' ) == 'off' ) {		
		        $excluded_home_cats = $this->event_category_ids();
		        $cni = $query->get('category__not_in');
		        $cni = array_merge( $cni, $excluded_home_cats );
		        $query->set('category__not_in', $cni );
			}
			return $query;
		}
		/**
		 * @return bool true if is_category() is on a child of the events category
		 */
		public function in_event_category( ) {
			if( is_category( The_Events_Calendar::CATEGORYNAME ) ) {
				return true;
			}
			$cat_id = get_query_var( 'cat' );
			if( $cat_id == $this->eventCategory() ) {
				return true;
			}
			$cats = get_categories('child_of=' . $this->eventCategory());
			$is_child = false;
			foreach( $cats as $cat ) {
				if( is_category( $cat->name ) ) {
					$is_child = true;
				}
			}
			return $is_child;
		}
		/**
		 * @return array of event category ids, including children
		 */
		public function event_category_ids( ) {
			$cats = array();
			$cats[] = $this->eventCategory();
			$children = get_categories('hide_empty=0&child_of=' . $cats[0]);
			foreach( $children as $cat ) {
				$cats[] = $cat->cat_ID;
			}
			return $cats;
		}
		/**
		 * orderby filter for standard wordpress templates.  Adds event ordering for queries that are
		 * in the events category and filtered according to the search parameters
		 *
		 * @param string orderby
		 * @return string modified orderby clause
		 */
		public function events_search_orderby( $orderby ) {
			if( !$this->in_event_category() ) { 
				return $orderby;
			}
			global $wpdb;
			$orderby = ' eventStart.meta_value '.$this->order;
			return $orderby;
		}
		/**
		 * limit filter for standard wordpress templates.  Adds limit clauses for pagination 
		 * for queries in the events category
		 *
		 * @param string limits clause
		 * @return string modified limits clause
		 */
		public function events_search_limits( $limits ) { 
			if( !$this->in_event_category() ) { 
				return $limits;
			}
			global $wpdb, $wp_query, $paged;
			if (empty($paged)) {
					$paged = 1;
			}
			$posts_per_page = intval( get_option('posts_per_page') );
			$paged = get_query_var('paged') ? intval( get_query_var('paged') ) : 1;
			$pgstrt = ( ( $paged - 1 ) * $posts_per_page ) . ', ';
			$limits = 'LIMIT ' . $pgstrt . $posts_per_page;
			return $limits;
		}
		/**
	     * Gets the Category id to use for an Event
	     * @return int|false Category id to use or false is none is set
	     */
	    static function eventCategory() {
			return get_cat_id( The_Events_Calendar::CATEGORYNAME );
	    }
		/**
		 * Flush rewrite rules to support custom links
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 */
		public function flushRewriteRules() 
		{
		   global $wp_rewrite;
		   $wp_rewrite->flush_rules();
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
			return $qvars;		  
		}
		/**
		 * Adds Event specific rewrite rules.
		 *
		 *	events/				=>	/?cat=27
		 *  events/month		=>  /?cat=27&eventDisplay=month
		 *	events/upcoming		=>	/?cat=27&eventDisplay=upcoming
		 *	events/past			=>	/?cat=27&eventDisplay=past
		 *	events/2008-01/#15	=>	/?cat=27&eventDisplay=bydate&eventDate=2008-01-01
		 *
		 * @return void
		 */
		public function filterRewriteRules( $wp_rewrite ) {
			if( $useRewriteRules = eventsGetOptionValue('useRewriteRules','on') == 'off' ) {
				return;
			}
			$categoryId = get_cat_id( The_Events_Calendar::CATEGORYNAME );
			$eventCategory = get_category( $categoryId );
			$eventCats = array( $eventCategory );
			$childCats = get_categories("hide_empty=0&child_of=$categoryId");
			$eventCats = array_merge( $eventCats, $childCats );
			$newRules = array();
			foreach( $eventCats as $cat ) {
				$url = get_category_link( $cat->cat_ID );
				$base = str_replace( trailingslashit( get_option( 'siteurl' ) ), '', $url );
				$newRules[$base . 'month'] 					= 'index.php?cat=' . $cat->cat_ID . '&eventDisplay=month';
				$newRules[$base . 'upcoming/page/(\d+)']	= 'index.php?cat=' . $cat->cat_ID . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
				$newRules[$base . 'upcoming']				= 'index.php?cat=' . $cat->cat_ID . '&eventDisplay=upcoming';
				$newRules[$base . 'past/page/(\d+)']		= 'index.php?cat=' . $cat->cat_ID . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(1);
				$newRules[$base . 'past']					= 'index.php?cat=' . $cat->cat_ID . '&eventDisplay=past';
				$newRules[$base . '(\d{4}-\d{2})$']			= 'index.php?cat=' . $cat->cat_ID . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(1);
				$newRules[$base . '?$']						= 'index.php?cat=' . $cat->cat_ID . '&eventDisplay=' . eventsGetOptionValue('viewOption','month');
			}
		  $wp_rewrite->rules = $newRules + $wp_rewrite->rules;
		}
		/**
		 * Creates the events category and updates the  core options (if not already done)
		 * @return int cat_ID
		 */
		public function create_category_if_not_exists( ) {
			if ( !category_exists( The_Events_Calendar::CATEGORYNAME ) ) {
				$category_id = wp_create_category( The_Events_Calendar::CATEGORYNAME );
				return $category_id;
			} else {
				return $this->eventCategory();
			}
		}
		/**
		 * This plugin does not have any deactivation functionality. Any events, categories, options and metadata are
		 * left behind.
		 * 
		 * @return void
		 */
		public function on_deactivate( ) { 
		  	wp_clear_scheduled_hook('reschedule_event_post');
		}
		/**
		 * Converts a set of inputs to YYYY-MM-DD HH:MM:SS format for MySQL
		 */
		public function dateToTimeStamp( $year, $month, $day, $hour, $minute, $meridian ) {
			if ( preg_match( '/(PM|pm)/', $meridian ) && $hour < 12 ) $hour += "12";
			if ( preg_match( '/(AM|am)/', $meridian ) && $hour == 12 ) $hour = "00";
			return "$year-$month-$day $hour:$minute:00";
		}
		public function getTimeFormat( $dateFormat = self::DATEONLYFORMAT ) {
			return $dateFormat . ' ' . get_option( 'time_format', self::TIMEFORMAT );
		}
		/**
		 * Adds / removes the event details as meta tags to the post.
		 *
		 * @param string $postId 
		 * @return void
		 */
		public function addEventMeta( $postId ) {
			if ($_POST['isEvent'] == 'yes') {
				$category_id = $this->create_category_if_not_exists();
				// add a function below to remove all existing categories - wp_set_post_categories(int ,  array )
				if( $_POST['EventAllDay'] == 'yes' ) {
					$_POST['EventStartDate'] = $this->dateToTimeStamp( $_POST['EventStartYear'], $_POST['EventStartMonth'], $_POST['EventStartDay'], "12", "00", "AM" );
					$_POST['EventEndDate'] = $this->dateToTimeStamp( $_POST['EventEndYear'], $_POST['EventEndMonth'], $_POST['EventEndDay'], "11", "59", "PM" );
				} else {
					delete_post_meta( $postId, '_EventAllDay' );
					$_POST['EventStartDate'] = $this->dateToTimeStamp( $_POST['EventStartYear'], $_POST['EventStartMonth'], $_POST['EventStartDay'], $_POST['EventStartHour'], $_POST['EventStartMinute'], $_POST['EventStartMeridian'] );
					$_POST['EventEndDate'] = $this->dateToTimeStamp( $_POST['EventEndYear'], $_POST['EventEndMonth'], $_POST['EventEndDay'], $_POST['EventEndHour'], $_POST['EventEndMinute'], $_POST['EventEndMeridian'] );
				}
				// sanity check that start date < end date
				$startTimestamp = strtotime( $_POST['EventStartDate'] );
				$endTimestamp 	= strtotime( $_POST['EventEndDate'] );
				if ( $startTimestamp > $endTimestamp ) {
					$_POST['EventEndDate'] = $_POST['EventStartDate'];
				}
				// make state and province mutually exclusive
				if( $_POST['EventStateExists'] ) $_POST['EventProvince'] = '';
				else $_POST['EventState'] = '';
				//ignore Select a Country: as a country
				if( $_POST['EventCountryLabel'] == "" ) $_POST['EventCountry'] = "";
				//google map checkboxes
				if( !isset( $_POST['EventShowMapLink'] ) ) update_post_meta( $postId, '_EventShowMapLink', 'false' );
				if( !isset( $_POST['EventShowMap'] ) ) update_post_meta( $postId, '_EventShowMap', 'false' );
				// give add-on plugins a chance to cancel this meta update
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
					if ( $tag != self::EVENTSERROROPT ) {
						if ( isset( $_POST[$htmlElement] ) ) {
							update_post_meta( $postId, $tag, $_POST[$htmlElement] );
						}
					}
				}
				try {
					do_action( 'sp_events_update_meta', $postId );
					if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
				} catch( TEC_Post_Exception $e ) {
					$this->postExceptionThrown = true;
					update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
				}

				update_post_meta( $postId, '_EventCost', the_event_cost( $postId ) ); // XXX eventbrite cost field
				// merge event category into this post
				$cats = wp_get_object_terms($postId, 'category', array('fields' => 'ids'));
				$new_cats = array_merge( array( get_category( $category_id )->cat_ID ), $cats );
				wp_set_post_categories( $postId, $new_cats );
			}
			if ($_POST['isEvent'] == 'no' && is_event( $postId ) ) {
				// remove event meta tags if they exist...this post is no longer an event
				foreach ( $this->metaTags as $tag ) {
					delete_post_meta( $postId, $tag );
				}
				$event_cats[] = $this->eventCategory();
				$cats = get_categories('child_of=' . $this->eventCategory());
				foreach( $cats as $cat ) {
					$event_cats[] = $cat->term_id;
				}
				// remove the event categories from this post but keep any non-event categories
				$terms =  wp_get_object_terms($postId, 'category'); 
				$non_event_cats = array();
				foreach ( $terms as $term ) {
					if( !in_array( $term->term_id, $event_cats ) ) {
						$non_event_cats[] = $term->term_id;
					}
				}
				wp_set_post_categories( $postId, $non_event_cats );
				
				try {
					do_action( 'sp_events_event_clear', $postId );
					if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
				} catch( TEC_Post_Exception $e ) {
					$this->postExceptionThrown = true;
					update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
				}
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
				if ( $postId ) {
					$$tag = get_post_meta( $postId, $tag, true );
				} else {
					$$tag = '';
				}
			}
			$isEventChecked			= ( $_isEvent == 'yes' ) ? 'checked' : '';
			$isNotEventChecked		= ( $_isEvent == 'no' || $_isEvent == '' ) ? 'checked' : '';
			$isEventAllDay = ( $_EventAllDay == 'yes' || $_isEvent == '' || $_isEvent == 'no' ) ? 'checked' : ''; // default is all day for new posts
			
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
			include( dirname( __FILE__ ) . '/views/events-meta-box.php' );
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
			add_meta_box( 'Event Details', __( 'The Events Calendar', 'Events_textdomain' ), 
		                array( $this, 'EventsChooserBox' ), 'post', 'normal', 'high' );
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
			$display = ( isset( $wp_query->query_vars['eventDisplay'] ) ) ? $wp_query->query_vars['eventDisplay'] : eventsGetOptionValue('viewOption','month');
			switch ( $display ) {
				case "past":
					$this->displaying		= "past";
					$this->startOperator	= "<=";
					$this->order			= "DESC";
					$this->date				= date_i18n( The_Events_Calendar::DBDATETIMEFORMAT );
					break;
				case "upcoming":
					$this->displaying		= "upcoming";					
					$this->startOperator	= ">=";
					$this->order			= "ASC";
					$this->date				= date_i18n( The_Events_Calendar::DBDATETIMEFORMAT );
					break;					
				case "month":
				case "default":
					$this->displaying		= "month";
					$this->startOperator	= ">=";
					$this->order			= "ASC";
					// TODO date set to YYYY-MM
					// TODO store DD as an anchor to the URL
					if ( isset ( $wp_query->query_vars['eventDate'] ) ) {
						$this->date = $wp_query->query_vars['eventDate'] . "-01";
					} else {
						$date = date_i18n( The_Events_Calendar::DBDATEFORMAT );
						$this->date = substr_replace( $date, '01', -2 );
					}
			}
		}
		public function getDateString( $date ) {
			$dateParts = split( '-', $date );
		    $timestamp = mktime( 0, 0, 0, $dateParts[1], 1, $dateParts[0] );
		    return date( "F Y", $timestamp );
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
		 * build an ical feed from events posts
		 */
		public function iCalFeed( $postId = null ) {
		    $getstring = $_GET['ical'];
			$wpTimezoneString = get_option("timezone_string");
			$categoryId = get_cat_id( The_Events_Calendar::CATEGORYNAME );
			$events = "";
			$lastBuildDate = "";
			$eventsTestArray = array();
			$blogHome = get_bloginfo('home');
			$blogName = get_bloginfo('name');
			$includePosts = ( $postId ) ? '&include=' . $postId : '';
			$eventPosts = get_posts( 'numberposts=-1&category=' . $categoryId . $includePosts );
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
				$events .= "DTSTART:" . $startDate . "\n";
				$events .= "DTEND:" . $endDate . "\n";
				$events .= "DTSTAMP:" . date("Ymd\THis", time()) . "\n";
				$events .= "CREATED:" . str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_date ) . "\n";
				$events .= "LAST-MODIFIED:". str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_modified ) . "\n";
		        $events .= "UID:" . $eventPost->ID . "@" . $blogHome . "\n";
		        $events .= "SUMMARY:" . $eventPost->post_title . "\n";				
		        $events .= "DESCRIPTION:" . $description . "\n";
				$events .= "LOCATION:" . tec_get_event_address( $eventPost->ID ) . "\n";
				$events .= "URL:" . get_permalink( $eventPost->ID ) . "\n";
		        $events .= "END:VEVENT\n";
			}
	        header('Content-type: text/calendar');
	        header('Content-Disposition: attachment; filename="iCal-The_Events_Calendar.ics"');
			$content = "BEGIN:VCALENDAR\n";
			$content .= "PRODID:-//" . $blogName . "//NONSGML v1.0//EN\n";
			$content .= "VERSION:2.0\n";
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
	} // end The_Events_Calendar class
	global $spEvents;
	$spEvents = new The_Events_Calendar();
} // end if !class_exists The_Events_Calendar