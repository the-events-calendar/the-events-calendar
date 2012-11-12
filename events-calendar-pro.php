<?php
/*
Plugin Name: The Events Calendar PRO
Description: The Events Calendar PRO, a premium add-on to the open source The Events Calendar plugin (required), enables recurring events, custom attributes, venue pages, new widgets and a host of other premium features.
Version: 2.0.10
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
		const REQUIRED_TEC_VERSION = '2.0.10';
		const VERSION = '2.0.10';

	    private function __construct() {
			$this->pluginDir = trailingslashit( basename( dirname( __FILE__ ) ) );
			$this->pluginPath = trailingslashit( dirname( __FILE__ ) );
			$this->pluginUrl = plugins_url().'/'.$this->pluginDir;
			$this->pluginSlug = 'events-calendar-pro';

			require_once( 'lib/tribe-date-series-rules.class.php' );
			require_once( 'lib/tribe-ecp-custom-meta.class.php' );
			require_once( 'lib/tribe-events-recurrence-meta.class.php' );
			require_once( 'lib/tribe-recurrence.class.php' );
			require_once( 'lib/widget-calendar.class.php' );
			require_once( 'template-tags.php' );
			require_once( 'lib/tribe-presstrends-events-calendar-pro.php' );

			// Tribe common resources
			require_once( 'vendor/tribe-common-libraries/tribe-common-libraries.class.php' );
			TribeCommonLibraries::register( 'pue-client', '1.2', $this->pluginPath . 'vendor/pue-client/pue-client.php' );
			TribeCommonLibraries::register( 'advanced-post-manager', '1.0.5', $this->pluginPath . 'vendor/advanced-post-manager/tribe-apm.php' );
			//TribeCommonLibraries::register( 'tribe-support', '0.1', $this->pluginPath . 'vendor/tribe-support/tribe-support.class.php' );

			// Next Event Widget
			require_once( 'lib/widget-featured.class.php');

			add_action( 'init', array( $this, 'init' ), 10 );
			add_action( 'init', array( $this, 'enqueue_resources' ) );
			add_action( 'tribe_after_location_details', array( $this, 'add_google_map_preview' ) );
			add_action( 'tribe_tec_template_chooser', array( $this, 'do_ical_template' ) );
			add_filter( 'tribe_settings_do_tabs', array( $this, 'add_settings_tabs' ) );
			add_filter( 'tribe_current_events_page_template', array( $this, 'select_venue_template' ) );
			add_filter( 'tribe_help_tab_getting_started_text', array( $this, 'add_help_tab_getting_started_text' ) );
			add_filter( 'tribe_help_tab_enb_content', array( $this, 'add_help_tab_enb_text' ) );
			add_filter( 'tribe_events_template_single-venue.php', array( $this, 'load_venue_template' ) );
			add_action( 'widgets_init', array( $this, 'pro_widgets_init' ), 100 );
			add_action( 'wp_loaded', array( $this, 'allow_cpt_search' ) );
			add_action( 'plugin_row_meta', array( $this, 'addMetaLinks' ), 10, 2 );
			add_filter( 'get_delete_post_link', array( $this, 'adjust_date_on_recurring_event_trash_link' ), 10, 2 );
			add_action( 'admin_footer', array( $this, 'addDeleteDialogForRecurringEvents' ) );
			// Load organizer and venue editors
			add_action( 'admin_menu', array( $this, 'addVenueAndOrganizerEditor' ) );
			add_action( 'tribe_venue_table_top', array( $this, 'displayEventVenueDropdown' ) );
			add_action( 'tribe_organizer_table_top', array( $this, 'displayEventOrganizerDropdown' ) );
			add_action( 'tribe_helper_activation_complete', array( $this, 'helpersLoaded' ) );
			add_filter( 'tribe_promo_banner', array( $this, 'tribePromoBannerPro' ) );
			add_filter( 'tribe_help_tab_forums_url', array( $this, 'helpTabForumsLink' ) );
			add_action( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'addLinksToPluginActions' ) );
		}

		public function init() {
			TribeEventsCustomMeta::init();
			TribeEventsRecurrenceMeta::init();
			$this->displayMetaboxCustomFields();
		}


		public function helpersLoaded() {
			require_once( 'lib/apm_filters.php' );
			new PluginUpdateEngineChecker( self::$updateUrl, $this->pluginSlug, array(), plugin_basename( __FILE__ ) );
		}

		public function do_ical_template($template) {
			// hijack to iCal template
			if ( get_query_var( 'ical' ) || isset( $_GET['ical'] ) ) {
				global $wp_query;
				if ( is_single() ) {
					$post_id = $wp_query->post->ID;
					$this->iCalFeed( $wp_query->post, null, get_query_var( 'eventDate' ) );
				} else if ( is_tax( TribeEvents::TAXONOMY ) ) {
					$this->iCalFeed( null, get_query_var( TribeEvents::TAXONOMY ) );
				} else {
					$this->iCalFeed();
				}
				die();
			}
	      }

		// event deletion
		public function adjust_date_on_recurring_event_trash_link( $link, $postId ) {
			global $post;
				if ( isset($_REQUEST['deleteAll']) ) {
					$link = remove_query_arg( array( 'eventDate', 'deleteAll'), $link );
				} elseif ( (isset($post->ID)) && tribe_is_recurring_event($post->ID) && isset($_REQUEST['eventDate']) ) {
					$link = add_query_arg( 'eventDate', $_REQUEST['eventDate'], $link );
				}
				return $link;
	      }

		public function addDeleteDialogForRecurringEvents() {
			global $current_screen, $post;
			if ( is_admin() && isset( $current_screen->post_type ) && $current_screen->post_type == TribeEvents::POSTTYPE
				&& (
					( isset( $current_screen->id ) && $current_screen->id == 'edit-'.TribeEvents::POSTTYPE ) // listing page
					|| ( ( isset( $post->ID ) ) && tribe_is_recurring_event( $post->ID ) ) // single event page
				)
			)
			// load the dialog
			require_once( TribeEvents::instance()->pluginPath.'admin-views/recurrence-dialog.php' );
	      }

	    public function displayMetaboxCustomFields(){
	    	// 'disable_metabox_custom_fields'
	    	$show_box = tribe_get_option('disable_metabox_custom_fields');
	    	if($show_box == 'show') {
		    	return true;
		    }
		    if($show_box == 'hide') {
		    	remove_post_type_support( TribeEvents::POSTTYPE, 'custom-fields' );
		    	return false;
		    }
		    if(empty($show_box)){
		    	global $wpdb;
		    	$meta_keys = $wpdb->get_results("select distinct pm.meta_key from $wpdb->postmeta pm
										LEFT JOIN $wpdb->posts p ON p.ID = pm.post_id
										WHERE p.post_type = '" . TribeEvents::POSTTYPE . "'
										AND pm.meta_key NOT LIKE '_wp_%'
										AND pm.meta_key NOT IN (
											'_edit_last',
											'_edit_lock',
											'_thumbnail_id',
											'_EventConference', 
											'_EventAllDay', 
											'_EventHideFromUpcoming', 
											'_EventAuditTrail',
											'_EventOrigin',
											'_EventShowMap',
											'_EventVenueID',
											'_EventShowMapLink',
											'_EventCost',
											'_EventOrganizerID',
											'_EventRecurrence',
											'_EventStartDate',
											'_EventEndDate',
											'_EventDuration',
											'_FacebookID')");
		    	if( empty($meta_keys) ) {
		    		remove_post_type_support( TribeEvents::POSTTYPE, 'custom-fields' );
		    		// update_option('disable_metabox_custom_fields','hide');
		    		$options['disable_metabox_custom_fields'] = 'hide';
		    		$r = false;
		    	} else {
		    		// update_option('disable_metabox_custom_fields','true');
		    		$options['disable_metabox_custom_fields'] = 'show';
		    		$r = true;
		    	}

		    	TribeEvents::setOptions($options);
		    	return $r;
		    }

	    }

		public function addVenueAndOrganizerEditor() {
			add_submenu_page( '/edit.php?post_type='.TribeEvents::POSTTYPE, __( 'Venues','tribe-events-calendar-pro' ), __( 'Venues','tribe-events-calendar-pro' ), 'edit_tribe_venues', 'edit.php?post_type='.TribeEvents::VENUE_POST_TYPE );
			add_submenu_page( '/edit.php?post_type='.TribeEvents::POSTTYPE, __( 'Organizers','tribe-events-calendar-pro' ), __( 'Organizers','tribe-events-calendar-pro' ), 'edit_tribe_organizers', 'edit.php?post_type='.TribeEvents::ORGANIZER_POST_TYPE );
		}

		/**
		 * displays the saved venue dropdown in the event metabox
		 *
		 * @param int $postID the event ID for which to create the dropdown
		 * @return void
		 */
		public function displayEventVenueDropdown( $postId ) {
			$VenueID = get_post_meta( $postId, '_EventVenueID', true );
			// override pro default with community on add page
			if( !$VenueID && class_exists('TribeCommunityEvents') ) {
				if( TribeCommunityEvents::instance()->isEditPage ) {
					$VenueID = TribeCommunityEvents::getOption( 'defaultCommunityVenueID' );
				}
			}
			$defaultsEnabled = tribe_get_option( 'defaultValueReplace' );
			if ( !$VenueID && $defaultsEnabled ) {
				$VenueID = tribe_get_option( 'eventsDefaultVenueID' );
			}
			$VenueID = apply_filters( 'tribe_display_event_venue_dropdown_id', $VenueID );
			?>
			<tr class="">
				<td style="width:170px"><?php _e( 'Use Saved Venue:','tribe-events-calendar-pro' ); ?></td>
				<td><?php $this->saved_venues_dropdown( $VenueID ); ?></td>
			</tr>
			<?php
		}

		/**
		 * displays the saved organizer dropdown in the event metabox
		 *
		 * @param int $postID the event ID for which to create the dropdown
		 * @return void
		 */
		public function displayEventOrganizerDropdown( $postId ) {
			$curOrg = get_post_meta( $postId, '_EventOrganizerID', true );
			// override pro default with community on add page
			if( !$curOrg && class_exists('TribeCommunityEvents') ) {
				if( TribeCommunityEvents::instance()->isEditPage ) {
					$curOrg = TribeCommunityEvents::getOption( 'defaultCommunityOrganizerID' );
				}
			}
			$defaultsEnabled = tribe_get_option( 'defaultValueReplace' );
			if ( !$curOrg && $defaultsEnabled ) {
				$curOrg = tribe_get_option( 'eventsDefaultOrganizerID' );
			}
			$curOrg = apply_filters( 'tribe_display_event_organizer_dropdown_id', $curOrg );
			?>
			<tr class="" >
				<td style="width:170px"><?php _e( 'Use Saved Organizer:', 'tribe-events-calendar-pro' ); ?></td>
				<td><?php $this->saved_organizers_dropdown( $curOrg ); ?></td>
			</tr>
			<?php
		}

		/**
		 * helper function for displaying the saved venue dropdown
		 *
		 * @since 2.0
		 * @param mixed $current the current saved venue
		 * @param string $name the name value for the field
		 */
		public function saved_venues_dropdown( $current = null, $name = 'venue[VenueID]' ){
			$my_venue_ids = array();
			$current_user = wp_get_current_user();
			$my_venues = false;
			$my_venue_options = '';
			if ( 0 != $current_user->ID ) {
			    $my_venues = TribeEvents::instance()->get_venue_info( null, null, array('post_status' => array('publish', 'draft'), 'author' => $current_user->ID) );
			    if( ! empty($my_venues)) {
					foreach($my_venues as $my_venue) {
						$my_venue_ids[] = $my_venue->ID;
						$venue_title = wp_kses( get_the_title( $my_venue->ID ), array() );
						$my_venue_options .= '<option data-address="' . esc_attr( TribeEvents::instance()->fullAddressString( $my_venue->ID ) ) . '" value="' . esc_attr( $my_venue->ID ) .'"';
						$my_venue_options .= selected( $current, $my_venue->ID, false );
						$my_venue_options .=  '>' . $venue_title . '</option>';
					}
				}
			}
			
			$venues = TribeEvents::instance()->get_venue_info( null, null, array('post_status' => 'publish', 'post__not_in' => $my_venue_ids) );
			if ( $venues || $my_venues ) {
				echo '<select class="chosen venue-dropdown" name="' . esc_attr( $name ) . '" id="saved_venue">';
				echo '<option value="0">' . __( 'Use New Venue' ,  'tribe-events-calendar-pro' ) . '</option>';
				if( $my_venues ) {
					echo $venues ? '<optgroup label="' . apply_filters('tribe_events_saved_venues_dropdown_my_optgroup', __('My Venues', 'tribe-events-calendar-pro')) . '">' : '';
					echo $my_venue_options;
					echo $venues ? '</optgroup>' : '';
				}
				if ( $venues ) {
					echo $my_venues ? '<optgroup label="' . apply_filters('tribe_events_saved_venues_dropdown_optgroup', __('Available Venues', 'tribe-events-calendar-pro')) . '">' : '';
					foreach ( $venues as $venue ) {
						$venue_title = wp_kses( get_the_title( $venue->ID ), array() );
						echo '<option data-address="' . esc_attr( TribeEvents::instance()->fullAddressString( $venue->ID ) ) . '" value="' . esc_attr( $venue->ID ) .'"';
						selected( ($current == $venue->ID) );
						echo '>' . $venue_title . '</option>';
					}
					echo $my_venues ? '</optgroup>'	: '';
				}
				echo '</select>';
			} else {
				echo '<p class="nosaved">' . __( 'No saved venues yet.', 'tribe-events-calendar-pro' ) . '</p>';
			}
		}

	    /**
	     * helper function for displaying the saved organizer dropdown
	     *
	     * @since 2.0
	     * @param mixed $current the current saved venue
	     * @param string $name the name value for the field
	     */
		public function saved_organizers_dropdown( $current = null, $name = 'organizer[OrganizerID]' ){
			$my_organizer_ids = array();
			$current_user = wp_get_current_user();
			$my_organizers = false;
			$my_organizers_options = '';
			if ( 0 != $current_user->ID ) {
			    $my_organizers = TribeEvents::instance()->get_organizer_info( null, null, array('post_status' => array('publish', 'draft'), 'author' => $current_user->ID) );
			    if( !empty($my_organizers)) {
					foreach($my_organizers as $my_organizer) {
						$my_organizer_ids[] = $my_organizer->ID;
						$organizer_title = wp_kses( get_the_title( $my_organizer->ID ), array() );
						$my_organizers_options .= '<option value="' . esc_attr( $my_organizer->ID ) .'"';
						$my_organizers_options .= selected( $current, $my_organizer->ID, false );
						$my_organizers_options .=  '>' . $organizer_title . '</option>';
					}
				}
			}
			
			$organizers = TribeEvents::instance()->get_organizer_info( null, null, array('post_status' => 'publish', 'post__not_in' => $my_organizer_ids) );
			if ( $organizers || $my_organizers ) {
				echo '<select class="chosen organizer-dropdown" name="' . esc_attr( $name ) . '" id="saved_organizer">';
				echo '<option value="0">' . __( 'Use New Organizer' ,  'tribe-events-calendar-pro' ) . '</option>';
				if( $my_organizers ) {
					echo $organizers ? '<optgroup label="' . apply_filters('tribe_events_saved_organizers_dropdown_my_optgroup', __('My Organizers', 'tribe-events-calendar-pro')) . '">' : '';
					echo $my_organizers_options;
					echo $organizers ? '</optgroup>' : '';
				}
				if ( $organizers ) {
					echo $my_organizers ? '<optgroup label="' . apply_filters('tribe_events_saved_organizers_dropdown_optgroup', __('Available Organizers', 'tribe-events-calendar-pro')) . '">' : '';
					foreach ( $organizers as $organizer ) {
						$organizer_title = wp_kses( get_the_title( $organizer->ID ), array() );
						echo '<option value="' . esc_attr( $organizer->ID ) .'"';
						selected( ($current == $organizer->ID) );
						echo '>' . $organizer_title . '</option>';
					}
					echo $my_organizers ? '</optgroup>'	: '';
				}
				echo '</select>';
			} else {
				echo '<p class="nosaved">' . __( 'No saved organizers yet.', 'tribe-events-calendar-pro' ) . '</p>';
			}
		}

	    /**
	     * Add the default settings tab
	     *
	     * @since 2.0.5
	     * @author jkudish
	     * @return void
	     */
	  	public function add_settings_tabs() {
			require_once( $this->pluginPath . 'admin-views/tribe-options-defaults.php' );
			new TribeSettingsTab( 'defaults', __( 'Defaults', 'tribe-events-calendar-pro' ), $defaultsTab );
			// The single-entry array at the end allows for the save settings button to be displayed.
			new TribeSettingsTab( 'additional-fields', __( 'Additional Fields', 'tribe-events-calendar-pro' ), array( 'priority' => 35, 'fields' => array( null ) ) );
	  	}


		public function add_help_tab_getting_started_text() {
			$ga_query_string = '?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin';
			$getting_started_text[] = sprintf( __('If this is your first time using The Events Calendar, you\'re in for a treat. You\'re going to find it super-easy to get up and running with managing your events. Here are some ways to get started:</p><ul><li><strong>Feeling adventurous?</strong> Jump right into it by visiting the Events menu to %sadd your first event%s.</li><li><strong>Want to get the low-down first?</strong> Visit our <a href="http://tri.be/support/documentation/events-calendar-pro-new-user-primer/' .$ga_query_string .'">new user primer</a>, designed with folk exactly like yourself in mind to help familiarize you with the plugin basics.</li></ul><p>Next, check out resources below, created to help you kick ass.</p>', 'tribe-events-calendar' ), '<a href="' . add_query_arg( array( 'post_type' => TribeEvents::POSTTYPE ), 'post-new.php' ) . '">' , '</a>' );
			$getting_started_text[] = sprintf( __( '%sOh, wondering what to do with your license key and whether you need it before you can get into event creation? Check out %s on that subject for an answer. %s, if you don\'t have it handy.%s', 'tribe-events-calendar-pro' ), '<p>', sprintf( '<a href="http://tri.be/events-calendar-pro-license-keys-when-you-need-them-when-you-dont/' . $ga_query_string . '">%s</a>', __( 'our blog post', 'tribe-events-calendar-pro' ) ), sprintf( '<a href="http://tri.be/finding-your-pro-license-key-re-downloading-the-plugin/' . $ga_query_string . '">%s</a>', __( 'Here\'s how you find your license key', 'tribe-events-calendar-pro' ) ), '</p>' );
			$content = implode( $getting_started_text );
			return $content;
		}

		public function add_help_tab_enb_text() {
			$ga_query_string = '?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin';
			$enb_text[] = sprintf( __( '%sOne of the advantages of being a PRO user is that you have access to our PRO-exclusive forums at %s. Our support staff hits the forums on a daily basis, and what they can\'t answer on the spot they\'ll bring a member of our dev team in to address directly.%s', 'tribe-events-calendar-pro' ), '<p class="admin-indent">', sprintf( '<a href="http://tri.be/support/forums/' . $ga_query_string . '">%s</a>', 'tri.be' ), '</p>' );
			$enb_text[] = sprintf( __( '%sSome things to consider before posting on the forum:%s', 'tribe-events-calendar' ), '<p class="admin-indent">', '</p><ul class="admin-list">' );
			$enb_text[] = sprintf( __( '%sLook through existing threads before posting a new one and check that there isn\'t already a discussion going on your issue. The tri.be site has a solid search function that should help find what you\'re looking for, if it indeed already is present.%s', 'tribe-events-calendar-pro' ), '<li>', '</li>' );
			$enb_text[] = sprintf( __( '%sA good way to help us out before posting is to check whether the issue is a conflict with another plugin or your theme. This can be tested relatively easily on a staging site by deactivating other plugins one-by-one, and reverting to the default 2011 theme as needed, to see if conflicts can be easily identified. If so, please note that when posting your thread.%s', 'tribe-events-calendar-pro' ), '<li>', '</li>' );
			$enb_text[] = sprintf( __( '%sSometimes, just resaving your permalinks (under Settings -> Permalinks) can resolve events-related problems on your site. It is worth a shot before creating a new thread.%s', 'tribe-events-calendar' ), '<li>', '</li></ul>' );
			$enb_text[] = sprintf( __( '%sWhile we won\'t build your site for you and can\'t guarantee The Events Calendar/PRO to play nicely with every theme and plugin out there, our team will do our best to help you get it functioning nicely with your site. And as an added bonus, once you\'re done you can post it in the %s so the rest of the community can see what you\'ve been working on.%s', 'tribe-events-calendar-pro' ), '<p class="admin-indent">', sprintf( '<a href="http://tri.be/support/forums/topic/showcase-2-0/' . $ga_query_string . '">%s</a>', __( 'Showcase thread', 'tribe-events-calendar-pro' ) ), '</p>' );
			$content = implode( $enb_text );
			return $content;
		}


		public function select_venue_template( $template ) {
			return ( is_singular( TribeEvents::VENUE_POST_TYPE ) ) ? TribeEventsTemplates::getTemplateHierarchy( 'single-venue' ) : $template;
		}

		public function load_venue_template( $file ) {
			return ( file_exists( $file ) ) ? $file : $this->pluginPath . 'views/single-venue.php';
		}

		public function add_google_map_preview( $postId ) {
			if ( !$postId )
				return;

			if ( tribe_get_option( 'embedGoogleMaps' ) ) {
				$display = tribe_embed_google_map( $postId ) ? 'block' : 'none';
				?>
				<div style="float:right; display:<?php echo $display ?>;">
					<?php echo tribe_get_embedded_map( $postId, 200, 200, true ); ?>
				</div>
				<?php
			}
			?>
			<div style="clear:both"></div>
			<?php
		}

		public function enqueue_resources() {
			if ( is_admin() ) {
				wp_enqueue_script( TribeEvents::POSTTYPE.'-premium-admin', $this->pluginUrl . 'resources/events-admin.js', array( 'jquery-ui-datepicker' ), '', true );
			}
		}

		public function iCalFeed( $post = null, $eventCatSlug = null, $eventDate = null ) {

			$tribeEvents = TribeEvents::instance();
			$postId = $post ? $post->ID : null;
			$getstring = ( isset( $_GET['ical'] ) ? $_GET['ical'] : null );
			$wpTimezoneString = get_option( 'timezone_string' );
			$postType = TribeEvents::POSTTYPE;
			$events = '';
			$lastBuildDate = '';
			$eventsTestArray = array();
			$blogHome = get_bloginfo( 'url' );
			$blogName = get_bloginfo( 'name' );
			$includePosts = ( $postId ) ? '&include=' . $postId : '';
			$eventsCats = ( $eventCatSlug ) ? '&' . TribeEvents::TAXONOMY . '=' . $eventCatSlug : '';

			if ( $post ) {
				$eventPosts = array();
				$eventPosts[] = $post;
			} else {
				$eventPosts = get_posts( 'posts_per_page=-1&post_type=' . $postType . $includePosts . $eventsCats );
			}

			foreach ( $eventPosts as $eventPost ) {
				if ( $eventDate ) {
					$duration = TribeDateUtils::timeBetween( $eventPost->EventStartDate, $eventPost->EventEndDate );
					$startDate = TribeDateUtils::addTimeToDate( $eventDate, TribeDateUtils::timeOnly( $eventPost->EventStartDate ) );
					$endDate = TribeDateUtils::dateAndTime( strtotime( $startDate ) + $duration, true );
				} else {
					$startDate = $eventPost->EventStartDate;
					$endDate = $eventPost->EventEndDate;
				}

				// convert 2010-04-08 00:00:00 to 20100408T000000 or YYYYMMDDTHHMMSS
				$startDate = str_replace( array( '-', ' ', ':' ) , array( '', 'T', '' ) , $startDate );
				$endDate = str_replace( array( '-', ' ', ':' ) , array( '', 'T', '' ) , $endDate );
				if ( get_post_meta( $eventPost->ID, '_EventAllDay', true ) == 'yes' ) {
					$startDate = substr( $startDate, 0, 8 );
					$endDate = substr( $endDate, 0, 8 );
					// endDate bumped ahead one day to counter iCal's off-by-one error
					$endDateStamp = strtotime( $endDate );
					$endDate = date( 'Ymd', $endDateStamp + 86400 );
					$type = 'DATE';
				} else {
					$type = 'DATE-TIME';
				}
				$description = preg_replace( "/[\n\t\r]/", ' ', strip_tags( $eventPost->post_content ) );

				// add fields to iCal output
				$item = array();
				$item[] = "DTSTART;VALUE=$type:" . $startDate;
				$item[] = "DTEND;VALUE=$type:" . $endDate;
				$item[] = 'DTSTAMP:' . date( 'Ymd\THis', time() );
				$item[] = 'CREATED:' . str_replace( array( '-', ' ', ':' ) , array( '', 'T', '' ) , $eventPost->post_date );
				$item[] = 'LAST-MODIFIED:' . str_replace( array( '-', ' ', ':' ) , array( '', 'T', '' ) , $eventPost->post_modified );
				$item[] = 'UID:' . $eventPost->ID . '-' . strtotime( $startDate ).'-'.strtotime( $endDate ) . '@' . $blogHome;
				$item[] = 'SUMMARY:' . $eventPost->post_title;
				$item[] = 'DESCRIPTION:' . str_replace( ',','\,', $description );
				$item[] = 'LOCATION:' . html_entity_decode( $tribeEvents->fullAddressString( $eventPost->ID ), ENT_QUOTES );
				$item[] = 'URL:' . get_permalink( $eventPost->ID );

				$item = apply_filters( 'tribe_ical_feed_item', $item, $eventPost );

				$events .= "BEGIN:VEVENT\n" . implode( "\n",$item ) . "\nEND:VEVENT\n";
			}

			header( 'Content-type: text/calendar' );
			header( 'Content-Disposition: attachment; filename="iCal-TribeEvents.ics"' );
			$content = "BEGIN:VCALENDAR\n";
			$content .= "VERSION:2.0\n";
			$content .= 'PRODID:-//' . $blogName . ' - ECPv' . TribeEvents::VERSION . "//NONSGML v1.0//EN\n";
			$content .= "CALSCALE:GREGORIAN\n";
			$content .= "METHOD:PUBLISH\n";
			$content .= 'X-WR-CALNAME:' . apply_filters( 'tribe_ical_feed_calname', $blogName ) . "\n";
			$content .= 'X-ORIGINAL-URL:' . $blogHome . "\n";
			$content .= 'X-WR-CALDESC:Events for ' . $blogName . "\n";
			if ( $wpTimezoneString ) $content .= 'X-WR-TIMEZONE:' . $wpTimezoneString . "\n";
			$content = apply_filters( 'tribe_ical_properties', $content );
			$content .= $events;
			$content .= 'END:VCALENDAR';
			echo $content;
			exit;
		}

		public function googleCalendarLink( $postId = null ) {
			global $post;
			$tribeEvents = TribeEvents::instance();

			if ( $postId === null || !is_numeric( $postId ) ) {
				$postId = $post->ID;
			}
			// protecting for reccuring because the post object will have the start/end date available
			$start_date = isset($post->EventStartDate) ? strtotime($post->EventStartDate) : strtotime( get_post_meta( $postId, '_EventStartDate', true ) );
			$end_date = isset($post->EventEndDate) ? 
				strtotime( $post->EventEndDate . ( get_post_meta( $postId, '_EventAllDay', true ) ? ' + 1 day' : '') ) :
				strtotime( get_post_meta( $postId, '_EventEndDate', true ) . ( get_post_meta( $postId, '_EventAllDay', true ) ? ' + 1 day' : '') );

			$dates = ( get_post_meta( $postId, '_EventAllDay', true ) ) ? date( 'Ymd', $start_date ) . '/' . date( 'Ymd', $end_date ) : date( 'Ymd', $start_date ) . 'T' . date( 'Hi00', $start_date ) . '/' . date( 'Ymd', $end_date ) . 'T' . date( 'Hi00', $end_date );
			$location = trim( $tribeEvents->fullAddressString( $postId ) );
			$base_url = 'http://www.google.com/calendar/event';
			$event_details = substr( get_the_content(), 0, 996 ) . '...';

			$params = array(
				'action' => 'TEMPLATE',
				'text' => str_replace( ' ', '+', strip_tags( urlencode( $post->post_title ) ) ),
				'dates' => $dates,
				'details' => str_replace( ' ', '+', strip_tags( apply_filters( 'the_content', urlencode( $event_details ) ) ) ),
				'location' => str_replace( ' ', '+', urlencode( $location ) ),
				'sprop' => get_option( 'blogname' ),
				'trp' => 'false',
				'sprop' => 'website:' . home_url(),
			);
			$params = apply_filters( 'tribe_google_calendar_parameters', $params );
			$url = add_query_arg( $params, $base_url );
			return esc_url( $url );
		}
		
		/**
		 * Return the forums link as it should appear in the help tab.
		 *
		 * @since 2.0.8
		 *
		 * @return string
		 */
		public function helpTabForumsLink( $content ) {
			$promo_suffix = '?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin';
			if ( get_option( 'pue_install_key_events_calendar_pro ' ) )
				return 'http://tri.be/support/forums/forum/events/events-calendar-pro/' . $promo_suffix;
			else
				return 'http://tri.be/support/forums/' . $promo_suffix;
		}
		
		/**
		 * Return additional action for the plugin on the plugins page.
		 *
		 * @since 2.0.8
		 *
		 * @return array
		 */
		public function addLinksToPluginActions( $actions ) {
			if( class_exists( 'TribeEvents' ) ) {
				$actions['settings'] = '<a href="' . add_query_arg( array( 'post_type' => TribeEvents::POSTTYPE, 'page' => 'tribe-events-calendar' ), admin_url( 'edit.php' ) ) .'">' . __('Settings', 'tribe-events-calendar-pro') . '</a>';
			}
			return $actions;
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
			add_filter( 'tribe_apm_textdomain', array( __CLASS__, 'apm_textdomain' ) );
			// load text domain after class registration
			load_plugin_textdomain( 'tribe-events-calendar-pro', false, basename( dirname( dirname( __FILE__ ) ) ) . '/lang/' );
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
		* @return string The new banner.
		*/
		public function tribePromoBannerPro() {
			return sprintf( __( 'Calendar powered by %sThe Events Calendar PRO%s', 'tribe-events-calendar-pro' ), '<a href="http://tri.be/wordpress-events-calendar-pro/">', '</a>' );
		}


		/**
		* Add meta links on the plugin page
		*/
		public function addMetaLinks( $links, $file ) {
			if ( $file == $this->pluginDir . 'events-calendar-pro.php' ) {
				$anchor = __( 'Support', 'tribe-events-calendar-pro' );
				$links [] = '<a href="' . self::$updateUrl . 'support/?ref=ecp-plugin">' . $anchor . '</a>';
				$anchor = __( 'View All Add-Ons', 'tribe-events-calendar-pro' );
				$links [] = '<a href="' . self::$updateUrl . 'shop/?ref=ecp-plugin">' . $anchor . '</a>';
			}
			return $links;
		}


		/* Static Methods */
		public static function instance() {
			if ( !isset( self::$instance ) ) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}

	} // end Class


	// Instantiate class and set up WordPress actions.
	function Tribe_ECP_Load() {
		add_filter( 'tribe_tec_addons', 'tribe_init_ecp_addon' );
		add_filter( 'tribe_tec_addons_comparison_operator', 'tribe_version_compare_operator' );
		$to_run_or_not_to_run = ( class_exists( 'TribeEvents' ) && defined( 'TribeEvents::VERSION' ) && version_compare( TribeEvents::VERSION, TribeEventsPro::REQUIRED_TEC_VERSION, '>=' ) );
		if ( apply_filters( 'tribe_ecp_to_run_or_not_to_run', $to_run_or_not_to_run ) ) {
			TribeEventsPro::instance();
		}
		if ( !class_exists( 'TribeEvents' ) ) {
			add_action( 'admin_notices', 'tribe_show_fail_message' );
		}
	}

	add_action( 'plugins_loaded', 'Tribe_ECP_Load', 1); // high priority so that it's not too late for tribe_register-helpers class

	/**
	 * Shows message if the plugin can't load due to TEC not being installed.
	 */
	function tribe_show_fail_message() {
		if ( current_user_can( 'activate_plugins' ) ) {
			$url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
			$title = __( 'The Events Calendar', 'tribe-events-calendar-pro' );
			echo '<div class="error"><p>'.sprintf( __( 'To begin using Events Calendar PRO, please install the latest version of <a href="%s" class="thickbox" title="%s">The Events Calendar</a>.', 'tribe-events-calendar-pro' ),$url, $title ).'</p></div>';
		}
	}

	/**
	* Add Events PRO to the list of add-ons to check required version.
	*
	* @author Paul Hughes, jkudish
	* @since 2.0.5
	* @return array $plugins the required info
	*/
	function tribe_init_ecp_addon( $plugins ) {
		$plugins['TribeEventsPro'] = array( 'plugin_name' => 'Events Calendar PRO', 'required_version' => TribeEventsPro::REQUIRED_TEC_VERSION, 'current_version' => TribeEventsPro::VERSION, 'plugin_dir_file' => basename( dirname( __FILE__ ) ) . '/events-calendar-pro.php' );
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

	register_deactivation_hook( __FILE__, 'tribe_ecp_deactivate' );
	register_uninstall_hook( __FILE__, 'tribe_ecp_uninstall' );

	// when we deactivate pro, we should reset some options
	function tribe_ecp_deactivate() {
		if ( function_exists( 'tribe_update_option' ) ) {
			tribe_update_option( 'defaultValueReplace', true );
			tribe_update_option( 'defaultCountry', null );
		}
	}

	function tribe_ecp_uninstall() {
		delete_option( 'pue_install_key_events_calendar_pro' );
	}
} // end if Class exists