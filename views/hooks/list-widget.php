<?php
/**
 * @for Events List Widget Template
 * This file contains the hook logic required to create an effective events list widget view.
 *
 * @return string
 *
 * @package TribeEventsCalendar
 * @since  2.1
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

if( !class_exists('Tribe_Events_List_Widget_Template')){
	class Tribe_Events_List_Widget_Template extends Tribe_Template_Factory {
		function init(){
			// start list widget template
			add_filter( 'tribe_events_list_widget_before_template', array( __CLASS__, 'before_template' ), 1, 1 );
	
			// start single event
			add_filter( 'tribe_events_list_widget_before_the_event', array( __CLASS__, 'before_the_event' ), 1, 1 );
	
			// event dates
			add_filter( 'tribe_events_list_widget_before_the_date', array( __CLASS__, 'before_the_date' ), 1, 1 );
			add_filter( 'tribe_events_list_widget_the_date', array( __CLASS__, 'the_date' ), 1, 1 );
			add_filter( 'tribe_events_list_widget_after_the_date', array( __CLASS__, 'after_the_date' ), 1, 1 );

			// event title
			add_filter( 'tribe_events_list_widget_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_list_widget_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			add_filter( 'tribe_events_list_widget_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );
	
			// end single event
			add_filter( 'tribe_events_list_widget_after_the_event', array( __CLASS__, 'after_the_event' ), 1, 1 );

			// end list widget template
			add_filter( 'tribe_events_list_widget_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Widget Template
		public function before_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_before_template');
		}
		// Start Single Event
		public function before_the_event( $post_id ){
			$html = '<li '. $class .'>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_before_the_event');
		}
		// Event Dates	
		public function before_the_date( $post_id ){
			$html = '<div class="when">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_before_the_date');
		}
		public function the_date( $post_id ){
			$space = false;
			$output = '';
			
			$html = tribe_get_start_date( $post->ID );
			if(tribe_is_multiday($post->ID))
				$html .= '<br/>'. __( 'Ends', 'tribe-events-calendar-pro') .' ';
				$html .= tribe_get_end_date( $post->ID );
			if($event->AllDay)
				$html .= ' <small><em>('. __( 'All Day', 'tribe-events-calendar' ) .')</em></small>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_the_date');
		}
		public function after_the_date( $post_id ){
			$html = '</div><!-- .when -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_after_the_date');
		}
		// Event Title
		public function before_the_title( $post_id ){
			$html = '<div class="event">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_before_the_title');
		}
		public function the_title( $post_id ){
			$html = '<a href="'. get_permalink( $post->ID ) .'">'. $post->post_title .'</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_the_title');
		}
		public function after_the_title( $post_id ){
			$html = '</div><!-- .event -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_after_the_title');
		}
		// End Single Event
		public function after_the_event( $post_id ){
			$html = '</li>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_after_the_event');
		}
		
		$alt_text = ( empty( $alt_text ) ) ? 'alt' : '';
		
		// End List Widget Template
		public function after_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_after_template');		
		}
	}
	Tribe_Events_List_Widget_Template::init();
}