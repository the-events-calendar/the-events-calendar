<?php
/**
 * @for Day Template
 * This file contains the hook logic required to create an effective day grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }


if( !class_exists('Tribe_Events_Day_Template')){
	class Tribe_Events_Day_Template extends Tribe_Template_Factory {

		static $timeslots = array();

		public static function init(){

			// override list methods
			add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title'), 20, 1);
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop'), 20, 1);
			add_filter( 'tribe_events_list_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 20, 1 );

			// remove list pagination
			remove_filter( 'tribe_events_list_before_pagination', array( 'Tribe_Events_List_Template', 'before_pagination' ), 20 );
			remove_filter( 'tribe_events_list_pagination', array( 'Tribe_Events_List_Template', 'pagination' ), 20 );
			remove_filter( 'tribe_events_list_after_pagination', array( 'Tribe_Events_List_Template', 'after_pagination' ), 20 );

			// remove month/year separators
			add_filter( 'tribe_events_list_show_separators', '__return_false' );
			
		}

		// Start List Template
		public static function the_title( $pass_through ){
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

			$current_day = $wp_query->get('start_date');
			$yesterday = Date('Y-m-d', strtotime($current_day . " -1 day") );
			$tomorrow = Date('Y-m-d', strtotime($current_day . " +1 day") );

			// Display Day Navigation
			// <-- Previous Day Next Day -->
			$html = sprintf('<div id="tribe-events-header" data-date="%s" data-title="%s" data-header="%s"><h3 class="tribe-events-visuallyhidden">%s</h3><ul class="tribe-events-sub-nav"><li class="tribe-events-nav-prev"><a href="%s" data-day="%s" rel="prev">&#x2190; %s</a></li><li class="tribe-events-nav-next"><a href="%s" data-day="%s" rel="next">%s &#x2192;</a><span class="tribe-spinner-medium" id="ajax-loading"><span class="tribe-events-visuallyhidden">Loading Events</span></span></li></ul></div>',
								Date('Y-m-d', strtotime($current_day) ),
								wp_title( '&raquo;', false ),
								Date("l, F jS Y", strtotime($wp_query->get('start_date'))),
								__( 'Day Navigation', 'tribe-events-calendar' ),
								tribe_get_day_permalink( $yesterday ),
								$yesterday,
								__( 'Yesterday', 'tribe-events-calendar-pro' ),
								tribe_get_day_permalink( $tomorrow ),
								$tomorrow,
								__( 'Tomorrow', 'tribe-events-calendar-pro' )
								);
			return apply_filters('tribe_template_factory_debug', $pass_through . $html, 'tribe_events_day_the_title');
		}

		public static function inside_before_loop( $pass_through ){
			global $post;

			$html = '';

			// setup the "start time" for the event header
			$start_time = ( $post->tribe_is_allday ) ? 
				__( 'All Day', 'tribe-events-calendar' ) :
				tribe_get_start_date( null, false, 'ga ' );

			// determine if we want to open up a new time block
			if( ! in_array( $start_time, self::$timeslots ) ) {

				self::$timeslots[] = $start_time;	

				// close out any prior opened time blocks
				$html .= ( Tribe_Events_List_Template::$loop_increment > 0 ) ? '</div>' : '';

				// open new time block & time vs all day header
				$html .= sprintf( '<div class="tribe-events-day-time-slot"><h5>%s</h5>', $start_time );

			}
			return apply_filters('tribe_template_factory_debug', $html . $pass_through, 'tribe_events_day_inside_before_loop');
		}

		public static function inside_after_loop( $pass_through ){
			global $wp_query;

			// close out the last time block
			$html = ( Tribe_Events_List_Template::$loop_increment == count($wp_query->posts)-1 ) ? '</div>' : '';

			return apply_filters('tribe_template_factory_debug', $pass_through . $html, 'tribe_events_day_inside_after_loop');
		}
	}
	Tribe_Events_Day_Template::init();
}
