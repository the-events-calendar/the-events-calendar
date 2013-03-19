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
	
			// Results
			add_filter( 'tribe_events_map_before_the_results', array( __CLASS__, 'before_the_results' ), 1, 1 );
			add_filter( 'tribe_events_map_after_the_results', array( __CLASS__, 'after_the_results' ), 1, 1 );
			
			// Map header
			add_filter( 'tribe_events_map_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_map_before_header_nav', array( __CLASS__, 'before_header_nav' ), 1, 1 );
			add_filter( 'tribe_events_map_header_nav', array( __CLASS__, 'header_navigation' ), 1, 1 );
			add_filter( 'tribe_events_map_after_header_nav', array( __CLASS__, 'after_header_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_map_after_header', array( __CLASS__, 'after_header' ), 1, 1 );
			
			// Map footer
			add_filter( 'tribe_events_map_before_footer', array( __CLASS__, 'before_footer' ), 1, 1 );
			
			// Navigation
			add_filter( 'tribe_events_map_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 1, 1 );
			add_filter( 'tribe_events_map_footer_nav', array( __CLASS__, 'footer_navigation' ), 1, 1 );
			add_filter( 'tribe_events_map_after_footer_nav', array( __CLASS__, 'after_footer_nav' ), 1, 1 );
			
			add_filter( 'tribe_events_map_after_footer', array( __CLASS__, 'after_footer' ), 1, 1 );

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
			$html = '<div id="tribe-geo-loading"><img class="tribe-ajax-loading tribe-spinner" src="'. trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" /></div>';
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
		// Map Header
		public static function before_header( $post_id ){
			$html = '<div id="tribe-events-header">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_header');
		}
		// Map Navigation
		public static function before_header_nav( $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Events Map Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_header_nav');
		}
		public static function header_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous"><a href="#" class="tribe_map_paged">'. __('&larr; Previous Events') .'</a></li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			$html .= '<a href="#" class="tribe_map_paged">'. __('Next Events &rarr;') .'</a>';
			
			// Loading spinner
			$html .= '<img class="tribe-ajax-loading tribe-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_header_nav');
		}
		public static function after_header_nav( $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_header_nav');
		}
		public static function after_header( $post_id ){
			$html = '</div><!-- #tribe-events-header -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_header');
		}
		// Start Results
		public static function before_the_results( $post_id ){
			$html = '<div id="tribe-geo-results">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_results');
		}
		// Map Footer
		public static function before_footer( $post_id ){
			$html = '<div id="tribe-events-footer">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_footer');
		}
		// Map Navigation
		public static function before_footer_nav( $post_id ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Events Map Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_footer_nav');
		}
		public static function footer_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous"><a href="#" class="tribe_map_paged">'. __('&larr; Previous Events') .'</a></li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			$html .= '<a href="#" class="tribe_map_paged">'. __('Next Events &rarr;') .'</a>';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_footer_nav');
		}
		public static function after_footer_nav( $post_id ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_footer_nav');
		}
		public static function after_footer( $post_id ){
			$html = '</div><!-- #tribe-events-footer -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_footer');
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