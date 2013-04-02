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


			// prevent these filters from running during ajax
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 20, 1 );
				add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'the_map' ), 20, 1 );
				add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_options' ), 20, 1 );
				add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title' ), 20, 2 );
			}

			add_filter( 'tribe_events_list_before_header', array( __CLASS__, 'before_header' ), 20, 1 );

			// Results
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_the_results' ), 20, 1 );

			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				add_filter( 'tribe_events_list_after_template', array( __CLASS__, 'after_template' ), 20, 3 );
			}

			// Navigation
			add_filter( 'tribe_events_list_before_header_nav', array( __CLASS__, 'before_header_nav' ), 20, 1 );
			add_filter( 'tribe_events_list_header_nav', array( __CLASS__, 'header_navigation' ), 20, 1 );

			// Navigation
			add_filter( 'tribe_events_list_before_footer_nav', array( __CLASS__, 'before_footer_nav' ), 20, 1 );
			add_filter( 'tribe_events_list_footer_nav', array( __CLASS__, 'footer_navigation' ), 20, 1 );
			add_filter( 'tribe_events_list_after_footer_nav', array( __CLASS__, 'after_footer_nav' ), 20, 1 );

		}
		// Start Map Template
		public static function before_template( $html ){
			$html = '<div id="tribe-geo-wrapper">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_template');
		}
		// Map
		public static function the_map( $html ){
			$html .= '<div id="tribe-geo-map-wrapper">';
			$html .= '<div id="tribe-geo-loading"><img class="tribe-events-ajax-loading tribe-spinner" src="'. trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" /></div>';
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

		public static function before_header( $html ){
			$html = '<div id="tribe-events-header" data-view="map" data-title="' . wp_title( '&raquo;', false ) . '">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_header');
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
			$html = '<li class="tribe-events-nav-previous"><a href="#" class="tribe_map_paged">'. __('&laquo; Previous Events') .'</a></li><!-- .tribe-events-nav-previous -->';

			// Display Next Page Navigation
			$html .= '<li class="tribe-events-nav-next">';
			$html .= '<a href="#" class="tribe_map_paged">'. __('Next Events &raquo;') .'</a>';

			// Loading spinner
			$html .= '<img class="tribe-events-ajax-loading tribe-events-spinner-medium" src="'. trailingslashit( $tribe_ecp->pluginUrl ) . 'resources/images/tribe-loading.gif" alt="Loading Events" />';
			$html .= '</li><!-- .tribe-events-nav-next -->';

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_header_nav');
		}

		public static function the_title( $html ){
			global $wp_query;
			$html .= '<div id="tribe-events-geo-content">';
			$html .= sprintf( '<h2 class="tribe-events-page-title">%s</h2>',
				tribe_get_events_title()
				);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_title');
		}

		// Start Results
		public static function before_the_results( $html ){
			$html = '<div id="tribe-geo-results" class="tribe-events-loop">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_results');
		}

		// Map Navigation
		public static function before_footer_nav( $html ){
			$html = '<h3 class="tribe-events-visuallyhidden">'. __( 'Events Map Navigation', 'tribe-events-calendar' ) .'</h3>';
			$html .= '<ul class="tribe-events-sub-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_before_footer_nav');
		}
		public static function footer_navigation( $html ){

			// Display Previous Page Navigation
			$html = '<li class="tribe-events-nav-previous"><a href="#" class="tribe_map_paged">'. __('&laquo; Previous Events') .'</a></li><!-- .tribe-events-nav-previous -->';

			// Display Next Page Navigation
			$html .= '<li class="tribe-events-nav-next">';
			$html .= '<a href="#" class="tribe_map_paged">'. __('Next Events &raquo;') .'</a>';
			$html .= '</li><!-- .tribe-events-nav-next -->';

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_footer_nav');
		}
		public static function after_footer_nav( $html ){
			$html = '</ul><!-- .tribe-events-sub-nav -->';
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				$html .= '</div><!-- #tribe-events-geo-content -->';
			}
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_footer_nav');
		}

		// End List Template
		public static function after_template( $html, $has_posts, $post_id ){
			$html = '</div><!-- #tribe-geo-wrapper -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_template');		
		}

	}
	Tribe_Events_Map_Template::init();
}
