<?php
/*
Plugin Name: The Events Calendar PRO
Description: The Events Calendar PRO, a premium add-on to the open source The Events Calendar plugin (required), enables recurring events, custom attributes, venue pages, new widgets and a host of other premium features.
Version: 3.9.3
Author: Modern Tribe, Inc.
Author URI: http://m.tri.be/20
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

		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginSlug;

		public $weekSlug = 'week';
		public $photoSlug = 'photo';

		/** @var TribeEventsPro_RecurrencePermalinks */
		public $permalink_editor = null;

		/**
		 * @var TribeEventsPro_SingleEventMeta
		 */
		public $single_event_meta;

		/**
		 * @var TribeEventsPro_EmbeddedMaps
		 */
		public $embedded_maps;

		/**
		 * @var Tribe__Events__Pro__Mini_Calendar_Shortcode
		 */
		public $mini_calendar_shortcode;

		const REQUIRED_TEC_VERSION = '3.9.2';
		const VERSION = '3.9.3';

        /**
         * Class constructor.
         */
        private function __construct() {
			$this->pluginDir = trailingslashit( basename( dirname( __FILE__ ) ) );
			$this->pluginPath = trailingslashit( dirname( __FILE__ ) );
			$this->pluginUrl = plugins_url( $this->pluginDir );
			$this->pluginSlug = 'events-calendar-pro';

			$this->loadTextDomain();

			$this->weekSlug = sanitize_title(__('week', 'tribe-events-calendar-pro'));
			$this->photoSlug = sanitize_title(__('photo', 'tribe-events-calendar-pro'));

			require_once( $this->pluginPath . 'lib/tribeeventspro-schemaupdater.php' );
			require_once( $this->pluginPath . 'lib/tribe-pro-template-factory.class.php' );
			require_once( $this->pluginPath . 'lib/tribe-date-series-rules.class.php' );
			require_once( $this->pluginPath . 'lib/tribe-ecp-custom-meta.class.php' );
			require_once( $this->pluginPath . 'lib/tribe-events-recurrence-meta.class.php' );
			require_once( $this->pluginPath . 'lib/tribeeventspro-recurrenceseriessplitter.php' );
			require_once( $this->pluginPath . 'lib/tribeeventspro-recurrenceinstance.php');
			require_once( $this->pluginPath . 'lib/tribe-recurrence.class.php' );
			require_once( $this->pluginPath . 'lib/tribeeventspro-recurrencepermalinks.php' );
			require_once( $this->pluginPath . 'lib/widget-venue.class.php' );
			require_once( $this->pluginPath . 'lib/tribe-mini-calendar.class.php' );
			require_once( $this->pluginPath . 'lib/widget-countdown.class.php' );
			require_once( $this->pluginPath . 'lib/widget-calendar.class.php' );

			require_once( $this->pluginPath . 'lib/template-classes/map.php' );
			require_once( $this->pluginPath . 'lib/template-classes/photo.php' );
			require_once( $this->pluginPath . 'lib/template-classes/single-organizer.php' );
			require_once( $this->pluginPath . 'lib/template-classes/single-venue.php' );
			require_once( $this->pluginPath . 'lib/template-classes/week.php' );

			require_once( $this->pluginPath . 'public/template-tags/general.php' );
			require_once( $this->pluginPath . 'public/template-tags/week.php' );
			require_once( $this->pluginPath . 'public/template-tags/venue.php' );
			require_once( $this->pluginPath . 'public/template-tags/widgets.php' );
			require_once( $this->pluginPath . 'lib/tribe-geoloc.class.php' );
			require_once( $this->pluginPath . 'lib/EmbeddedMaps.php' );
			require_once( $this->pluginPath . 'lib/SingleEventMeta.php' );
			require_once( $this->pluginPath . 'lib/Mini_Calendar_Shortcode.php' );

			if ( TribeEventsPro_SchemaUpdater::update_required() ) {
				add_action( 'admin_init', array( 'TribeEventsPro_SchemaUpdater', 'init' ), 10, 0 );
			}

			// Tribe common resources
			TribeCommonLibraries::register( 'advanced-post-manager', '1.0.5', $this->pluginPath . 'vendor/advanced-post-manager/tribe-apm.php' );
			add_action( 'tribe_helper_activation_complete', array( $this, 'helpersLoaded' ) );

			add_action( 'init', array( $this, 'init' ), 10 );
			add_action( 'tribe_events_enqueue', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'tribe_venues_enqueue', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_pro_scripts' ), 8);
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			add_action( 'tribe_settings_do_tabs', array( $this, 'add_settings_tabs' ) );
			add_filter( 'tribe_settings_tab_fields', array( $this, 'filter_settings_tab_fields' ), 10, 2 );
			add_filter( 'generate_rewrite_rules', array( $this, 'add_routes' ), 11 );
			add_action( 'tribe_events_parse_query', array( $this, 'parse_query'));
			add_action( 'tribe_events_pre_get_posts', array( $this, 'pre_get_posts'));
			add_filter( 'tribe_enable_recurring_event_queries', '__return_true', 10, 1 );
			add_filter( 'body_class', array( $this, 'body_class') );
			add_filter( 'tribe_current_events_page_template', array( $this, 'select_page_template' ) );
			add_filter( 'tribe_current_events_template_class', array( $this, 'get_current_template_class' ) );
			add_filter( 'tribe_events_template_paths', array( $this, 'template_paths' ) );
			add_filter( 'tribe_events_template_class_path', array( $this, 'template_class_path' ) );

			add_filter( 'tribe_help_tab_getting_started_text', array( $this, 'add_help_tab_getting_started_text' ) );
			add_filter( 'tribe_help_tab_introtext', array( $this, 'add_help_tab_intro_text' ) );
			add_filter( 'tribe_help_tab_forumtext', array( $this, 'add_help_tab_forumtext' ) );

			add_action( 'widgets_init', array( $this, 'pro_widgets_init' ), 100 );
			add_action( 'wp_loaded', array( $this, 'allow_cpt_search' ) );
			add_action( 'plugin_row_meta', array( $this, 'addMetaLinks' ), 10, 2 );
			add_filter( 'tribe_get_events_title', array( $this, 'reset_page_title' ), 10, 2 );
			add_filter( 'tribe_events_title_tag', array($this, 'maybeAddEventTitle' ), 10, 3 );

			add_filter( 'tribe_help_tab_forums_url', array( $this, 'helpTabForumsLink' ) );
			add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(
				$this,
				'addLinksToPluginActions'
			) );

			add_filter( 'tribe_events_before_html', array( $this, 'events_before_html' ), 10 );

			// add custom fields to "the_meta" on single event template
			add_filter( 'tribe_events_single_event_the_meta_addon', array(
				$this,
				'single_event_the_meta_addon'
			), 10, 2 );
			add_filter( 'tribe_events_single_event_meta_group_template_keys', array(
				$this,
				'single_event_meta_group_template_keys'
			), 10 );
			add_filter( 'tribe_events_single_event_meta_template_keys', array(
				$this,
				'single_event_meta_template_keys'
			), 10 );
			add_filter( 'tribe_event_meta_venue_name', array('TribeEventsPro_SingleEventMeta', 'venue_name'), 10, 2);
			add_filter( 'tribe_event_meta_organizer_name', array(
				'TribeEventsPro_SingleEventMeta',
				'organizer_name'
			), 10, 2 );
			add_filter( 'tribe_events_single_event_the_meta_group_venue', array(
				$this,
				'single_event_the_meta_group_venue'
			), 10, 2 );

			$this->enable_recurring_info_tooltip();
			add_action( 'tribe_events_before_the_grid', array( $this, 'disable_recurring_info_tooltip' ), 10, 0 );
			add_action( 'tribe_events_after_the_grid', array( $this, 'enable_recurring_info_tooltip' ), 10, 0 );
			add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'register_related_events_view' ) );

			// see function tribe_convert_units( $value, $unit_from, $unit_to )
			add_filter( 'tribe_convert_kms_to_miles_ratio', array( $this, 'kms_to_miles_ratio' ) );
			add_filter( 'tribe_convert_miles_to_kms_ratio', array( $this, 'miles_to_kms_ratio' ) );

			/* Setup Tribe Events Bar */
			add_filter( 'tribe-events-bar-views', array( $this, 'setup_weekview_in_bar' ), 10, 1 );
			add_filter( 'tribe-events-bar-views', array( $this, 'setup_photoview_in_bar' ), 30, 1 );
			add_filter( 'tribe_events_ugly_link', array( $this, 'ugly_link' ), 10, 3);
			add_filter( 'tribe_events_getLink', array( $this, 'get_link' ), 10, 4 );
			add_filter( 'tribe_bar_datepicker_caption', array( $this, 'setup_datepicker_label' ), 10, 1 );
			add_action( 'tribe_events_after_the_title', array( $this, 'add_recurring_occurance_setting_to_list' ) );

			add_filter( 'tribe_is_ajax_view_request', array( $this, 'is_pro_ajax_view_request' ), 10, 2 );

			add_action( 'tribe_events_pre_get_posts' , array( $this, 'setup_hide_recurrence_in_query' ) );

			add_filter( 'wp' , array( $this, 'detect_recurrence_redirect' ) );
			add_filter( 'template_redirect', array( $this, 'filter_canonical_link_on_recurring_events' ), 10, 1 );

			$this->permalink_editor = apply_filters( 'tribe_events_permalink_editor', new TribeEventsPro_RecurrencePermalinks() );
			add_filter( 'post_type_link', array(
				$this->permalink_editor,
				'filter_recurring_event_permalinks'
			), 10, 4 );

			add_filter( 'tribe_events_register_venue_type_args', array( $this, 'addSupportsThumbnail' ), 10, 1 );
			add_filter( 'tribe_events_register_organizer_type_args', array( $this, 'addSupportsThumbnail' ), 10, 1 );

			// filter the query sql to get the recurrence end date
			add_filter( 'tribe_events_query_posts_joins', array($this, 'posts_join'));
			add_filter( 'tribe_events_query_posts_fields', array($this, 'posts_fields'));

		}

		/**
		 * @return bool Whether related events should be shown in the single view
		 */
		public function show_related_events() {
			if ( tribe_get_option('hideRelatedEvents', false) == true ) {
				return false;
			}

			return true;
		}

		/**
		 * add related events to single event view
		 *
		 * @return void
		 */
		public function register_related_events_view() {
			if ( $this->show_related_events() ) {
				tribe_single_related_events();
			}
		}

		/**
		 * Append the recurring info tooltip after an event schedule
		 *
		 * @param string $schedule_details
		 * @param int $event_id
		 *
		 * @return string
		 */
		public function append_recurring_info_tooltip( $schedule_details, $event_id = 0 ) {
			$tooltip = tribe_events_recurrence_tooltip($event_id);

			return $schedule_details . $tooltip;
		}

		public function enable_recurring_info_tooltip() {
			add_filter( 'tribe_events_event_schedule_details', array( $this, 'append_recurring_info_tooltip' ), 9, 2 );
		}

		public function disable_recurring_info_tooltip() {
			remove_filter(
				'tribe_events_event_schedule_details', array(
					$this,
					'append_recurring_info_tooltip'
				), 9, 2
			);
		}

		public function recurring_info_tooltip_status() {
			if ( has_filter(
				'tribe_events_event_schedule_details', array(
					$this,
					'append_recurring_info_tooltip'
				)
			)
			) {
				return true;
			}

			return false;
		}

		/**
		 * Filters in a meta walker group for new items regarding the PRO addon.
		 *
		 * @param string $html The current HTML for the event meta..
		 * @param int $event_id The post_id of the current event.
		 *
		 * @return string The modified HTML for the event meta.
		 */
		function single_event_the_meta_addon( $html, $event_id){

			// add custom meta if it's available
			$html .= tribe_get_meta_group('tribe_event_group_custom_meta');

			return $html;
		}

		/**
		 * Adds for the meta walker a key for custom meta to do with PRO addon.
		 *
		 * @param array $keys The current array of meta keys.
		 *
		 * @return array The modified array.
		 */
		function single_event_meta_template_keys( $keys ){
			$keys[] = 'tribe_event_custom_meta';

			return $keys;
		}

		/**
		 * Adds for the meta walker a key for custom meta groups to do with PRO addon.
		 *
		 * @param array $keys The current array of meta keys.
		 *
		 * @return array The modified array.
		 */
		function single_event_meta_group_template_keys( $keys ){
			$keys[] = 'tribe_event_group_custom_meta';

			return $keys;
		}

		/**
		 * Adds (currently nothing) to the venue section of the meta walker for single events.
		 *
		 * @param bool $status Whether currently it is filtered to display venue information in a group or not.
		 * @param int $event_id The post_id of the current event.
		 *
		 * @return bool The modified boolean.
		 */
		function single_event_the_meta_group_venue( $status, $event_id ){

			return $status;
		}

		/**
		 * Modifies the page title for pro views.
		 *
		 * @param string $new_title The currently filtered title.
		 * @param string $title The oldest default title.
		 * @param string $sep The separator for title elements.
		 *
		 * @return string The modified title.
		 * @todo remove in 3.10
		 * @deprecated
		 */
		function maybeAddEventTitle( $new_title, $title, $sep = null ){
			if ( has_filter( 'tribe_events_pro_add_title' ) ) {
				_deprecated_function( "The 'tribe_events_pro_add_title' filter", '3.8', " the 'tribe_events_add_title' filter" );

				return apply_filters( 'tribe_events_pro_add_title', $new_title, $title, $sep );
			}

			return $new_title;
		}

        /**
         * Gets the events_before_html content.
         *
         * @param string $html The events_before_html currently.
		 *
         * @return string The modified html.
         */
        function events_before_html( $html ) {
			global $wp_query;
			if ( $wp_query->tribe_is_event_venue || $wp_query->tribe_is_event_organizer ) {
				add_filter( 'tribe-events-bar-should-show', '__return_false' );
			}

			return $html;
		}

		/**
		 * Sets the page title for the various PRO views.
		 *
		 * @param string $title The current title.
		 *
		 * @return string The modified title.
		 */
		function reset_page_title( $title, $depth = true ){
			global $wp_query;
			$tec = TribeEvents::instance();
			$date_format = apply_filters( 'tribe_events_pro_page_title_date_format', tribe_get_date_format( true ) );

			if( tribe_is_showing_all() ){
				$reset_title = sprintf(
					'%s %s',
					__( 'All events for', 'tribe-events-calendar-pro' ),
					get_the_title()
				);
			}

			// week view title
			if( tribe_is_week() ) {
				$reset_title = sprintf(
					__( 'Events for week of %s', 'tribe-events-calendar-pro' ),
					date_i18n( $date_format, strtotime( tribe_get_first_week_day($wp_query->get('start_date') ) ) )
					);
			}

			if ( ! empty($reset_title) && is_tax( $tec->get_event_taxonomy() ) && $depth ) {
				$cat = get_queried_object();
				$reset_title = '<a href="' . esc_url( tribe_get_events_link() ) . '">' . $reset_title . '</a>';
				$reset_title .= ' &#8250; ' . $cat->name;
			}

			return isset($reset_title) ? apply_filters( 'tribe_template_factory_debug', $reset_title, 'tribe_get_events_title' ) : $title;
		}

		/**
		 * The class init function.
		 *
		 * @return void
		 */
		public function init() {
			TribeEventsMiniCalendar::instance();
			TribeEventsCustomMeta::init();
			TribeEventsRecurrenceMeta::init();
			TribeEventsGeoLoc::instance();
			$this->displayMetaboxCustomFields();
			$this->single_event_meta = new TribeEventsPro_SingleEventMeta;
			$this->embedded_maps = new TribeEventsPro_EmbeddedMaps;
			$this->mini_calendar_shortcode = new Tribe__Events__Pro__Mini_Calendar_Shortcode;
		}

		/**
		 * At the pre_get_post hook detect if we should redirect to a particular instance
		 * for an invalid 404 recurrence entries.
		 *
		 * @return void
		 */
		function detect_recurrence_redirect(){
			global $wp_query, $wp;
			if ( ! isset( $wp_query->query_vars['eventDisplay'] ) ) {
				return false;
			}

			$current_url = null;

			switch( $wp_query->query_vars['eventDisplay'] ){
				case 'single-event':
					// a recurrence event with a bad date will throw 404 because of WP_Query limiting by date range
					if( is_404() || empty( $wp_query->query['eventDate'] ) ) {
						$recurrence_check = array_merge( array( 'posts_per_page' => -1 ), $wp_query->query );
						unset( $recurrence_check['eventDate'] );
						unset( $recurrence_check['tribe_events'] );

						// retrieve event object
						$get_recurrence_event = new WP_Query( $recurrence_check );
						// if a reccurence event actually exists then proceed with redirection
						if( !empty($get_recurrence_event->posts) && tribe_is_recurring_event($get_recurrence_event->posts[0]->ID) && get_post_status($get_recurrence_event->posts[0]) == 'publish' ){
							$current_url = TribeEvents::instance()->getLink('all', $get_recurrence_event->posts[0]->ID);
						}
						break;
					}

					// A child event should be using its parent's slug. If it's using its own, redirect.
					if ( tribe_is_recurring_event(get_the_ID()) ) {
						$event = get_post(get_the_ID());
						if ( !empty($event->post_parent) ) {
							if ( isset($wp_query->query['name']) && $wp_query->query['name'] == $event->post_name ) {
								$current_url = get_permalink($event->ID);
							}
						}
					}
					break;

			}

			if( !empty( $current_url )) {
				// redirect user with 301
				$confirm_redirect = apply_filters( 'tribe_events_pro_detect_recurrence_redirect', true, $wp_query->query_vars['eventDisplay'] );
				do_action('tribe_events_pro_detect_recurrence_redirect', $wp_query->query_vars['eventDisplay'] );
				if( $confirm_redirect ) {
					wp_safe_redirect( $current_url, 301 );
					exit;
				}
			}
		}

		public function filter_canonical_link_on_recurring_events() {
			if ( is_singular(TribeEvents::POSTTYPE) && get_query_var('eventDate') && has_action('wp_head', 'rel_canonical') ) {
				remove_action( 'wp_head', 'rel_canonical' );
				add_action( 'wp_head', array( $this, 'output_recurring_event_canonical_link' ) );
			}
		}

		public function output_recurring_event_canonical_link() {
			// set the EventStartDate so TribeEvents can filter the permalink appropriately
			$post = get_post(get_queried_object_id());
			$post->EventStartDate = get_query_var('eventDate');

			// use get_post_permalink instead of get_permalink so that the post isn't converted
			// back to an ID, then to a post again (without the EventStartDate)
			$link = get_post_permalink( $post );

			echo "<link rel='canonical' href='" . esc_url( $link ) . "' />\n";
		}

		/**
		 * Filter the event fields to use the duration to get the end date (to accomodate recurrence)
		 *
		 * @return string
		 **/
		public static function posts_fields($fields){
			$fields['event_end_date'] = "tribe_event_end_date.meta_value as EventEndDate";

			return $fields;
		}

		/**
		 * Filter the event joins to use the duration to get the end date (to accomodate recurrence)
		 *
		 * @return string
		 **/
		public static function posts_join($joins){
			global $wpdb;
			$joins['event_end_date'] = " LEFT JOIN {$wpdb->postmeta} as tribe_event_end_date ON ( {$wpdb->posts}.ID = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' ) ";

			return $joins;
		}

		/**
		 * Loop through recurrence posts array and find out the next recurring instance from right now
		 *
		 * @param WP_Post[] $event_list
		 *
		 * @return int
		 */
		public function get_last_recurrence_id( $event_list ) {
			global $wp_query;

			$event_list = empty($event_list) ? $wp_query->posts : $event_list;
			$right_now = current_time( 'timestamp' );
			$next_recurrence = 0;

			// find next recurrence date by loop
			foreach( $event_list as $key => $event ){
				if( $right_now < strtotime( $event->EventStartDate ) ) {
					$next_recurrence = $event;
				}
			}
			if( empty($next_recurrence) && !empty($event_list) ){
				$next_recurrence = reset($event_list);
			}

			return apply_filters( 'tribe_events_pro_get_last_recurrence_id', $next_recurrence->ID, $event_list, $right_now );
		}

		/**
		 * Common library plugins have been activated. Functions that need to be applied afterwards can be added here.
		 *
		 * @return void
		 */
		public function helpersLoaded() {
			remove_action( 'widgets_init', 'tribe_related_posts_register_widget' );
			require_once( $this->pluginPath . 'lib/apm_filters.php' );
			if ( class_exists( 'TribeRelatedPosts' ) ) {
				TribeRelatedPosts::instance();
				require_once( $this->pluginPath . 'vendor/tribe-related-posts/template-tags.php' );
			}
		}

		/**
		 * Determines whether or not to show the custom fields metabox for events.
		 *
		 * @return bool Whether to show or not.
		 */
	    public function displayMetaboxCustomFields(){
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
				$meta_keys = $wpdb->get_results(
					"SELECT DISTINCT pm.meta_key FROM $wpdb->postmeta pm
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
											'_FacebookID')"
				);
		    	if( empty($meta_keys) ) {
		    		remove_post_type_support( TribeEvents::POSTTYPE, 'custom-fields' );
		    		$show_box = 'hide';
		    		$r = false;
		    	} else {
		    		$show_box = 'show';
		    		$r = true;
		    	}

		    	tribe_update_option( 'disable_metabox_custom_fields', $show_box );

		    	return $r;
		    }

	    }

	    /**
	     * Add the default settings tab
	     *
	     * @return void
	     */
	  	public function add_settings_tabs() {
			require_once( $this->pluginPath . 'admin-views/tribe-options-defaults.php' );
			new TribeSettingsTab( 'defaults', __( 'Default Content', 'tribe-events-calendar-pro' ), $defaultsTab );
			// The single-entry array at the end allows for the save settings button to be displayed.
			new TribeSettingsTab( 'additional-fields', __( 'Additional Fields', 'tribe-events-calendar-pro' ), array(
				'priority' => 35,
				'fields'   => array( null )
			) );
	  	}

		public function filter_settings_tab_fields( $fields, $tab ) {
			switch ( $tab ) {
				case 'display':
					$fields = TribeEvents::array_insert_after_key(
						'tribeDisableTribeBar', $fields, array(
							'hideRelatedEvents' => array(
								'type'            => 'checkbox_bool',
								'label'           => __( 'Hide related events', 'tribe-events-calendar-pro' ),
								'tooltip'         => __( 'Remove related events from the single event view', 'tribe-events-calendar-pro' ),
								'default'         => false,
								'validation_type' => 'boolean',
							),
						)
					);
					$fields = TribeEvents::array_insert_after_key(
						'monthAndYearFormat', $fields, array(
							'weekDayFormat' => array(
								'type' => 'text',
								'label' => __('Week Day Format', 'tribe-events-calendar-pro'),
								'tooltip' => __('Enter the format to use for week days. Used when showing days of the week in Week view.', 'tribe-events-calendar-pro' ),
								'default' => 'D jS',
								'size' => 'medium',
								'validation_type' => 'html',
							),
						)
					);
					break;
			}

			return $fields;
		}

		/**
		 * Add the "Getting Started" text to the help tab for PRO addon.
		 *
		 * @return string The modified content.
		 */
		public function add_help_tab_getting_started_text() {
			$getting_started_text[] = sprintf ( __("Thanks for buying Events Calendar PRO! From all of us at Modern Tribe, we sincerely appreciate it. If you're looking for help with Events Calendar PRO, you've come to the right place. We are committed to helping make your calendar kick ass... and hope the resources provided below will help get you there.", 'tribe-events-calendar-pro'));
			$content = implode( $getting_started_text );

			return $content;
		}

		/**
		 * Add the intro text that concerns PRO to the help tab.
		 *
		 * @return string The modified content.
		 */
		public function add_help_tab_intro_text(){
			$intro_text[] = '<p>' . __("If this is your first time using The Events Calendar Pro, you're in for a treat and are already well on your way to creating a first event. Here are some basics we've found helpful for users jumping into it for the first time:", 'tribe-events-calendar-pro' ) . '</p>';
			$intro_text[] = '<ul>';
			$intro_text[] = '<li>';
			$intro_text[] = sprintf( __ ("%sOur New User Primer%s was designed for folks in your exact position. Featuring both step-by-step videos and written walkthroughs that feature accompanying screenshots, the primer aims to take you from zero to hero in no time.", 'tribe-events-calendar-pro' ), '<a href="http://m.tri.be/4t" target="blank">', '</a>' );
			$intro_text[] = '</li><li>';
			$intro_text[] = sprintf( __("%sInstallation/Setup FAQs%s from our support page can help give an overview of what the plugin can and cannot do. This section of the FAQs may be helpful as it aims to address any basic install questions not addressed by the new user primer.", 'tribe-events-calendar-pro'), '<a href="http://m.tri.be/4u" target="blank">','</a>' );
			$intro_text[] = '</li><li>';
			$intro_text[] = sprintf( __( "Take care of your license key. Though not required to create your first event, you'll want to get it in place as soon as possible to guarantee your access to support and upgrades. %sHere's how to find your license key%s, if you don't have it handy.", 'tribe-events-calendar-pro'), '<a href="http://m.tri.be/4v" target="blank">','</a>' );
			$intro_text[] = '</li></ul><p>';
			$intro_text[] = __( "Otherwise, if you're feeling adventurous, you can get started by heading to the Events menu and adding your first event.", 'tribe-events-calendar-pro');
			$intro_text[] = '</p>';
			$intro_text = implode( $intro_text );

			return $intro_text;
		}

		/**
		 * Add help text regarding the Tribe forums to the help tab.
		 *
		 * @return string The content.
		 */
		public function add_help_tab_forumtext(){
			$forum_text[] = '<p>' . sprintf( __("Written documentation can only take things so far...sometimes, you need help from a real person. This is where our %ssupport forums%s come into play.", 'tribe-events-calendar-pro'), '<a href="http://m.tri.be/4w/" target="blank">', '</a>') . '</p>';
			$forum_text[] = '<p>' . sprintf( __("Users who have purchased an Events Calendar PRO license are granted total access to our %spremium support forums%s. Unlike at the %sWordPress.org support forum%s, where our involvement is limited to identifying and patching bugs, we have a dedicated support team for PRO users. We're on the PRO forums daily throughout the business week, and no thread should go more than 24-hours without a response.", 'tribe-events-calendar-pro' ), '<a href="http://m.tri.be/4w/" target="blank">', '</a>', '<a href="http://wordpress.org/support/plugin/the-events-calendar" target="blank">', '</a>' ) . '</p>';
			$forum_text[] = '<p>' . __("Our number one goal is helping you succeed, and to whatever extent possible, we'll help troubleshoot and guide your customizations or tweaks. While we won't build your site for you, and we can't guarantee we'll be able to get you 100% integrated with every theme or plugin out there, we'll do all we can to point you in the right direction and to make you -- and your client, as is often more importantly the case -- satisfied.", 'tribe-events-calendar-pro' ) . '</p>';
			$forum_text[] = '<p>' . __("Before posting a new thread, please do a search to make sure your issue hasn't already been addressed. When posting please make sure to provide as much detail about the problem as you can (with screenshots or screencasts if feasible), and make sure that you've identified whether a plugin / theme conflict could be at play in your initial message.", 'tribe-events-calendar-pro' ) . '</p>';
			$forum_text = implode($forum_text );

			return $forum_text;
		}

		/**
		 * Add rewrite routes for custom PRO stuff and views.
		 *
		 * @param WP_Rewrite $wp_rewrite The WP_Rewrite object
		 *
		 * @return void
		 */
		public function add_routes( $wp_rewrite ) {
			$generator = $this->get_rewrite_generator($wp_rewrite);

			$week_rules = $generator->get_week_rules($this->weekSlug);
			$photo_rules = $generator->get_photo_rules($this->photoSlug);
			$tax_rules = $generator->get_taxonomy_rules();

			$wp_rewrite->rules = $week_rules + $photo_rules + $tax_rules + $wp_rewrite->rules;
		}

		private function get_rewrite_generator( WP_Rewrite $wp_rewrite ) {
			require_once( $this->pluginPath . 'lib/tribeeventspro-rewriterulegenerator.php' );
			$generator = new TribeEventsPro_RewriteRuleGenerator( $wp_rewrite );
			$tec = TribeEvents::instance();

			$base = trailingslashit( $tec->rewriteSlug );
			$generator->set_base( $base );

			$cat_base = trailingslashit( $tec->taxRewriteSlug );
			$cat_base = "(.*)" . $cat_base . "(?:[^/]+/)*";
			$generator->set_cat_base( $cat_base );

			$tag_base = trailingslashit( $tec->tagRewriteSlug );
			$tag_base = "(.*)" . $tag_base;
			$generator->set_tag_base( $tag_base );

			return $generator;
		}

		/**
		 * Adds the proper css class(es) to the body tag.
		 *
		 * @param array $classes The current array of body classes.
		 *
		 * @return array The modified array of body classes.
		 * @TODO move this to template class
		 */
		public function body_class( $classes ){
			global $wp_query;

			// @TODO do we really need all these array_diff()s?

			if( $wp_query->tribe_is_event_query ) {
				if( $wp_query->tribe_is_week ) {
					$classes[] = ' tribe-events-week';
					// remove the default gridview class from core
					$classes = array_diff($classes, array('events-gridview'));
				}
				if( $wp_query->tribe_is_photo ) {
					$classes[] = ' tribe-events-photo';
					// remove the default gridview class from core
					$classes = array_diff($classes, array('events-gridview'));
				}
				if ( $wp_query->tribe_is_map ) {
					$classes[] = ' tribe-events-map';
					// remove the default gridview class from core
					$classes = array_diff( $classes, array( 'events-gridview' ) );
				}
				if ( tribe_is_map() || !tribe_get_option( 'hideLocationSearch', false ) ) {
					$classes[] = ' tribe-events-uses-geolocation';
				}
			}

			return $classes;
		}

		/**
		 * Set PRO query flags
		 *
		 * @param WP_Query $query The current query object.
		 *
		 * @return WP_Query The modified query object.
		 **/
		public function parse_query( $query ) {
			$query->tribe_is_week = false;
			$query->tribe_is_photo = false;
			$query->tribe_is_map = false;
			$query->tribe_is_event_pro_query = false;
			if( ! empty( $query->query_vars['eventDisplay'] ) ) {
				$query->tribe_is_event_pro_query = true;
				switch( $query->query_vars['eventDisplay'] ){
					case 'week':
						$query->tribe_is_week = true;
					break;
					case 'photo':
						$query->tribe_is_photo = true;
					break;
					case 'map':
						/*
						* Query setup for the map view is located in
						* TribeEventsGeoLoc->setup_geoloc_in_query()
						*/
						$query->tribe_is_map = true;
					break;
				}
			}
		}

		/**
		 * Add custom query modification to the pre_get_posts hook as necessary for PRO.
		 *
		 * @param WP_Query $query The current query object.
		 *
		 * @return WP_Query The modified query object.
		 */
		public function pre_get_posts( $query ){
			if ( $query->is_single() && $query->get('eventDate') ) {
				$this->set_post_id_for_recurring_event_query( $query );
			}
			if( !empty($query->tribe_is_event_pro_query) ) {
				switch( $query->query_vars['eventDisplay'] ) {
					case 'week':
						$week = tribe_get_first_week_day( $query->get('eventDate') );
						$query->set( 'eventDate', $week );
						$query->set( 'start_date', $week );
						$query->set( 'end_date', tribe_get_last_week_day( $week ) );
						$query->set( 'posts_per_page', -1 ); // show ALL week posts
						$query->set( 'hide_upcoming', false );
						break;
					case 'photo':
						$query->set( 'hide_upcoming', false );
						break;
					case 'all':
						$slug = $query->get( 'name' );
						if ( empty($slug) ) {
							break; // we shouldn't be here
						}
						unset( $query->query_vars['name'] );
						unset( $query->query_vars['tribe_events']);

						$all_ids = TribeEventsRecurrenceMeta::get_events_by_slug( $slug );
						if ( empty($all_ids) ) {
							$query->set('p', -1);
						} else {
							$query->set('post__in', $all_ids);
							$query->set('post_status', 'publish');
						}
						break;
				}
				apply_filters('tribe_events_pro_pre_get_posts', $query);
			}
		}

		/**
		 * A recurring event will have the base post's slug in the
		 * 'name' query var. We need to remove that and replace it
		 * with the correct post's ID
		 *
		 * @param WP_Query $query
		 *
		 * @return void
		 */
		private function set_post_id_for_recurring_event_query( $query ) {
			$date = $query->get( 'eventDate' );
			$slug = $query->get( 'name' );
			if ( empty($date) || empty($slug) ) {
				return; // we shouldn't be here
			}
			$cache = new TribeEventsCache();
			$post_id = $cache->get('single_event_'.$slug.'_'.$date, 'save_post' );
			if ( !empty($post_id) ) {
				unset( $query->query_vars['name'] );
				unset( $query->query_vars['tribe_events']);
				$query->set('p', $post_id);

				return;
			}
			global $wpdb;
			$parent_sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type=%s";
			$parent_sql = $wpdb->prepare( $parent_sql, $slug, TribeEvents::POSTTYPE );
			$parent_id = $wpdb->get_var($parent_sql);

			$parent_start = get_post_meta($parent_id, '_EventStartDate', true);
			if ( empty($parent_start) ) {
				return; // how does this series not have a start date?
			} else {
				$parent_start_date = date('Y-m-d', strtotime($parent_start));
				$parent_start_time = date('H:i:s', strtotime($parent_start));
			}

			if ( $parent_start_date == $date ) {
				$post_id = $parent_id;
			} else {
				$child_sql = "SELECT ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_EventStartDate' WHERE p.post_parent=%d AND p.post_type=%s AND m.meta_value=%s";
				$child_sql = $wpdb->prepare( $child_sql, $parent_id, TribeEvents::POSTTYPE, $date.' '.$parent_start_time );
				$post_id = $wpdb->get_var($child_sql);
			}

			if ( $post_id ) {
				unset( $query->query_vars['name'] );
				unset( $query->query_vars['tribe_events']);
				$query->set('p', $post_id);
				$cache->set('single_event_'.$slug.'_'.$date, $post_id, TribeEventsCache::NO_EXPIRATION, 'save_post' );
			}
		}

		/**
		 * Get the path to the current events template.
		 *
		 * @param string $template The current template path.
		 *
		 * @return string The modified template path.
		 */
		public function select_page_template( $template ) {
			// venue view
			if( is_singular( TribeEvents::VENUE_POST_TYPE ) ) {
				$template = TribeEventsTemplates::getTemplateHierarchy( 'pro/single-venue' );
			}
			// organizer view
			if( is_singular( TribeEvents::ORGANIZER_POST_TYPE ) ) {
				$template = TribeEventsTemplates::getTemplateHierarchy( 'pro/single-organizer' );
			}
			// week view
			if( tribe_is_week() ) {
				$template = TribeEventsTemplates::getTemplateHierarchy( 'pro/week' );
			}

			// photo view
			if( tribe_is_photo() ){
				$template = TribeEventsTemplates::getTemplateHierarchy( 'pro/photo' );
			}

			// map view
			if ( tribe_is_map() ) {
				$template = TribeEventsTemplates::getTemplateHierarchy( 'pro/map' );
			}

			// recurring "all" view
			if ( tribe_is_showing_all() ) {
				$template = TribeEventsTemplates::getTemplateHierarchy( 'list' );
				// don't show pagination on the "all" view
				add_filter( 'tribe_get_template_part_path_list/nav.php', '__return_empty_string' );
			}

			return $template;
		}

		/**
		 * Check the ajax request action looking for pro views
		 *
		 * @param $is_ajax_view_request bool
		 */
		public function is_pro_ajax_view_request( $is_ajax_view_request, $view ) {

			// if a particular view wasn't requested, or this isn't an ajax request, or there was no action param in the request, don't continue
			if ( $view == false || ! ( defined( 'DOING_AJAX') && DOING_AJAX ) || empty( $_REQUEST['action'] ) ) {
				return $is_ajax_view_request;
			}

			switch ( $view ) {
				case 'map' :
					$is_ajax_view_request = ( $_REQUEST['action'] == Tribe_Events_Pro_Map_Template::AJAX_HOOK );
					break;

				case 'photo' :
					$is_ajax_view_request = ( $_REQUEST['action'] == Tribe_Events_Pro_Photo_Template::AJAX_HOOK );
					break;

				case 'week' :
					$is_ajax_view_request = ( $_REQUEST['action'] == Tribe_Events_Pro_Week_Template::AJAX_HOOK );
					break;
			}

			return $is_ajax_view_request;

		}

		/**
		 * Specify the PHP class for the current page template
		 *
		 * @param string $class The current class we are filtering.
		 *
		 * @return string The class.
		 */
		public function get_current_template_class( $class ) {

			// venue view
			if ( is_singular( TribeEvents::VENUE_POST_TYPE ) ) {
				$class = 'Tribe_Events_Pro_Single_Venue_Template';
			} // organizer view
			elseif ( is_singular( TribeEvents::ORGANIZER_POST_TYPE ) ) {
				$class = 'Tribe_Events_Pro_Single_Organizer_Template';
			} // week view
			elseif ( tribe_is_week() || tribe_is_ajax_view_request( 'week' ) ) {
				$class = 'Tribe_Events_Pro_Week_Template';
			} // photo view
			elseif ( tribe_is_photo() || tribe_is_ajax_view_request( 'photo' ) ) {
				$class = 'Tribe_Events_Pro_Photo_Template';
			} // map view
			elseif ( tribe_is_map() || tribe_is_ajax_view_request( 'map' ) ) {
				$class = 'Tribe_Events_Pro_Map_Template';
			}

			return $class;

		}

		/**
		 * Add premium plugin paths for each file in the templates array
		 *
		 * @param $template_paths array
		 *
		 * @return array
		 */
		function template_paths( $template_paths = array() ) {

			$template_paths['pro'] =  $this->pluginPath;

			return $template_paths;

		}

		/**
		 * Add premium plugin paths for each file in the templates array
		 *
		 * @param $template_class_path string
		 *
		 * @return array
		 **/
		function template_class_path( $template_class_paths = array() ) {

			$template_class_paths[] = $this->pluginPath.'/lib/template-classes/';

			return $template_class_paths;

		}

		/**
		 * Enqueues the necessary JS for the admin side of things.
		 *
		 * @return void
		 */
	    public function admin_enqueue_scripts() {
	    	wp_enqueue_script( TribeEvents::POSTTYPE.'-premium-admin', $this->pluginUrl . 'resources/events-admin.js', array( 'jquery-ui-datepicker' ), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ), true );
		    $data = apply_filters( 'tribe_events_pro_localize_script', array(), 'TribeEventsProAdmin', TribeEvents::POSTTYPE.'-premium-admin' );
		    wp_localize_script( TribeEvents::POSTTYPE.'-premium-admin', 'TribeEventsProAdmin', $data);
	    }

		/**
		 * Enqueue the proper styles depending on what is requred by a given page load.
		 *
		 * @return void
		 */
		public function enqueue_styles() {

			if ( tribe_is_event_query()
					|| is_active_widget( false, false, 'tribe-events-adv-list-widget' )
					|| is_active_widget( false, false, 'tribe-mini-calendar' )
					|| is_active_widget( false, false, 'tribe-events-countdown-widget' )
					|| is_active_widget( false, false, 'next_event' )
					|| is_active_widget( false, false, 'tribe-events-venue-widget')
				) {

				Tribe_PRO_Template_Factory::asset_package( 'events-pro-css' );

			}
		}

		/**
		 * Enqueue the proper PRO scripts as necessary.
		 *
		 * @return void
		 */
		public function enqueue_pro_scripts() {
			if ( tribe_is_event_query() ) {
				// @TODO filter the tribe_events_resource_url() function
				$resources_url = trailingslashit( $this->pluginUrl ) . 'resources/';
				$path = Tribe_Template_Factory::getMinFile( $resources_url . 'tribe-events-pro.js', true );
				wp_enqueue_script(
					'tribe-events-pro', $path, array(
						'jquery',
						'tribe-events-calendar-script'
					), apply_filters( 'tribe_events_pro_js_version', TribeEventsPro::VERSION ), false
				);

				$geoloc = TribeEventsGeoLoc::instance();

				$data = array(
					'geocenter' => $geoloc->estimate_center_point(),
					'map_tooltip_event' => __( 'Event: ', 'tribe-events-calendar-pro' ),
					'map_tooltip_address' => __( 'Address: ', 'tribe-events-calendar-pro' )
				);

				$data = apply_filters( 'tribe_events_pro_localize_script', $data, 'TribeEventsPro', 'tribe-events-pro' );

				wp_localize_script( 'tribe-events-pro', 'TribeEventsPro', $data );

			}
		}

		/**
		 * Sets up to add the query variable for hiding subsequent recurrences of recurring events on the frontend.
		 *
		 * @param WP_Query $query The current query object.
		 *
		 * @return WP_Query The modified query object.
		 */
		public function setup_hide_recurrence_in_query( $query ) {

			// don't hide any recurrences on the all recurrences view
			if ( tribe_is_showing_all() || tribe_is_week() || tribe_is_month() || tribe_is_day() ) {
				return $query;
			}

			// don't hide any recurrences in the admin
			if ( is_admin() && !( defined('DOING_AJAX') && DOING_AJAX ) ) {
				return $query;
			}

			// don't override an explicitly passed value
			if ( isset( $query->query_vars['tribeHideRecurrence'] ) ) {
				return $query;
			}

			// if the admin option is set to hide recurrences, or the user option is set
			if ( tribe_get_option( 'hideSubsequentRecurrencesDefault', false ) == true || ( isset( $_REQUEST['tribeHideRecurrence'] ) && $_REQUEST['tribeHideRecurrence'] == '1' ) ) {
				$query->query_vars['tribeHideRecurrence'] = 1;
			}

			return $query;
		}

		/**
		 * Return the forums link as it should appear in the help tab.
		 *
		 * @return string
		 */
		public function helpTabForumsLink( $content ) {
			if ( get_option( 'pue_install_key_events_calendar_pro ' ) ) {
				return 'http://m.tri.be/4x';
			} else {
				return 'http://m.tri.be/4w';
		}
		}

		/**
		 * Return additional action for the plugin on the plugins page.
		 *
		 * @return array
		 */
		public function addLinksToPluginActions( $actions ) {
			if( class_exists( 'TribeEvents' ) ) {
				$actions['settings'] = '<a href="' . esc_url(
						add_query_arg(
							array(
								'post_type' => TribeEvents::POSTTYPE,
								'page'      => 'tribe-events-calendar'
							),
							admin_url( 'edit.php' )
						)
					) . '">' . __( 'Settings', 'tribe-events-calendar-pro' ) . '</a>';
			}

			return $actions;
		}

		/**
		 * Adds thumbnail/featured image support to Organizers and Venues when PRO is activated.
		 *
		 * @param array $post_type_args The current register_post_type args.
		 *
		 * @return array The new register_post_type args.
		 */
		public function addSupportsThumbnail( $post_type_args ) {
			$post_type_args['supports'][] = 'thumbnail';

			return $post_type_args;
		}

		/**
		 * Enable "view post" links on metaposts
		 *
		 * @param $messages array
		 * return array
		 */
		public function updatePostMessages ($messages) {
			global $post, $post_ID;

			$messages[TribeEvents::VENUE_POST_TYPE][1] = sprintf( __('Venue updated. <a href="%s">View venue</a>', 'tribe-events-calendar-pro'), esc_url( get_permalink($post_ID) ) );
				/* translators: %s: date and time of the revision */
			$messages[TribeEvents::VENUE_POST_TYPE][6] = sprintf( __('Venue published. <a href="%s">View venue</a>', 'tribe-events-calendar-pro'), esc_url( get_permalink($post_ID) ) );
			$messages[TribeEvents::VENUE_POST_TYPE][8] = sprintf( __('Venue submitted. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar-pro'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );
			$messages[ TribeEvents::VENUE_POST_TYPE ][9]  = sprintf(
				__( 'Venue scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview venue</a>', 'tribe-events-calendar-pro' ),
					// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'tribe-events-calendar-pro' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) )
			);
			$messages[TribeEvents::VENUE_POST_TYPE][10] = sprintf( __('Venue draft updated. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar-pro'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );

			$messages[TribeEvents::ORGANIZER_POST_TYPE][1] = sprintf( __('Organizer updated. <a href="%s">View organizer</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) );
			$messages[TribeEvents::ORGANIZER_POST_TYPE][6] = sprintf( __('Organizer published. <a href="%s">View organizer</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) );
			$messages[TribeEvents::ORGANIZER_POST_TYPE][8] = sprintf( __('Organizer submitted. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );
			$messages[ TribeEvents::ORGANIZER_POST_TYPE ][9]  = sprintf(
				__( 'Organizer scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview organizer</a>', 'tribe-events-calendar' ),
					// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'tribe-events-calendar' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) )
			);
			$messages[TribeEvents::ORGANIZER_POST_TYPE][10] = sprintf( __('Organizer draft updated. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) );

			return $messages;

		}

		/**
		 * Includes and handles registration/de-registration of the advanced list widget. Idea from John Gadbois.
		 *
		 * @return void
		 */
		public function pro_widgets_init() {
			require_once( $this->pluginPath . 'lib/Widgets.php' );
			require_once( $this->pluginPath . 'lib/widget-advanced-list.class.php' );
			unregister_widget( 'TribeEventsListWidget' );
			register_widget( 'TribeEventsAdvancedListWidget' );
			register_widget( 'TribeEventsMiniCalendarWidget' );
		}

		/**
		 * Load textdomain for localization
		 *
		 * @return void
		 */
		public function loadTextDomain() {
			load_plugin_textdomain( 'tribe-events-calendar-pro', false, $this->pluginDir . 'lang/');
		}

		/**
		* Re-registers the custom post types for venues so they allow search from the frontend.
		*
		* @return void
		*/
		public function allow_cpt_search() {
			$tec = TribeEvents::instance();
			$venue_args = $tec->getVenuePostTypeArgs();
			$venue_args['exclude_from_search'] = false;
			register_post_type( TribeEvents::VENUE_POST_TYPE, apply_filters( 'tribe_events_register_venue_type_args', $venue_args ) );
		}

		/**
		* Add meta links on the plugins page.
		*
		* @param array $links The current array of links to display.
		* @param string $file The plugin to add meta links to.
		 *
		* @return array The modified array of links to display.
		*/
		public function addMetaLinks( $links, $file ) {
			if ( $file == $this->pluginDir . 'events-calendar-pro.php' ) {
				$anchor = __( 'Support', 'tribe-events-calendar-pro' );
				$links [] = '<a href="http://m.tri.be/4z">' . $anchor . '</a>';
				$anchor = __( 'View All Add-Ons', 'tribe-events-calendar-pro' );
				$links [] = '<a href="http://m.tri.be/50">' . $anchor . '</a>';
			}

			return $links;
		}

		/**
		 * Add support for ugly links for ugly links with PRO views.
		 *
		 * @param string $eventUrl The current URL.
		 * @param string $type The type of endpoint/view whose link was requested.
		 * @param string $secondary More data that is necessary for generating the link.
		 *
		 * @return string The ugly-linked URL.
		 */
		public function ugly_link( $eventUrl, $type, $secondary ){
			switch( $type ) {
				case 'week':
					$eventUrl = add_query_arg('post_type', TribeEvents::POSTTYPE, home_url() );
					// if we're on an Event Cat, show the cat link, except for home.
					if ( $type !== 'home' && is_tax( TribeEvents::TAXONOMY ) ) {
						$eventUrl = add_query_arg( TribeEvents::TAXONOMY, get_query_var('term'), $eventUrl );
					}
					$eventUrl = add_query_arg( array( 'eventDisplay' => $type ), $eventUrl );
					if ( $secondary ) {
						$eventUrl = add_query_arg( array( 'eventDate' => $secondary ), $eventUrl );
					}
					break;
				case 'photo':
				case 'map':
					$eventUrl = add_query_arg( array( 'eventDisplay' => $type ), $eventUrl );
					break;
				case 'all':
					remove_filter(
						'post_type_link', array(
							$this->permalink_editor,
							'filter_recurring_event_permalinks'
						), 10, 4
					);
					$post_id = $secondary ? $secondary : get_the_ID();
					$parent_id = wp_get_post_parent_id( $post_id );
					if ( !empty($parent_id) ) {
						$post_id = $parent_id;
					}
					$eventUrl = add_query_arg('eventDisplay', 'all', get_permalink($post_id) );
					add_filter(
						'post_type_link', array(
							$this->permalink_editor,
							'filter_recurring_event_permalinks'
						), 10, 4
					);
					break;
				default:
					break;
			}

			return apply_filters( 'tribe_events_pro_ugly_link', $eventUrl, $type, $secondary );
		}

		/**
		 * filter TribeEvents::getLink for pro views
		 *
		 * @param  string $eventUrl
		 * @param  string $type
		 * @param  string $secondary
		 * @param  string $term
		 *
		 * @return string
		 */
		public function get_link( $eventUrl, $type, $secondary, $term ){
			switch( $type ) {
				case 'week':
					$eventUrl = trailingslashit( esc_url_raw( $eventUrl . $this->weekSlug ) );
					if ( !empty( $secondary ) ) {
						$eventUrl = esc_url_raw( trailingslashit( $eventUrl ) . $secondary );
					}
					break;
				case 'photo':
					$eventUrl = trailingslashit( esc_url_raw( $eventUrl . $this->photoSlug ) );
					if ( !empty( $secondary ) ) {
						$eventUrl = esc_url_raw( trailingslashit( $eventUrl ) . $secondary );
					}
					break;
				case 'map':
					$eventUrl = trailingslashit( esc_url_raw( $eventUrl . TribeEventsGeoLoc::instance()->rewrite_slug ) );
					if ( !empty( $secondary ) ) {
						$eventUrl = esc_url_raw( trailingslashit( $eventUrl ) . $secondary );
					}
					break;
				case 'all':
					remove_filter(
						'post_type_link', array(
							$this->permalink_editor,
							'filter_recurring_event_permalinks'
						), 10, 4
					);
					$post_id = $secondary ? $secondary : get_the_ID();
					$post_id = wp_get_post_parent_id( $post_id );
					$eventUrl = trailingslashit(get_permalink($post_id));
					$eventUrl = trailingslashit( esc_url_raw($eventUrl . 'all') );
					add_filter(
						'post_type_link', array(
							$this->permalink_editor,
							'filter_recurring_event_permalinks'
						), 10, 4
					);
					break;
				default:
					break;
			}

			return apply_filters( 'tribe_events_pro_get_link', $eventUrl, $type, $secondary, $term );
		}

		/**
		 * Add week view to the views selector in the tribe events bar.
		 *
		 * @param array $views The current array of views registered to the tribe bar.
		 *
		 * @return array The views registered with week view added.
		 */
		public function setup_weekview_in_bar( $views ) {
			$views[] = array(
				'displaying'     => 'week',
			                  'anchor'     => __( 'Week', 'tribe-events-calendar-pro' ),
			                  'event_bar_hook'       => 'tribe_events_week_before_template',
				'url'            => tribe_get_week_permalink()
			);

			return $views;
		}

		/**
		 * Add photo view to the views selector in the tribe events bar.
		 *
		 * @param array $views The current array of views registered to the tribe bar.
		 *
		 * @return array The views registered with photo view added.
		 */
		public function setup_photoview_in_bar( $views ) {
			$views[] = array(
				'displaying'     => 'photo',
			                  'anchor'     => __( 'Photo', 'tribe-events-calendar-pro' ),
			                  'event_bar_hook'       => 'tribe_events_before_template',
				'url'            => tribe_get_photo_permalink()
			);

			return $views;
		}

		/**
		 * Change the datepicker label, depending on what view the user is on.
		 *
		 * @param string $caption The current caption for the datepicker.
		 *
		 * @return string The new caption.
		 */
		public function setup_datepicker_label ( $caption ) {
			if ( tribe_is_week() ) {
				$caption = __('Week Of', 'tribe-events-calendar-pro');
			}

			return $caption;
		}

		/**
		 * Echo the setting for hiding subsequent occurrences of recurring events to frontend.
		 *
		 * @return void
		 */
		public function add_recurring_occurance_setting_to_list () {
			if ( tribe_get_option( 'userToggleSubsequentRecurrences', true ) && ! tribe_is_showing_all() && ( tribe_is_upcoming() || tribe_is_past() || tribe_is_map() || tribe_is_photo() ) || apply_filters( 'tribe_events_display_user_toggle_subsequent_recurrences', false ) ) {
				echo tribe_recurring_instances_toggle();
		}
		}

		/**
		 * Returns he ratio of kilometers to miles.
		 *
		 * @return float The ratio.
		 */
		function kms_to_miles_ratio() {
			return 0.621371;
		}

		/**
		 * Returns he ratio of miles to kilometers.
		 *
		 * @return float The ratio.
		 */
		function miles_to_kms_ratio() {
			return 1.60934;
		}

		/**
		 * The singleton function.
		 *
		 * @return TribeEventsPro The instance.
		 */
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
		$to_run_or_not_to_run = ( class_exists( 'TribeEvents' ) && defined( 'TribeEvents::VERSION' ) && version_compare( TribeEvents::VERSION, TribeEventsPro::REQUIRED_TEC_VERSION, '>=' ) );
		if ( apply_filters( 'tribe_ecp_to_run_or_not_to_run', $to_run_or_not_to_run ) ) {
			TribeEventsPro::instance();
		} else {
			/**
			 * Dummy function to avoid fatal error in edge upgrade case
			 *
			 * @todo remove in 3.1
			 * @return bool
			 **/
			function tribe_is_recurring_event() {
				return false;
			}
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
			$langpath = trailingslashit( basename( dirname( __FILE__ ) ) ) . 'lang/';
			load_plugin_textdomain( 'tribe-events-calendar-pro', false, $langpath );
			$url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
			$title = __( 'The Events Calendar', 'tribe-events-calendar-pro' );
			echo '<div class="error"><p>'.sprintf( __( 'To begin using Events Calendar PRO, please install the latest version of <a href="%s" class="thickbox" title="%s">The Events Calendar</a>.', 'tribe-events-calendar-pro' ), esc_url( $url ), $title ).'</p></div>';
		}
	}

    /**
	 * Add Events PRO to the list of add-ons to check required version.
	 *
	 * @return array $plugins the required info
	 */
	function tribe_init_ecp_addon( $plugins ) {
		$plugins['TribeEventsPro'] = array(
			'plugin_name'      => 'Events Calendar PRO',
			'required_version' => TribeEventsPro::REQUIRED_TEC_VERSION,
			'current_version'  => TribeEventsPro::VERSION,
			'plugin_dir_file'  => basename( dirname( __FILE__ ) ) . '/events-calendar-pro.php'
		);

		return $plugins;
	}

	register_activation_hook( __FILE__, 'tribe_ecp_activate' );
	register_deactivation_hook( __FILE__, 'tribe_ecp_deactivate' );
	register_uninstall_hook( __FILE__, 'tribe_ecp_uninstall' );

	function tribe_ecp_activate() {
		flush_rewrite_rules();
		if ( function_exists( 'tribe_update_option' ) ) {
			tribe_update_option( 'defaultValueReplace', get_option('ecp_defaultValueReplace_prev') );
			delete_option('ecp_defaultValueReplace_prev');
		} else {
			if (is_array(get_option('tribe_events_calendar_options'))) {
				$tec_options = get_option('tribe_events_calendar_options');
				$tec_options['defaultValueReplace'] = get_option('ecp_defaultValueReplace_prev');
				update_option('tribe_events_calendar_options', $tec_options);
				delete_option('ecp_defaultValueReplace_prev');
			}
		}
	}

	// when we deactivate pro, we should reset some options
	function tribe_ecp_deactivate() {
		if ( function_exists( 'tribe_update_option' ) ) {
			update_option('ecp_defaultValueReplace_prev', tribe_get_option('defaultValueReplace'));
			tribe_update_option( 'defaultValueReplace', false );
		} else {
			if (is_array(get_option('tribe_events_calendar_options'))) {
				$tec_options = get_option('tribe_events_calendar_options');
				if ( array_key_exists('defaultValueReplace', $tec_options) ) {
					update_option('ecp_defaultValueReplace_prev', $tec_options['defaultValueReplace']);
					$tec_options['defaultValueReplace'] = false;
					update_option('tribe_events_calendar_options', $tec_options);
		}
	}
		}
	}

	function tribe_ecp_uninstall() {
	}

	require_once( dirname( __FILE__ ) . '/lib/tribe-events-pro-pue.class.php' );
	new TribeEventsProPUE( __FILE__ );
} // end if Class exists
