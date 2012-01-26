<?php
/*
 Plugin Name: Events Calendar PRO
 Description: The Events Calendar PRO, a premium add-on to the open source The Events Calendar plugin (required), enables recurring events, custom attributes, venue pages, new widgets and a host of other premium features.
 Version: 2.1
 Author: Modern Tribe, Inc.
 Author URI: http://tri.be/?ref=ecp-plugin
 Text Domain: tribe-events-calendar-pro
 */

if ( !class_exists( 'TribeEventsPro' ) ) {
	class TribeEventsPro {

		private static $instance;

		//instance variables
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginSlug;
		public $licenseKey;
		public static $updateUrl = 'http://tri.be/';
		const REQUIRED_TEC_VERSION = '2.0.2';
		
	    private function __construct() {
			$this->pluginDir = trailingslashit( basename( dirname(__FILE__) ) );
			$this->pluginPath = trailingslashit( dirname(__FILE__) );
			$this->pluginUrl = WP_PLUGIN_URL.'/'.$this->pluginDir;
			$this->pluginSlug = 'events-calendar-pro';

			require_once( 'lib/tribe-date-series-rules.class.php' );
			require_once( 'lib/tribe-ecp-custom-meta.class.php' );
			require_once( 'lib/tribe-events-recurrence-meta.class.php' );
			require_once( 'lib/tribe-recurrence.class.php' );
			require_once( 'lib/widget-calendar.class.php' );
			require_once( 'lib/tribe-related-events.class.php' );
			require_once( 'lib/widget-related-events.class.php' );
			require_once( 'lib/widget-venue.class.php' );
			require_once( 'lib/widget-countdown.class.php' );
			require_once( 'template-tags.php' );

			// Tribe common resources
			require_once( 'vendor/tribe-common-libraries/tribe-common-libraries.class.php' );
			TribeCommonLibraries::register( 'pue-client', '1.1', $this->pluginPath . 'vendor/pue-client/pue-client.php' );
			TribeCommonLibraries::register( 'advanced-post-manager', '1.0.5', $this->pluginPath . 'vendor/advanced-post-manager/tribe-apm.php' );
			//TribeCommonLibraries::register( 'tribe-support', '0.1', $this->pluginPath . 'vendor/tribe-support/tribe-support.class.php' );

			// Next Event Widget
			require_once( 'lib/widget-featured.class.php');

			add_action( 'init', array( $this, 'init' ), 10 );			
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles') );
			add_action( 'tribe_after_location_details', array( $this, 'add_google_map_preview') );
			add_action( 'tribe_tec_template_chooser', array( $this, 'do_ical_template' ) );
			add_action( 'tribe-events-settings-tab', array( $this, 'add_defaults_settings_tab') );
			add_action( 'tribe-events-defaults-settings-content', array( $this, 'add_defaults_settings_content') );
			add_action( 'tribe-events-before-general-settings', array( $this, 'event_license_key') );
			add_filter( 'tribe_current_events_page_template', array( $this, 'select_venue_template' ) );
			add_filter( 'tribe_events_template_single-venue.php', array( $this, 'load_venue_template' ) );
			add_action( 'widgets_init', array( $this, 'pro_widgets_init' ), 100 );
			add_action( 'wp_loaded', array( $this, 'allow_cpt_search' ) );
			add_action( 'plugin_row_meta', array( $this, 'addMetaLinks' ), 10, 2 );
			add_filter( 'get_delete_post_link', array($this, 'adjust_date_on_recurring_event_trash_link'), 10, 2 );	
			add_action( 'admin_footer', array( $this, 'addDeleteDialogForRecurringEvents' ) );	
			// Load organizer and venue editors
			add_action( 'admin_menu', array( $this, 'addVenueAndOrganizerEditor' ) );
			add_action( 'tribe_venue_table_top', array($this, 'displayEventVenueDropdown') );
			add_action( 'tribe_organizer_table_top', array($this, 'displayEventOrganizerDropdown') );
			add_action( 'tribe_helper_activation_complete', array($this, 'helpersLoaded') );
	    }
		
		public function init() {
			TribeEventsCustomMeta::init();
			TribeEventsRecurrenceMeta::init();			
		}
		
		public function helpersLoaded() {
			require_once( 'lib/apm_filters.php');
			new PluginUpdateEngineChecker(self::$updateUrl, $this->pluginSlug, array(), plugin_basename(__FILE__));
		}

		public function do_ical_template($template) {
			// hijack to iCal template
			if ( get_query_var('ical') || isset($_GET['ical']) ) {
				global $wp_query;
				if ( is_single() ) {
					$post_id = $wp_query->post->ID;
					$this->iCalFeed($wp_query->post, null, get_query_var('eventDate') );
				}
				else if ( is_tax( TribeEvents::TAXONOMY) ) {
					$this->iCalFeed( null, get_query_var( TribeEvents::TAXONOMY ) );
				}
				else {
					$this->iCalFeed();
				}
				die;
			}

      }

      // event deletion
      public function adjust_date_on_recurring_event_trash_link( $link, $postId ) {
      	global $post;
				if ( isset($_REQUEST['deleteAll']) ) {
					$link = remove_query_arg( array( 'eventDate', 'deleteAll'), $link );
				}
				elseif ( (isset($post->ID)) && tribe_is_recurring_event($post->ID) && isset($_REQUEST['eventDate']) ) {
					$link = add_query_arg( 'eventDate', $_REQUEST['eventDate'], $link );
				}
				return $link;
      }

      public function addDeleteDialogForRecurringEvents() {
      	global $current_screen, $post;
      	if ( is_admin() && isset($current_screen->post_type) && $current_screen->post_type == TribeEvents::POSTTYPE
	      	&& (
		      ( isset($current_screen->id) && $current_screen->id == 'edit-'.TribeEvents::POSTTYPE ) // listing page
      		|| ( (isset($post->ID)) && tribe_is_recurring_event($post->ID) ) // single event page
	      ) )
	      	// load the dialog
      		require_once(TribeEvents::instance()->pluginPath.'admin-views/recurrence-dialog.php');
      }

      public function addVenueAndOrganizerEditor() {
         add_submenu_page( '/edit.php?post_type='.TribeEvents::POSTTYPE, __('Venues','tribe-events-calendar-pro'), __('Venues','tribe-events-calendar-pro'), 'edit_posts', 'edit.php?post_type='.TribeEvents::VENUE_POST_TYPE);
         add_submenu_page( '/edit.php?post_type='.TribeEvents::POSTTYPE, __('Organizers','tribe-events-calendar-pro'), __('Organizers','tribe-events-calendar-pro'), 'edit_posts', 'edit.php?post_type='.TribeEvents::ORGANIZER_POST_TYPE);
      }

      public function displayEventVenueDropdown($postId) {
         $VenueID = get_post_meta( $postId, '_EventVenueID', true);
         $defaultsEnabled = tribe_get_option('defaultValueReplace');
         if (!$VenueID && $defaultsEnabled) {
         	$VenueID = tribe_get_option('eventsDefaultVenueID');
         }
         ?>
			<tr class="">
				<td style="width:170px"><?php _e('Use Saved Venue:','tribe-events-calendar-pro'); ?></td>
				<td>
					<?php $this->saved_venues_dropdown($VenueID);?>
				</td>
			</tr>
         <?php
      }

      public function displayEventOrganizerDropdown($postId) {
	     $curOrg = get_post_meta( $postId, '_EventOrganizerID', true);
         $defaultsEnabled = tribe_get_option('defaultValueReplace');
		 if (!$curOrg && $defaultsEnabled) {
         	$curOrg = tribe_get_option('eventsDefaultOrganizerID');
         }
         ?>
			<tr class="" >
				<td style="width:170px"><?php _e('Use Saved Organizer:','tribe-events-calendar-pro'); ?></td>
				<td>
					<?php $this->saved_organizers_dropdown($curOrg);?>
				</td>
			</tr>
         <?php
      }
      
      public function add_defaults_settings_content() {
      			$tec = TribeEvents::instance();
         		$tecp = $this;
      			include( $this->pluginPath . 'admin-views/event-defaults.php' );
      		}
      
      public function add_defaults_settings_tab() {
				$tab = 'defaults';
				$name = 'Defaults';
				if (isset ( $_GET['tab'] ) ) {
					$class = ($_GET['tab'] == $tab) ? ' nav-tab-active' : '';
				} else {
					$class = '';
				}
				echo '<a class="nav-tab' . $class .'" href="?page=tribe-events-calendar&tab=' . $tab .'">' . $name . '</a>';
			}

      public function event_license_key() {
				do_action('pue-settings_events-calendar-pro');
      }

      public function select_venue_template($template) {
	      if ( is_singular( TribeEvents::VENUE_POST_TYPE ) ) {
	         return TribeEventsTemplates::getTemplateHierarchy('single-venue');
	      }

         return $template;
      }

      public function load_venue_template($file) {
         if ( !file_exists($file) ) {
            $file = $this->pluginPath . 'views/single-venue.php';
         }

         return $file;
      }

      public function add_google_map_preview($postId) {
         if( tribe_get_option('embedGoogleMaps') ) {
            // && tribe_embed_google_map($postId )
            $display = tribe_embed_google_map( $postId ) ? "block" : "none";
            ?><div style="float:right; display:<?php echo $display ?>;"><?php
               echo tribe_get_embedded_map($postId, 200, 200, true);
            ?></div><?php
         }
         ?><div style="clear:both"></div><?php
      }

      public function admin_enqueue_scripts() {
            wp_enqueue_script( TribeEvents::POSTTYPE.'-premium-admin', $this->pluginUrl . 'resources/events-admin.js', array('jquery-ui-datepicker'), '', true );
      }
      
      public function enqueue_styles() {
         // Enqueue the pro-stylesheet.
         $stylesheet_url = $this->pluginUrl . 'resources/events.css';
         if ( $stylesheet_url ) {
	         wp_enqueue_style( 'tribe_events_pro_stylesheet', $stylesheet_url );
		 }
      }

		/**
		 * Build an ical feed for an event post
		 */
		public function iCalFeed( $post = null, $eventCatSlug = null, $eventDate = null ) {
			$tribeEvents = TribeEvents::instance();
			$postId = $post ? $post->ID : null;
			$getstring = $_GET['ical'];
			$wpTimezoneString = get_option("timezone_string");
			$postType = TribeEvents::POSTTYPE;
			$events = "";
			$lastBuildDate = "";
			$eventsTestArray = array();
			$blogHome = get_bloginfo('home');
			$blogName = get_bloginfo('name');
			$includePosts = ( $postId ) ? '&include=' . $postId : '';
			$eventsCats = ( $eventCatSlug ) ? '&'.TribeEvents::TAXONOMY.'='.$eventCatSlug : '';

			if ($post) {
				$eventPosts = array();
				$eventPosts[] = $post;
			} else {
				$eventPosts = get_posts( 'numberposts=-1&post_type=' . $postType . $includePosts . $eventsCats );
			}

			foreach( $eventPosts as $eventPost ) {
				if ( $eventDate) {
					$duration = TribeDateUtils::timeBetween($eventPost->EventStartDate, $eventPost->EventEndDate);
					$startDate = TribeDateUtils::addTimeToDate($eventDate, TribeDateUtils::timeOnly($eventPost->EventStartDate));
					$endDate = TribeDateUtils::dateAndTime(strtotime($startDate) + $duration, true);
				} else {
					$startDate = $eventPost->EventStartDate;
					$endDate = $eventPost->EventEndDate;
				}

				// convert 2010-04-08 00:00:00 to 20100408T000000 or YYYYMMDDTHHMMSS
				$startDate = str_replace( array("-", " ", ":") , array("", "T", "") , $startDate);
				$endDate = str_replace( array("-", " ", ":") , array("", "T", "") , $endDate);
				if( get_post_meta( $eventPost->ID, "_EventAllDay", true ) == "yes" ) {
					$startDate = substr( $startDate, 0, 8 );
					$endDate = substr( $endDate, 0, 8 );
					// endDate bumped ahead one day to counter iCal's off-by-one error
					$endDateStamp = strtotime($endDate);
					$endDate = date( 'Ymd', $endDateStamp + 86400 );
					$type="DATE";
				}else{
					$type="DATE-TIME";
				}
				$description = preg_replace("/[\n\t\r]/", " ", strip_tags( $eventPost->post_content ) );
				//$cost = get_post_meta( $eventPost->ID, "_EventCost", true);
				//if( $cost ) $description .= " Cost: " . $cost;
				// add fields to iCal output
				$events .= "BEGIN:VEVENT\n";
				$events .= "DTSTART;VALUE=$type:" . $startDate . "\n";
				$events .= "DTEND;VALUE=$type:" . $endDate . "\n";
				$events .= "DTSTAMP:" . date("Ymd\THis", time()) . "\n";
				$events .= "CREATED:" . str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_date ) . "\n";
				$events .= "LAST-MODIFIED:". str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_modified ) . "\n";
				$events .= "UID:" . $eventPost->ID . "@" . $blogHome . "\n";
				$events .= "SUMMARY:" . $eventPost->post_title . "\n";				
				$events .= "DESCRIPTION:" . str_replace(",",'\,',$description) . "\n";
				$events .= "LOCATION:" . html_entity_decode($tribeEvents->fullAddressString( $eventPost->ID ), ENT_QUOTES) . "\n";
				$events .= "URL:" . get_permalink( $eventPost->ID ) . "\n";
				$events .= "END:VEVENT\n";
			}
			header('Content-type: text/calendar');
			header('Content-Disposition: attachment; filename="iCal-TribeEvents.ics"');
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

      public function googleCalendarLink( $postId = null ) {
         $tribeEvents = TribeEvents::instance();

			if ( $postId === null || !is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			$start_date = strtotime(get_post_meta( $postId, '_EventStartDate', true ));
			$end_date = strtotime(get_post_meta( $postId, '_EventEndDate', true ) . ( get_post_meta( $postId, '_EventAllDay', true ) ? " + 1 day" : ""));
			$dates = ( get_post_meta( $postId, '_EventAllDay', true ) ) ? date('Ymd', $start_date) . '/' . date('Ymd', $end_date) : date('Ymd', $start_date) . 'T' . date('Hi00', $start_date) . '/' . date('Ymd', $end_date) . 'T' . date('Hi00', $end_date);
			$location = trim( $tribeEvents->fullAddressString( $postId ) );
			$base_url = 'http://www.google.com/calendar/event';
			$params = array(
				'action' => 'TEMPLATE',
				'text' => str_replace( ' ', '+', strip_tags( urlencode( get_the_title() ) ) ),
				'dates' => $dates,
				'details' => str_replace( ' ' , '+', strip_tags( apply_filters( 'the_content', urlencode( get_the_content() ) ) ) ),
				'location' => str_replace( ' ', '+', urlencode( $location ) ),
				'sprop' => get_option('blogname'),
				'trp' => 'false',
				'sprop' => 'website:' . home_url()
			);
			$url = add_query_arg( $params, $base_url );
			return esc_url($url);
		}

		/**
		 * Includes and handles registration/de-registration of the advanced list widget. Idea from John Gadbois.
		 *
		 * @return void
		 * @author Elliot Wiltshire
		 */

		public function pro_widgets_init() {
			require_once( 'lib/widget-advanced-list.class.php' );
			unregister_widget( 'TribeEventsListWidget' );
			register_widget( 'TribeEventsAdvancedListWidget' );
			add_filter( 'tribe_apm_textdomain', array(__CLASS__, 'apm_textdomain') );
			// load text domain after class registration
			load_plugin_textdomain( 'tribe-events-calendar-pro', false, basename(dirname(dirname(__FILE__))) . '/lang/');
		}
		
		public function apm_textdomain($domain) {
			return 'tribe-events-calendar-pro';
		}

		/**
		 * Re-registers the custom post types for venues so they allow search from the frontend.
		 *
		 * @return void
		 * @author Elliot Wiltshire
		 */

		public function allow_cpt_search() {
			$tec = TribeEvents::instance();
			$venue_args = $tec->getVenuePostTypeArgs();
			$venue_args['exclude_from_search'] = false;
			register_post_type( TribeEvents::VENUE_POST_TYPE, $venue_args );
		}

      /**
       * Creates the venue dropdown
       */
		function saved_venues_dropdown($current = null, $name="venue[VenueID]"){
			$venues = TribeEvents::instance()->get_venue_info();
	
			if($venues){
				echo '<select name="'.$name.'" id="saved_venue">';
					echo '<option value="0">' . __("Use New Venue", 'tribe-events-calendar-pro') . '</option>';
				foreach($venues as $venue){
					$selected = ($current == $venue->ID) ? 'selected="selected"' : '';
               $venue_title = strlen($venue->post_title) > 70 ? substr($venue->post_title, 0, 67) . '...' : $venue->post_title;
					echo "<option data-address='" . esc_attr(TribeEvents::instance()->fullAddressString($venue->ID)) . "' value='{$venue->ID}' $selected>{$venue_title}</option>";
				}
				echo '</select>';
			}else{
				echo '<p class="nosaved">'.__('No saved venues yet.','tribe-events-calendar-pro').'</p>';
			}
		}

      /**
       * Creates the organizer dropdown
       */
		function saved_organizers_dropdown($current = null, $name="organizer[OrganizerID]"){
			$organizers = TribeEvents::instance()->get_organizer_info();
			if($organizers){
				echo '<select name="'.$name.'" id="saved_organizer">';
					echo '<option value="0">' . __('Use New Organizer', 'tribe-events-calendar-pro') . '</option>';
				foreach($organizers as $organizer){
					$selected = ($current == $organizer->ID) ? 'selected="selected"' : '';
					echo "<option value='{$organizer->ID}' $selected>{$organizer->post_title}</option>";
				}
				echo '</select>';
			}else{
				echo '<p class="nosaved_organizer">'.__('No saved organizers yet.','tribe-events-calendar-pro').'</p>';
			}
		}
      /**
       * Add meta links on the plugin page
       */
		public function addMetaLinks( $links, $file ) {
         if ( $file == $this->pluginDir . 'events-calendar-pro.php' ) {
            $anchor = __( 'Support', 'tribe-events-calendar' );
            $links []= '<a href="'.self::$updateUrl.'support/?ref=ecp-plugin">' . $anchor . '</a>';

				$anchor = __( 'View All Add-Ons', 'tribe-events-calendar' ); 
				$links []= '<a href="'.self::$updateUrl.'shop/?ref=ecp-plugin">' . $anchor . '</a>';
			}
			return $links;
		}

		/* Static Methods */
	    public static function instance()
	    {
	        if (!isset(self::$instance)) {
	            $className = __CLASS__;
	            self::$instance = new $className;
	        }

	        return self::$instance;
	    }
	}
	
	// Instantiate class and set up WordPress actions.
	function Tribe_ECP_Load() {
		if( class_exists( 'TribeEvents' ) && defined('TribeEvents::VERSION') && version_compare( TribeEvents::VERSION, TribeEventsPro::REQUIRED_TEC_VERSION, '>=') ) {
			TribeEventsPro::instance();
		} else {
			add_action( 'admin_notices', 'tribe_show_fail_message' );
		}
	}

	add_action( 'plugins_loaded', 'Tribe_ECP_Load', 1); // high priority so that it's not too late for tribe_register-helpers class

	/**
	 * Shows message if the plugin can't load due to TEC not being installed.
	 */

	function tribe_show_fail_message() {
		if ( current_user_can('activate_plugins') ) {
			$url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
			$title = __('The Events Calendar', 'tribe-events-calendar-pro');
			echo '<div class="error"><p>'.sprintf(__('To begin using Events Calendar PRO, please install the latest version of <a href="%s" class="thickbox" title="%s">The Events Calendar</a>.', 'tribe-events-calendar-pro'),$url,$title).'</p></div>';
		}
	}   

	register_uninstall_hook(__FILE__, 'tribe_ecp_uninstall'); 

	function tribe_ecp_uninstall() {
		delete_option('pue_install_key_events_calendar_pro');
	}
}
?>
