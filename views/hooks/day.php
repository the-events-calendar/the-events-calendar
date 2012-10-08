<?php
/**
 * @for Day Grid Template
 * This file contains the hook logic required to create an effective day grid view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }


if( !class_exists('Tribe_Events_Day_Template')){
	class Tribe_Events_Day_Template extends Tribe_Template_Factory {

		public static function init(){
			// Start list template
			add_filter( 'tribe_events_day_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// List pagination
			add_filter( 'tribe_events_day_before_pagination', array( __CLASS__, 'before_pagination' ), 1, 1 );
			add_filter( 'tribe_events_day_pagination', array( __CLASS__, 'pagination' ), 1, 1 );
			add_filter( 'tribe_events_day_after_pagination', array( __CLASS__, 'after_pagination' ), 1, 1 );

			// Start list loop
			add_filter( 'tribe_events_day_before_loop', array( __CLASS__, 'before_loop' ), 1, 1 );
			add_filter( 'tribe_events_day_inside_before_loop', array( __CLASS__, 'inside_before_loop' ), 1, 1 );
	
			// End list loop
			add_filter( 'tribe_events_day_inside_after_loop', array( __CLASS__, 'inside_after_loop' ), 1, 1 );
			add_filter( 'tribe_events_day_after_loop', array( __CLASS__, 'after_loop' ), 1, 1 );
	
				// End list template
			add_filter( 'tribe_events_day_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Template
		public function before_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_template');
		}
		// Start List Loop
		public function before_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_loop');
		}
		public function inside_before_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_inside_before_loop');
		}

		// End List Loop
		public function inside_after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_inside_after_loop');
		}
		public function after_loop( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_after_loop');
		}

		// List Pagination
		public function before_pagination( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_pagination');
		}
		public function pagination( $post_id ){
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();
			echo '<pre>';
			print_r($wp_query->posts);
			echo '</pre>';
			$current_day = $wp_query->get('start_date');
			// Display Day Navigation
			// <-- Previous Day | Month/Day/Year Selector | Next Day -->
			$html = sprintf('<a href="%s">%s</a> %s <a href="%s">%s</a>',
								trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($current_day . " -1 day") ) ),
								__( 'Yesterday', 'tribe-events-calendar-pro' ),
								'| ' . date('F jS', strtotime($current_day)) . ' |',
								trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($current_day . " +1 day") ) ),
								__( 'Tomorrow', 'tribe-events-calendar-pro' )
								);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_pagination');
		}
		public function after_pagination( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_after_pagination');
		}
		// End List Template
		public function after_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_after_template');		
		}
	}
	Tribe_Events_Day_Template::init();
}