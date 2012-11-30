<?php
/**
 * @for Photo Template
 * This file contains the hook logic required to create an effective day grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1');
}


if( !class_exists('Tribe_Events_Day_Template')){
	class Tribe_Events_Day_Template extends Tribe_Template_Factory {

		static $timeslots = array();

		public static function init(){

			Tribe_PRO_Template_Factory::asset_package('isotope');
			wp_enqueue_script('tribe-events-pro-photo', trailingslashit( TribeEventsPro::instance()->pluginUrl ) . 'resources/' .  'tribe-events-photo-view.js', array('jquery'));

			add_filter( 'tribe_events_list_show_separators', '__return_false' );

			// override list methods
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_loop'), 20, 1);
			add_filter( 'tribe_events_list_inside_before_loop', array( __CLASS__, 'inside_before_loop'), 20, 1);
			add_filter( 'tribe_events_list_the_content', array( __CLASS__, 'the_content'), 20, 1);

		}

		public static function before_loop( $pass_through ){
			$html = '<div class="tribe-events-loop hfeed" id="tribe-events-photo-events">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_photo_before_loop');
		}

		public static function inside_before_loop( $pass_through ){
			$post_id = get_the_ID();
			// Get our wrapper classes (for event categories, organizer, venue, and defaults)
			$tribe_string_classes = '';
			$tribe_cat_ids = tribe_get_event_cat_ids( $post_id ); 
			foreach( $tribe_cat_ids as $tribe_cat_id ) { 
				$tribe_string_classes .= 'tribe-events-category-'. $tribe_cat_id .' '; 
			}
			$tribe_string_wp_classes = '';
			$allClasses = get_post_class(); 
			foreach ($allClasses as $class) { 
				$tribe_string_wp_classes .= $class . ' '; 
			}
			$tribe_classes_default = 'hentry vevent '. $tribe_string_wp_classes;
			$tribe_classes_venue = tribe_get_venue_id() ? 'tribe-events-venue-'. tribe_get_venue_id() : '';
			$tribe_classes_organizer = tribe_get_organizer_id() ? 'tribe-events-organizer-'. tribe_get_organizer_id() : '';
			$tribe_classes_categories = $tribe_string_classes;
			$class_string = $tribe_classes_default .' '. $tribe_classes_venue .' '. $tribe_classes_organizer .' '. $tribe_classes_categories;		
			$html = '<div id="post-'. $post_id .'" class="'. $class_string .' tribe-events-photo-event tribe-clearfix">';
			return apply_filters('tribe_template_factory_debug', $html , 'tribe_events_day_inside_before_loop');
		}

		public static function the_content( $post_id ){
			$html = '';
			if (has_excerpt())
				$html .= '<p>'. get_the_excerpt() .'</p>';
			else
				$html .= '<p>'. TribeEvents::truncate(get_the_content(), 20) .'</p>';	
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_photo_the_content');
		}

	}
	Tribe_Events_Day_Template::init();
}
