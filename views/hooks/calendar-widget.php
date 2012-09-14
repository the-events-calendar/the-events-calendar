<?php
/**
 * @for Calendar Widget Template
 * This file contains the hook logic required to create an effective calendar widget view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

// the div tribe-events-widget-nav controls ajax navigation for the calendar widget. 
// Modify with care and do not remove any class names or elements inside that element 
// if you wish to retain ajax functionality.

if( !class_exists('Tribe_Events_Calendar_Widget_Template')){
	class Tribe_Events_Calendar_Widget_Template extends Tribe_Template_Factory {
		function init(){
			// start calendar widget template
			add_filter( 'tribe_events_calendar_widget_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// calendar ajax navigation
			add_filter( 'tribe_events_calendar_widget_before_the_nav', array( __CLASS__, 'before_the_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_the_nav', array( __CLASS__, 'the_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_after_the_nav', array( __CLASS__, 'after_the_nav' ), 1, 1 );

			// start calendar
			add_filter( 'tribe_events_calendar_widget_before_the_cal', array( __CLASS__, 'before_the_cal' ), 1, 1 );
	
			// calendar days of the week
			add_filter( 'tribe_events_calendar_widget_before_the_days', array( __CLASS__, 'before_the_days' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_after_the_days', array( __CLASS__, 'after_the_days' ), 1, 1 );

			// calendar dates
			add_filter( 'tribe_events_calendar_widget_before_the_dates', array( __CLASS__, 'before_the_dates' ), 1, 1 );
			add_filter( 'tribe_events_calendar_widget_after_the_dates', array( __CLASS__, 'after_the_dates' ), 1, 1 );
	
			// end calendar
			add_filter( 'tribe_events_calendar_widget_after_the_cal', array( __CLASS__, 'after_the_cal' ), 1, 1 );

			// end calendar widget template
			add_filter( 'tribe_events_calendar_widget_after_template', array( __CLASS__, 'after_template' ), 1, 1 );	
		}
		// Start Calendar Widget Template
		public function before_template( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_template');
		}
		// Calendar Ajax Navigation
		public function before_the_nav( $post_id ){
			$html = '<div class="tribe-events-widget-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_the_nav');
		}
		public function the_nav( $post_id ){
			$html = '<a class="tribe-mini-ajax prev-month" href="#" data-month="'. $tribe_ecp->previousMonth( $current_date ) .'" title="'. tribe_get_previous_month_text() .'"><span>'. tribe_get_previous_month_text() .'</span></a>';
			$html .= '<span id="tribe-mini-ajax-month">'. $tribe_ecp->monthsShort[date( 'M',$date )]; date( ' Y',$date ) .'</span>';
			$html .= '<a class="tribe-mini-ajax next-month" href="#" data-month="'. $tribe_ecp->nextMonth( $current_date ) .'" title="'. tribe_get_next_month_text() .'"><span>'. tribe_get_next_month_text() .'</span></a>';
			$html .= '<img id="ajax-loading-mini" src="'. esc_url( admin_url( 'images/wpspin_light.gif' ) ) .'" alt="loading..." />';		
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_the_nav');
		}
		public function after_the_nav( $post_id ){
			$html = '</div><!-- .tribe-events-widget-nav -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_the_nav');
		}
		// Start Calendar
		public function before_the_cal( $post_id ){
			$html = '<table class="tribe-events-calendar tribe-events-calendar-widget" id="small">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_the_cal');
		}
		// Calendar Days of the Week
		public function before_the_days( $post_id ){
			$html = '<thead><tr>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_the_days');
		}
		public function after_the_days( $post_id ){
			$html = '</tr></thead>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_the_days');
		}
		// Calendar Dates
		public function before_the_dates( $post_id ){
			$html = '<tbody><tr>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_before_the_dates');
		}
		public function after_the_dates( $post_id ){
			$html = '</tr></tbody>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_the_dates');
		}
		// End Calendar
		public function after_the_cal( $post_id ){
			$html = '</table><!-- .tribe-events-calendar-widget -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_the_cal');
		}
		// End Calendar Widget Template		
		public function after_template( $post_id ){
			$html = '<a class="tribe-view-all-events" href="'. tribe_get_events_link() .'">'. __( 'View all &raquo;', 'tribe-events-calendar' ) .'</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_widget_after_template');		
		}
	}
	Tribe_Events_Calendar_Widget_Template::init();
}