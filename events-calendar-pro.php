<?php
/*
 Plugin Name: Events Calendar Pro
 Description: The Events Calendar Pro Premium plugin enables recurring events, custom meta, and other premium features for The Events Calendar plugin 
 Version: 2.0
 Author: Modern Tribe, Inc.
 Author URI: http://tribe.pro/
 Text Domain: events-calendar-pro
 */

if ( !class_exists( 'TribeEventsPro' ) ) {
	class TribeEventsPro {

		const PLUGIN_DOMAIN = 'events-calendar-pro';

	    private static $instance;

		//instance variables
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public static $updateUrl = 'http://tribe.pro/';
		
	    private function __construct()
	    {
			$this->pluginDir = trailingslashit( basename( dirname(__FILE__) ) );
			$this->pluginPath = trailingslashit( dirname(__FILE__) );
			$this->pluginUrl = WP_PLUGIN_URL.'/'.$this->pluginDir;
			if (defined('TRIBE_UPDATE_URL')) { self::$updateUrl = TRIBE_UPDATE_URL; }
			
			require_once( 'lib/tribe-date-series-rules.class.php' );
			require_once( 'lib/tribe-ecp-custom-meta.class.php' );
			require_once( 'lib/tribe-events-recurrence-meta.class.php' );
			require_once( 'lib/tribe-recurrence.class.php' );
			require_once( 'lib/tribe-support.class.php' );
			require_once( 'lib/widget-calendar.class.php' );
			require_once( 'template-tags.php' );
			require_once( 'lib/plugins/pue-client.php' );
         // Advanced Post Manager
         require_once( 'vendor/advanced-post-manager/tribe-apm.php' );
         require_once( 'lib/apm_filters.php');

         // Next Event Widget
         require_once( 'lib/widget-featured.class.php');
			
			add_action( 'init', array( $this, 'init' ), 10 );			
         add_action( 'init', array( $this, 'enqueue_resources') );
         add_action( 'tribe_after_location_details', array( $this, 'add_google_map_preview') );
         add_action( 'tribe_tec_template_chooser', array( $this, 'do_ical_template' ) );
         add_action( 'tribe-events-after-theme-settings', array( $this, 'event_defaults_options') );
         add_filter( 'tribe_current_events_page_template', array( $this, 'select_venue_template' ) );
         add_filter( 'tribe_events_template_single-venue.php', array( $this, 'load_venue_template' ) );
	 add_action( 'widgets_init', array( $this, 'pro_widgets_init' ), 100 );
	 add_action( 'wp_loaded', array( $this, 'allow_cpt_search' ) );
	    }
		
		public function init() {
			TribeEventsCustomMeta::init();
			TribeEventsRecurrenceMeta::init();
			new PluginUpdateEngineChecker(self::$updateUrl, self::PLUGIN_DOMAIN, array('apikey'=>'ec94dc0f20324d00831a56b3013f428a'));
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

      public function event_defaults_options() {
         $tec = TribeEvents::instance();
			include( $this->pluginPath . 'admin-views/event-defaults.php' );
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
            ?><div style="float:right;"><?php
               echo tribe_get_embedded_map($postId, 200, 200, true);
            ?></div><?php
         }
         ?><div style="clear:both"></div><?php
      }

      public function enqueue_resources() {
         if( is_admin() ) {
            wp_enqueue_script( TribeEvents::POSTTYPE.'-premium-admin', $this->pluginUrl . 'resources/events-admin.js', array('jquery-ui-datepicker'), '', true );
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
				'text' => strip_tags(get_the_title()),
				'dates' => $dates,
				'details' => strip_tags( get_the_excerpt() ),
				'location' => $location,
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
        	        // load text domain after class registration
                	load_plugin_textdomain( 'tribe-events-calendar', false, basename(dirname(dirname(__FILE__))) . '/lang/');
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

		/* Static Methods */
	    public static function instance()
	    {
	        if (!isset(self::$instance)) {
	            $className = __CLASS__;
	            self::$instance = new $className;
	        }

	        return self::$instance;
	    }
		
		/**
		 * check_for_ecp
		 *
		 * Check that the required minimum version of the base events plugin is activated.
		 * 
		 * @author John Gadbois 
		 */
		public static function check_for_ecp() {
			if( !class_exists( 'TribeEvents' ) || !defined('TribeEvents::VERSION') || !version_compare( TribeEvents::VERSION, '2.0', '>=') ) {
				deactivate_plugins(basename(__FILE__)); // Deactivate ourself
				wp_die("Sorry, but you must activate The Events Calendar 2.0 or greater in order for this plugin to be installed.");	
			}
		}
	}
	
	register_activation_hook( __FILE__, array('TribeEventsPro', 'check_for_ecp') );	

	// Instantiate class and set up WordPress actions.
	TribeEventsPro::instance();
}
?>
