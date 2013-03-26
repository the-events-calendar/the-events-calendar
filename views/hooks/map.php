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

			// Start map template
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'before_template' ), 20, 1 );
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'the_map' ), 20, 1 );
			add_filter( 'tribe_events_list_before_template', array( __CLASS__, 'the_options' ), 20, 1 );
			add_filter( 'tribe_events_list_the_title', array( __CLASS__, 'the_title' ), 20, 1 );

			// Results
			add_filter( 'tribe_events_list_before_loop', array( __CLASS__, 'before_the_results' ), 20, 1 );

			// End list template
			add_filter( 'tribe_events_list_after_template', array( __CLASS__, 'after_template' ), 20, 1 );

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

		public static function the_title( $html ){
			$html = '<div id="tribe-events-geo-content">';
			$html .= sprintf( '<h2 class="tribe-events-page-title">%s</h2>',
				tribe_get_events_title()
				);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_the_title');
		}

		// Start Results
		public static function before_the_results( $html ){
			$html = '<div id="tribe-geo-results">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_map_before_the_results');
		}

		// End List Template
		public static function after_template( $html ){
			$html .= '</div><!-- #tribe-events-geo-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_after_template');		
		}

	}
	Tribe_Events_Map_Template::init();
}