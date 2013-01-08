<?php
/**
 * @for Map Template
 * This file contains the hook logic required to create an effective map view.
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Map_Template')){
	class Tribe_Events_Map_Template extends Tribe_Template_Factory {
		public static function init(){

			// Start map template
			add_filter( 'tribe_events_map_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// Map
			add_filter( 'tribe_events_map_before_the_map', array( __CLASS__, 'before_the_map' ), 1, 1 );
			add_filter( 'tribe_events_map_the_map', array( __CLASS__, 'the_map' ), 1, 1 );
			add_filter( 'tribe_events_map_after_the_map', array( __CLASS__, 'after_the_map' ), 1, 1 );
	
			// Options
			add_filter( 'tribe_events_map_before_the_options', array( __CLASS__, 'before_the_options' ), 1, 1 );
			add_filter( 'tribe_events_map_the_options', array( __CLASS__, 'the_options' ), 1, 1 );
			add_filter( 'tribe_events_map_after_the_options', array( __CLASS__, 'after_the_options' ), 1, 1 );
	
//			// Results
			add_filter( 'tribe_events_map_before_the_results', array( __CLASS__, 'before_the_results' ), 1, 1 );
			add_filter( 'tribe_events_map_after_the_results', array( __CLASS__, 'after_the_results' ), 1, 1 );

//			// Pagination
			add_filter( 'tribe_events_map_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_map_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_map_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );


			// End map template
			add_filter( 'tribe_events_map_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
						
		}
		// Start Map Template
		public static function before_template( $post_id ){
			$html = '<div id="tribe-geo-wrapper">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_template');
		}
		// Map
		public static function before_the_map( $post_id ){
			$html = '<div id="tribe-geo-map-wrapper">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_map');
		}
		public static function the_map( $post_id ){
			$html = '<div id="tribe-geo-loading"><span class="tribe-spinner"></span></div>';
			$html .= '<div id="tribe-geo-map"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_the_map');
		}
		public static function after_the_map( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_the_map');
		}
		// Options
		public static function before_the_options( $post_id ){
			$html = '<div id="tribe-geo-options">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_options');
		}
		public static function the_options( $post_id ){
			$html = '<h2>'. __( 'Refine your search:', 'tribe-events-calendar-pro' ) .'</h2>';
			$html .= '<div id="tribe-geo-links"></div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_the_options');
		}
		public static function after_the_options( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_the_options');
		}
		// Start Results
		public static function before_the_results( $post_id ){
			$html = '<div id="tribe-geo-results">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_results');
		}		
		// Pagination
		public static function before_pagination( $post_id ){
			$html = '<div class="tribe-events-loop-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_pagination');
		}
		public static function pagination( $post_id ){
			$html = '<a href="#" id="tribe_map_paged_prev" class="tribe_map_paged">'. __('<< Previous Events') .'</a>';
			$html .= '<a href="#" id="tribe_map_paged_next" class="tribe_map_paged">'. __('Next Events >>') .'</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_pagination');
		}
		public static function after_pagination( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_pagination');
		}
		// End Results
		public static function after_the_results( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_the_results');		
		}
		// End Map Template
		public static function after_template( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_template');		
		}
	}
	Tribe_Events_Map_Template::init();
}