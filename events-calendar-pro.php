<?php
/*
Plugin Name: Events Calendar PRO
Description: The Events Calendar PRO, a premium add-on to the open source The Events Calendar plugin (required), enables recurring events, custom attributes, venue pages, new widgets and a host of other premium features.
Version: 2.1
Author: Modern Tribe, Inc.
Author URI: http://tri.be/?ref=ecp-plugin
Text Domain: tribe-events-calendar-pro
License: GPLv2 or later
*/

/*
Copyright 2010-2012 by Modern Tribe Inc and the contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
		const REQUIRED_TEC_VERSION = '2.1';
		const VERSION = '2.1';
		
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
			TribeCommonLibraries::register( 'pue-client', '1.2', $this->pluginPath . 'vendor/pue-client/pue-client.php' );
			TribeCommonLibraries::register( 'advanced-post-manager', '1.0.5', $this->pluginPath . 'vendor/advanced-post-manager/tribe-apm.php' );
			TribeCommonLibraries::register( 'related-posts', '1.1', $this->pluginPath. 'vendor/tribe-related-posts/tribe-related-posts.php' );
			//TribeCommonLibraries::register( 'tribe-support', '0.1', $this->pluginPath . 'vendor/tribe-support/tribe-support.class.php' );

			// Next Event Widget
			require_once( 'lib/widget-featured.class.php');

			add_action( 'init', array( $this, 'init' ), 10 );			
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts') );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles') );
			add_action( 'tribe_after_location_details', array( $this, 'add_google_map_preview') );
			add_action( 'tribe_tec_template_chooser', array( $this, 'do_ical_template' ) );
			add_filter( 'tribe_settings_do_tabs', array( $this, 'add_defaults_settings_tab' ) );
			add_filter( 'tribe_current_events_page_template', array( $this, 'select_venue_template' ) );
			add_filter( 'tribe_help_tab_getting_started_text', array( $this, 'add_help_tab_getting_started_text' ) );
			add_filter( 'tribe_help_tab_enb_content', array( $this, 'add_help_tab_enb_text' ) );
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
			add_filter( 'tribe_promo_banner', array($this, 'tribePromoBannerPro') );
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
         $VenueID = apply_filters('tribe_display_event_venue_dropdown_id',$VenueID);
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
         $curOrg = apply_filters('tribe_display_event_organizer_dropdown_id',$curOrg);

         ?>
			<tr class="" >
				<td style="width:170px"><?php _e('Use Saved Organizer:', 'tribe-events-calendar-pro'); ?></td>
				<td>
					<?php $this->saved_organizers_dropdown($curOrg);?>
				</td>
			</tr>
         <?php
      }

    /**
     * Add the default settings tab
     *
     * @since 2.0.5
     * @author jkudish
     * @return void
     */
  	public function add_defaults_settings_tab() {
  		require_once( $this->pluginPath . 'admin-views/tribe-options-defaults.php' );
			new TribeSettingsTab( 'defaults', __('Defaults', 'tribe-events-calendar-pro'), $defaultsTab );
  	}

		
		public function add_help_tab_getting_started_text() {
			$ga_query_string = '?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin';

			$getting_started_text[] = sprintf( __('%sWelcome to Events Calendar, a full-featured events management system for WordPress. By buying a license you\'ve given us a vote of confidence, will get active support and have hooked up some sweet additional features not found in the free The Events Calendar.%s', 'tribe-events-calendar-pro'), '<p class="admin-indent">', '</p>' );
			$getting_started_text[] = sprintf( __('%sIf you aren\'t familiar with The Events Calendar, it may be wise to check out our %s. It\'ll introduce you to the basics of what the plugin has to offer and will have you creating events in no time. From there, the resources below -- extensive template tag documentation, FAQs, video walkthroughs and more -- will give you a leg up as you dig deeper.%s', 'tribe-events-calendar-pro'), '<p class="admin-indent">', sprintf( '<a href="http://tri.be/support/documentation/events-calendar-pro-new-user-primer/' . $ga_query_string . '">%s</a>', __('new user primer', 'tribe-events-calendar-pro') ), '</p>' );
			$getting_started_text[] = sprintf( __('%sOh, wondering what to do with your license key and whether you need it before you can get into event creation? Check out %s on that subject for an answer. %s, if you don\'t have it handy.%s', 'tribe-events-calendar-pro'), '<p class="admin-indent">', sprintf( '<a href="http://tri.be/events-calendar-pro-license-keys-when-you-need-them-when-you-dont/' . $ga_query_string . '">%s</a>', __('our blog post', 'tribe-events-calendar-pro') ), sprintf( '<a href="http://tri.be/finding-your-pro-license-key-re-downloading-the-plugin/' . $ga_query_string . '">%s</a>', __('Here\'s how you find your license key') ), '</p>' );
			$content = implode( $getting_started_text );
			
			return $content;
		}
		
		public function add_help_tab_enb_text() {
			$ga_query_string = '?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin';

			$enb_text[] = sprintf( __('%sOne of the advantages of being a PRO user is that you have access to our PRO-exclusive forums at %s. Our support staff hits the forums on a daily basis, and what they can\'t answer on the spot they\'ll bring a member of our dev team in to address directly.%s', 'tribe-events-calendar-pro'), '<p class="admin-indent">', sprintf( '<a href="http://tri.be/support/forums/' . $ga_query_string . '">%s</a>', __('tri.be', 'tribe-events-calendar-pro') ), '</p>' );
			$enb_text[] = sprintf( __('%sSome things to consider before posting on the forum:%s', 'tribe-events-calendar'), '<p class="admin-indent">', '</p><ul class="admin-list">' );
			$enb_text[] = sprintf( __('%sLook through existing threads before posting a new one and check that there isn\'t already a discussion going on your issue. The tri.be site has a solid search function that should help find what you\'re looking for, if it indeed already is present.%s', 'tribe-events-calendar'), '<li>', '</li>' );
			$enb_text[] = sprintf( __('%sA good way to help us out before posting is to check whether the issue is a conflict with another plugin or your theme. This can be tested relatively easily on a staging site by deactivating other plugins one-by-one, and reverting to the default 2011 theme as needed, to see if conflicts can be easily identified. If so, please note that when posting your thread.%s', 'tribe-events-calendar'), '<li>', '</li>' );
			$enb_text[] = sprintf( __('%sSometimes, just resaving your permalinks (under Settings -> Permalinks) can resolve events-related problems on your site. It is worth a shot before creating a new thread.%s', 'tribe-events-calendar'), '<li>', '</li></ul>' );
			$enb_text[] = sprintf( __('%sWhile we won\'t build your site for you and can\'t guarantee The Events Calendar/PRO to play nicely with every theme and plugin out there, our team will do our best to help you get it functioning nicely with your site. And as an added bonus, once you\'re done you can post it in the %s so the rest of the community can see what you\'ve been working on.%s', 'tribe-events-calendar'), '<p class="admin-indent">', sprintf( '<a href="http://tri.be/support/forums/topic/showcase-2-0/' . $ga_query_string . '">%s</a>', __('Showcase thread', 'tribe-events-calendar-pro') ), '</p>' );
			$content = implode ( $enb_text );
			
			return $content;
		}


      public function select_venue_template($template) {
	      if ( is_singular( TribeEvents::VENUE_POST_TYPE ) ) {
	         return TribeEventsTemplates::getTemplateHierarchy('single-venue');
	      }

         return $template;
      }

      public function load_venue_template($file) {
        return (file_exists($file)) ? $file : $this->pluginPath . 'views/single-venue.php';
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
			$getstring = (isset($_GET['ical']) ? $_GET['ical'] : null);
			$wpTimezoneString = get_option("timezone_string");
			$postType = TribeEvents::POSTTYPE;
			$events = "";
			$lastBuildDate = "";
			$eventsTestArray = array();
			$blogHome = get_bloginfo('url');
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
				$item = array();
				$item[] = "DTSTART;VALUE=$type:" . $startDate;
				$item[] = "DTEND;VALUE=$type:" . $endDate;
				$item[] = "DTSTAMP:" . date("Ymd\THis", time());
				$item[] = "CREATED:" . str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_date );
				$item[] = "LAST-MODIFIED:". str_replace( array("-", " ", ":") , array("", "T", "") , $eventPost->post_modified );
				$item[] = "UID:" . $eventPost->ID.'-'.strtotime($startDate).'-'.strtotime($endDate)."@".$blogHome;
				$item[] = "SUMMARY:" . $eventPost->post_title;
				$item[] = "DESCRIPTION:" . str_replace(",",'\,',$description);
				$item[] = "LOCATION:" . html_entity_decode($tribeEvents->fullAddressString( $eventPost->ID ), ENT_QUOTES);
				$item[] = "URL:" . get_permalink( $eventPost->ID );

				$item = apply_filters('tribe_ical_feed_item', $item, $eventPost );
				
				$events .= "BEGIN:VEVENT\n" . implode("\n",$item) . "\nEND:VEVENT\n";

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
		 * Adds the "PRO" to the promo banner and changes the link to link to the pro website.
		 *
		 * @author Paul Hughes
		 * @since 2.0.5 
		 *
		 * @return string The new banner.
		 */
		public function tribePromoBannerPro() {
			return sprintf( __('Calendar powered by %sThe Events Calendar PRO%s', 'tribe-events-calendar'), '<a href="http://tri.be/wordpress-events-calendar-pro/">', '</a>' );
		}

      /**
       * Creates the venue dropdown
       */
		function saved_venues_dropdown($current = null, $name="venue[VenueID]"){
			$venues = TribeEvents::instance()->get_venue_info();
	
			if($venues){
				echo '<select class="chosen venue-dropdown" name="'.$name.'" id="saved_venue">';
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
				echo '<select class="chosen organizer-dropdown" name="'.$name.'" id="saved_organizer">';
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
		add_filter( 'tribe_tec_addons', 'tribe_init_ecp_addon' );
		add_filter( 'tribe_tec_addons_comparison_operator', 'tribe_version_compare_operator' );
		$to_run_or_not_to_run = (class_exists( 'TribeEvents' ) && defined('TribeEvents::VERSION') && version_compare( TribeEvents::VERSION, TribeEventsPro::REQUIRED_TEC_VERSION, '>='));
		if ( apply_filters('tribe_ecp_to_run_or_not_to_run', $to_run_or_not_to_run) ) {
			TribeEventsPro::instance();
		}
	}

	add_action( 'plugins_loaded', 'Tribe_ECP_Load', 1); // high priority so that it's not too late for tribe_register-helpers class

	/**
	 * Add Events PRO to the list of add-ons to check required version.
	 *
	 * @author Paul Hughes, jkudish
	 * @since 2.0.5
	 * @return array $plugins the required info
	 */
	function tribe_init_ecp_addon( $plugins ) {
		$plugins['TribeEventsPro'] = array('plugin_name' => 'Events Calendar Pro', 'required_version' => TribeEventsPro::REQUIRED_TEC_VERSION);
		return $plugins;
	}
	
	/**
	 * What operator should be used to compare PRO's required version with TEC's version.
	 * Note that a result of TRUE with the version_compare results in the error message.
	 * As is the case here, if they are NOT equal (!=), an error should result.
	 *
	 * @author Paul Hughes
	 * @since 2.0.5
	 * @return string $operator the operator to use.
	 */
	function tribe_version_compare_operator () {
		$operator = '!=';
		return $operator;
	}

	register_deactivation_hook(__FILE__, 'tribe_ecp_deactivate');
	register_uninstall_hook(__FILE__, 'tribe_ecp_uninstall'); 

	function tribe_ecp_deactivate() {
		// when we deactivate pro, we should reset this to true
		if (function_exists('tribe_update_option')) {
			tribe_update_option('defaultValueReplace', true);
			tribe_update_option('defaultCountry', null);
		}
	}

	function tribe_ecp_uninstall() {
		delete_option('pue_install_key_events_calendar_pro');
	}
}