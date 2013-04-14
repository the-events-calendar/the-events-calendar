<?php
/**
 * @for Events List Template
 * This file contains the hook logic required to create an effective event list view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_List_Template')){
	class Tribe_Events_List_Template extends Tribe_Template_Factory {

		private $first = true;
		static $loop_increment = 0;
		static $prev_event_month = null;
		static $prev_event_year = null;

		public static function init(){

			Tribe_Template_Factory::asset_package( 'ajax-list' );

			// customize meta items
			tribe_set_the_meta_template( 'tribe_event_venue_name', array(
				'before'=>'',
				'after'=>'',
				'label_before'=>'',
				'label_after'=>'',
				'meta_before'=>'<span class="%s">',
				'meta_after'=>'</span>'
			));
			tribe_set_meta_label( 'tribe_event_venue_address', '' );
			tribe_set_the_meta_template( 'tribe_event_venue_address', array(
				'before'=>'',
				'after'=>'',
				'label_before'=>'',
				'label_after'=>'',
				'meta_before'=>'',
				'meta_after'=>''
			));
			tribe_set_the_meta_visibility( 'tribe_event_venue_gmap_link', false );

			global $wp_query;

			// Look for a search query
			if( !empty( $wp_query->query_vars['s'] )){
				$search_term = $wp_query->query_vars['s'];
			} else if( !empty($_POST['tribe-bar-search'])) {
				$search_term = $_POST['tribe-bar-search'];
			}

			// Search term based notices
			if( !empty($search_term) && !have_posts() ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There  were no results found for <strong>"%s"</strong>.', 'tribe-events-calendar' ), $search_term ) );
			}

			// Our various messages if there are no events for the query
			else if ( empty($search_term) && empty( $wp_query->query_vars['s'] ) && !have_posts() ) { // Messages if currently no events, and no search term
				$tribe_ecp = TribeEvents::instance();
				$is_cat_message = '';
				if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
					$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
					if( tribe_is_upcoming() ) {
						$is_cat_message = sprintf( __( 'listed under %s. Check out past events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
					} else if( tribe_is_past() ) {
						$is_cat_message = sprintf( __( 'listed under %s. Check out upcoming events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
					}
				}
				if( tribe_is_day() ) {						
					TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No events scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar' ), date_i18n( 'F d, Y', strtotime( get_query_var( 'eventDate' ) ) ) ) );
				} elseif( tribe_is_upcoming() ) {
					$date = date('Y-m-d', strtotime($tribe_ecp->date));
					if ( $date == date('Y-m-d') ) {
						TribeEvents::setNotice( 'events-not-found', __('No upcoming events ', 'tribe-events-calendar') . $is_cat_message );
					} else {
						TribeEvents::setNotice( 'events-not-found', __('No matching events ', 'tribe-events-calendar') . $is_cat_message );
					}
				} elseif( tribe_is_past() ) {
					TribeEvents::setNotice( 'events-past-not-found', __('No previous events ', 'tribe-events-calendar') . $is_cat_message );
				}
			}

			if ( ! defined('DOING_AJAX') || ! DOING_AJAX) { // ajax requests handle the query separately
				// Hijack global WP_Query
				add_action( 'tribe_events_list_before_template', array(__CLASS__, 'setup_events_query') );

				// Reset global WP_Query
				add_action( 'tribe_events_list_after_template', 'wp_reset_query' );
			}

			// Set excerpt length & more
			add_action( 'tribe_events_list_before_loop', array(__CLASS__, 'setup_excerpt') );

			// Reset excerpt length & more
			add_action( 'tribe_events_list_after_loop', array(__CLASS__, 'reset_excerpt') );
			
			// Event cost
			add_filter( 'tribe_events_list_the_event_cost', array( __CLASS__, 'the_event_cost' ), 1, 2 );

			do_action('tribe_events_list_template_init');
		}

		/**
		 * Hijack the global $wp_query and replace it with the upcoming events query
		 *
		 * @return void
		 * @since 
		 **/
		public static function setup_events_query() {

			global $wp_query;
			$args = NULL;
			if ( empty($wp_query->query['eventDisplay']) || $wp_query->query['eventDisplay'] != 'upcoming' ) {
				$args = wp_parse_args(array( 'eventDisplay' => 'upcoming' ), $wp_query->query);
			}

			// hijack the main query to load the events via provided $args
			if ( !is_null( $args ) || ! ( $wp_query->tribe_is_event || $wp_query->tribe_is_event_category ) ) {
				$wp_query = TribeEventsQuery::getEvents( $args, true );
			}
	
			// single-event notices are jumping in on this init when loading as a module
			TribeEvents::removeNotice( 'event-past' );
		}

		/**
		 * Set up excerpt to how we like it in list view
		 *
		 * @return void
		 * @since 3.0
		 **/
		public static function setup_excerpt() {
			add_filter( 'excerpt_length', array(__CLASS__, 'excerpt_length') );
			add_filter( 'excerpt_more', array(__CLASS__, 'excerpt_more') );
		}

		/**
		 * Reset excerpt to how it used to be
		 *
		 * @return void
		 * @since 3.0
		 **/
		public static function reset_excerpt() {
			remove_filter( 'excerpt_length', array(__CLASS__, 'excerpt_length') );
			remove_filter( 'excerpt_more', array(__CLASS__, 'excerpt_more') );
		}

		/**
		 * Set excerpt length used in list view
		 *
		 * @return void
		 * @since 
		 **/
		public static function excerpt_length() {
			return 30;
		}

		/**
		 * Set excerpt more used in list view
		 *
		 * @return void
		 * @since 
		 **/
		public static function excerpt_more() {
			return '&hellip;';
		}

		public static function the_event_cost( $content, $post_id ) {
			$html= '';
			if ( tribe_get_cost() ) // Get our event cost 
				$html .= '<div class="tribe-events-event-cost"><span>'. tribe_get_cost( null, true ) .'</span></div>';			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_event_cost');		
		}		
	}
	Tribe_Events_List_Template::init();
}
