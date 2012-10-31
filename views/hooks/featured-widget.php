<?php
/**
 * @for Events Pro Featured Widget Template
 * This file contains the hook logic required to create an effective featured widget view.
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue, 
 * $address, $city, $state, $province'], $zip, $country, $phone, $cost
 *
 * @return string
 *
 * @package TribeEventsCalendarPro
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */ 

// Vars set:
// '$event->AllDay',
// '$event->StartDate',
// '$event->EndDate',
// '$event->ShowMapLink',
// '$event->ShowMap',
// '$event->Cost',
// '$event->Phone',

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Pro_featured_widget_Template')){
	class Tribe_Events_Pro_featured_widget_Template extends Tribe_Template_Factory {
		public static function init(){
			// Start featured widget template
			add_filter( 'tribe_events_pro_featured_widget_before_template', array( __CLASS__, 'before_template' ), 1, 1 );
	
			// Start single event
			add_filter( 'tribe_events_pro_featured_widget_before_the_event', array( __CLASS__, 'before_the_event' ), 1, 1 );
	
			// Event dates
			add_filter( 'tribe_events_pro_featured_widget_before_the_date', array( __CLASS__, 'before_the_date' ), 1, 1 );
			add_filter( 'tribe_events_pro_featured_widget_the_date', array( __CLASS__, 'the_date' ), 1, 2 );
			add_filter( 'tribe_events_pro_featured_widget_after_the_date', array( __CLASS__, 'after_the_date' ), 1, 1 );

			// Event title
			add_filter( 'tribe_events_pro_featured_widget_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_pro_featured_widget_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			add_filter( 'tribe_events_pro_featured_widget_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );
			
			// Event location
			add_filter( 'tribe_events_pro_featured_widget_before_the_location', array( __CLASS__, 'before_the_location' ), 1, 1 );
			add_filter( 'tribe_events_pro_featured_widget_the_location', array( __CLASS__, 'the_location' ), 1, 1 );
			add_filter( 'tribe_events_pro_featured_widget_after_the_location', array( __CLASS__, 'after_the_location' ), 1, 1 );
			
			// Event content
			add_filter( 'tribe_events_pro_featured_widget_before_the_location', array( __CLASS__, 'before_the_location' ), 1, 1 );
			add_filter( 'tribe_events_pro_featured_widget_the_location', array( __CLASS__, 'the_location' ), 1, 1 );
			add_filter( 'tribe_events_pro_featured_widget_after_the_location', array( __CLASS__, 'after_the_location' ), 1, 1 );
	
			// End single event
			add_filter( 'tribe_events_pro_featured_widget_after_the_event', array( __CLASS__, 'after_the_event' ), 1, 1 );

			// End featured widget template
			add_filter( 'tribe_events_pro_featured_widget_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start Featured Widget Template
		public function before_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_before_template');
		}
		// Start Single Event
		public function before_the_event( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_before_the_event');
		}
		// Event Title
		public function before_the_title( $post_id ){
			$html = '<div class="event">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_before_the_title');
		}
		public function the_title( $post ){
			$html = '<a href="'. get_permalink( $post->ID ) .'">'. $post->post_title .'</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_the_title');
		}
		public function after_the_title( $post_id ){
			$html = '</div><!-- .event -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_after_the_title');
		}
		// Event Dates	
		public function before_the_date( $post_id ){
			$html = '<div class="when">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_before_the_date');
		}
		public function the_date( $post_id, $event ){
			$start = isset( $start ) ? $start : null;
			$html = tribe_get_start_date( $post_id, $start );
			if($event->AllDay)
				$html .= ' <small><em>('. __( 'All Day', 'tribe-events-calendar' ) .')</em></small>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_the_date');
		}
		public function after_the_date( $post_id ){
			$html = '</div><!-- .when -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_after_the_date');
		}
		// Event Location
		public function before_the_location( $post_id ){
			$html = '<div class="loc">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_before_the_location');
		}
		public function the_location( $post_id ){
			ob_start();

			if ( tribe_get_city() != '' ) {
				echo tribe_get_city() . ', ';
			}
			if ( tribe_get_region() != '' ) {
				echo tribe_get_region() . ', '; 
			}
			if ( tribe_get_country() != '' ) {
				echo tribe_get_country(); 
			}			
		
			$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_the_location');
		}
		public function after_the_location( $post_id ){
			$html = '</div><!-- .loc -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_after_the_location');
		}
		// Event Content	
		public function before_the_content( $post_id ){
			$html = '<div class="event_body">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_before_the_content');
		}
		public function the_content( $post_id ){
			ob_start();
			
			$content = apply_filters( 'the_content', strip_shortcodes( $post->post_content ) );
	    	$content = str_replace( ']]>', ']]&gt;', $content );
	    	echo wp_trim_words( $content, apply_filters( 'excerpt_length' ), apply_filters( 'excerpt_more' ) );
	    	
	    	$html = ob_get_clean();
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_the_content');
		}
		public function after_the_content( $post_id ){
			$html = '</div><!-- .event_body -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_after_the_content');
		}
		// End Single Event
		public function after_the_event( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_after_the_event');
		}
		
		// End Featured Widget Template
		public function after_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_pro_featured_widget_after_template');		
		}
	}
	Tribe_Events_Pro_featured_widget_Template::init();
}