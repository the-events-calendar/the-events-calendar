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
			add_filter( 'tribe_events_day_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			add_filter( 'tribe_events_day_the_header', array( __CLASS__, 'the_header' ), 1, 1 );
			add_filter( 'tribe_events_day_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

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

		public function before_header( $post_id ){
			$html = '<div id="tribe-events-calendar-header" class="clearfix">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_before_pagination');
		}
		public function the_header( $post_id ){
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();

			ob_start();
			tribe_month_year_dropdowns( "tribe-events-" );
			$dropdown = ob_get_clean();

			// echo '<pre>';
			// print_r($wp_query->posts);
			// echo '</pre>';
			$current_day = $wp_query->get('start_date');
			// Display Day Navigation
			// <-- Previous Day | Month/Day/Year Selector | Next Day -->
			$html = sprintf('<span class="tribe-events-week-nav"><span class="tribe-events-prev-week"><a href="%s">%s</a></span> %s <span class="tribe-events-next-week"><a href="%s">%s</a><img src="%s" class="ajax-loading" id="ajax-loading" alt="" style="display: none" /></span></span>',
								trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($current_day . " -1 day") ) ),
								__( 'Yesterday', 'tribe-events-calendar-pro' ),
								$dropdown,
								trailingslashit( get_site_url() ) . trailingslashit( $tribe_ecp->rewriteSlug ) . trailingslashit( Date('Y-m-d', strtotime($current_day . " +1 day") ) ),
								__( 'Tomorrow', 'tribe-events-calendar-pro' ),
								esc_url( admin_url( 'images/wpspin_light.gif' ) )
								);

			// View Buttons
			ob_start();
			include_once(TribeEventsTemplates::getTemplateHierarchy( 'buttons', 'modules' ));
			$html .= ob_get_clean();

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_day_pagination');
		}
		public function after_header( $post_id ){
			$html = '</div>';
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