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

			add_filter( 'tribe_events_list_show_separators', "__return_false" );
			remove_filter( 'tribe_events_list_before_template' , array( 'TribeEventsBar', 'show' ), 30 );

			// Start map template
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 20, 1 );
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'the_map' ), 20, 1 );
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'the_options' ), 20, 1 );
	
			// Results
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_the_results' ), 20, 1 );

			// Navigation
			add_filter( 'tribe_events_list_before_header_nav', array( __CLASS__, 'before_header_nav' ), 20, 1 );
			add_filter( 'tribe_events_list_header_nav', array( __CLASS__, 'header_navigation' ), 20, 1 );
			
			// Navigation
			add_filter( 'tribe_events_map_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 20, 1 );
			add_filter( 'tribe_events_map_footer_nav', array( __CLASS__, 'footer_navigation' ), 20, 1 );
			add_filter( 'tribe_events_map_after_footer_nav', array( __CLASS__, 'after_footer_nav' ), 20, 1 );

		}
		// Start Map Template
		public static function before_template( $html ){
			$html = '<div id="tribe-geo-wrapper">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_template');
		}
		// Map
		public static function the_map( $html ){
			$html .= '<div id="tribe-geo-map-wrapper">';
			$html .= '<div id="tribe-geo-loading"><img class="tribe-ajax-loading tribe-spinner" src="'. trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" /></div>';
			$html .= '<div id="tribe-geo-map"></div>';
			$html .= '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_the_map');
		}
		// Options
		public static function the_options( $html ){
			$html .= '<div id="tribe-geo-options">';
			$html .= '<h2>'. __( 'Refine your search:', 'tribe-events-calendar-pro' ) .'</h2>';
			$html .= '<div id="tribe-geo-links"></div>';
			$html .= '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_the_options');
		}

		// Map Navigation
		public static function before_header_nav( $html ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Events Map Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_header_nav');
		}
		public static function header_navigation( $post_id ){
			$tribe_ecp = TribeEvents::instance();

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous"><a href="#" class="tribe_map_paged">'. __('&laquo; Previous Events') .'</a></li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			$html .= '<a href="#" class="tribe_map_paged">'. __('Next Events &raquo;') .'</a>';
			
			// Loading spinner
			$html .= '<img class="tribe-ajax-loading tribe-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_header_nav');
		}

		// Start Results
		public static function before_the_results( $html ){
			$html = '<div id="tribe-geo-results">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_results');
		}

		// Map Navigation
		public static function before_footer_nav( $html ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Events Map Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_footer_nav');
		}
		public static function footer_navigation( $html ){
			$tribe_ecp = TribeEvents::instance();

			// Display Previous Page Navigation
			$html = '<li class="tribe-nav-previous"><a href="#" class="tribe_map_paged">'. __('&laquo; Previous Events') .'</a></li><!-- .tribe-nav-previous -->';
			
			// Display Next Page Navigation
			$html .= '<li class="tribe-nav-next">';
			$html .= '<a href="#" class="tribe_map_paged">'. __('Next Events &raquo;') .'</a>';
			$html .= '</li><!-- .tribe-nav-next -->';
			
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_footer_nav');
		}
		public static function after_footer_nav( $html ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_footer_nav');
		}

		// End Results
		public static function after_the_results( $html ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_the_results');		
		}

		public static function after_template( $html ){
			$html .= '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_after_template');			
		}

	}
	Tribe_Events_Map_Template::init();
}