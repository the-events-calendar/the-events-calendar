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
		static $loop_increment = 0;

		public static function init(){

			// override list methods
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template'), 20, 1);
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop'), 20, 1);
			add_filter( 'tribe_events_list_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 20, 1 );

			// remove list pagination
			remove_filter( 'tribe_events_list_before_pagination', array( 'Tribe_Events_List_Template', 'before_pagination' ), 20 );
			remove_filter( 'tribe_events_list_pagination', array( 'Tribe_Events_List_Template', 'pagination' ), 20 );
			remove_filter( 'tribe_events_list_after_pagination', array( 'Tribe_Events_List_Template', 'after_pagination' ), 20 );

		}

		// Start List Template
		public function before_template( $pass_through ){
			// This title is here for ajax loading – do not remove if you want ajax switching between month views
			/*
			ob_start(); ?>
			<div id="tribe-events-content" class="tribe-events-day-grid">
				<!--
					@Tim
					I noticed when using the navigation that the url seems to get updated, but not the events or
					or events page date. Looks like maybe the ajax is busted, b/c when I refresh on a given dates page,
					then the title updated to the correct day date.
				-->
				<title><?php wp_title(); ?></title>
			<?php
			$html = ob_get_clean();
			 */
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

			$current_day = $wp_query->get('start_date');
			$yesterday = Date('Y-m-d', strtotime($current_day . " -1 day") );
			$tomorrow = Date('Y-m-d', strtotime($current_day . " +1 day") );

			// Display Day Navigation
			// <-- Previous Day Next Day -->
			$html = sprintf('<div id="tribe-events-header" data-title="%s" data-header="%s"><h3 class="tribe-events-visuallyhidden">%s</h3><ul class="tribe-events-sub-nav"><li class="tribe-events-nav-prev"><a href="%s" data-day="%s" rel="prev">&#x2190; %s</a></li><li class="tribe-events-nav-next"><a href="%s" data-day="%s" rel="next">%s &#x2192;</a><img src="%s" class="ajax-loading" id="ajax-loading" alt="Loading events" /></li></ul></div>',
								wp_title( '&raquo;', false ),
								Date("l, F jS Y", strtotime($wp_query->get('start_date'))),
								__( 'Day Navigation', 'tribe-events-calendar' ),
								tribe_get_day_permalink( $yesterday ),
								$yesterday,
								__( 'Yesterday', 'tribe-events-calendar-pro' ),
								tribe_get_day_permalink( $tomorrow ),
								$tomorrow,
								__( 'Tomorrow', 'tribe-events-calendar-pro' ),
								esc_url( admin_url( 'images/wpspin_light.gif' ) )
								);
			return apply_filters('tribe_template_factory_debug', $pass_through . $html, 'tribe_events_day_before_template');
		}

		public function inside_before_loop( $pass_through ){
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
				$html .= ( self::$loop_increment > 0 ) ? '</div>' : '';

				// open new time block & time vs all day header
				$html .= sprintf( '<div class="tribe-events-day-time-slot"><h5>%s</h5>', $start_time );

			}
			return apply_filters('tribe_template_factory_debug', $html . $pass_through, 'tribe_events_day_inside_before_loop');
		}

		public function inside_after_loop( $pass_through ){
			global $wp_query;

			// close out the last time block
			$html = ( self::$loop_increment == count($wp_query->posts)-1 ) ? '</div>' : '';

			// internal increment to keep track of position within the loop
			self::$loop_increment++;

			return apply_filters('tribe_template_factory_debug', $pass_through . $html, 'tribe_events_day_inside_after_loop');
		}
	}
	Tribe_Events_Day_Template::init();
}