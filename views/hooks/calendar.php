<?php
/**
 * Grid View Template
 * This file loads the TEC month view, specifically the 
 * month view navigation. The actual rendering if the calendar happens in the 
 * table.php template.
 *
 * You can customize this view by putting a replacement file of the same name 
 * (calendar.php) in the tribe-events/ directory of your theme.
 *
 * @package TribeEventsCalendar
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Calendar_Template')){
	class Tribe_Events_Calendar_Template extends Tribe_Template_Factory {
		function init(){
			// start calendar template
			add_filter( 'tribe_events_calendar_before_template', array( __CLASS__, 'before_template' ), 1, 1 );

			// calendar header
			add_filter( 'tribe_events_calendar_before_header', array( __CLASS__, 'before_header' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_header', array( __CLASS__, 'after_header' ), 1, 1 );

			// calendar title
			add_filter( 'tribe_events_calendar_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_calendar_the_title', array( __CLASS__, 'the_title' ), 1, 2 );
			add_filter( 'tribe_events_calendar_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// calendar navigation
			add_filter( 'tribe_events_calendar_before_nav', array( __CLASS__, 'before_nav' ), 1, 1 );
			add_filter( 'tribe_events_calendar_nav', array( __CLASS__, 'navigation' ), 1, 2 );
			add_filter( 'tribe_events_calendar_after_nav', array( __CLASS__, 'after_nav' ), 1, 1 );

			// calendar notices
			add_filter( 'tribe_events_calendar_notices', array( __CLASS__, 'notices' ), 1, 2 );

			// calendar buttons
			add_filter( 'tribe_events_calendar_before_buttons', array( __CLASS__, 'before_buttons' ), 1, 1 );
			add_filter( 'tribe_events_calendar_buttons', array( __CLASS__, 'buttons' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_buttons', array( __CLASS__, 'after_buttons' ), 1, 1 );

			// calendar content
			add_filter( 'tribe_events_calendar_before_the_content', array( __CLASS__, 'before_the_content' ), 1, 1 );
			add_filter( 'tribe_events_calendar_the_content', array( __CLASS__, 'the_content' ), 1, 1 );
			add_filter( 'tribe_events_calendar_after_the_content', array( __CLASS__, 'after_the_content' ), 1, 1 );

			// end calendar template
			apply_filters( 'tribe_events_calendar_after_template', array( __CLASS__, 'after_template' ), 1, 1 );
		}
		public function before_template( $post_id ){
			$html = '<div id="tribe-events-content" class="grid">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_template');
		}
		public function before_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_before_the_title');
		}
		public function the_title( $title, $post_id ){
			// This title is here for ajax loading – do not remove if you want ajax switching between month views
			$html = '<title>' . wp_title( '&raquo;', false ) . '</title>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_the_title');
		}
		public function after_the_title( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_single_event_after_the_title');
		}
		public function notices( $notices, $post_id ){
			$html = '';
			if(!empty($notices))	
				$html .= '<div class="event-notices">' . implode('<br />', $notices) . '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_notices');
		}
		public function before_header( $post_id ){
			$html = '<div id="tribe-events-calendar-header" class="clearfix">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_header');
		}
		public function after_header( $post_id ){
			$html = '</div>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_header');
		}
		// calendar navigation
		public function before_nav( $post_id ){
			$html = '<span class="tribe-events-month-nav">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_nav');
		}
		public function navigation( $post_id ){
			$html = '<span class="tribe-events-prev-month">';
			$html .= '<a href="' . tribe_get_previous_month_link() . '"> &#x2190; ' . tribe_get_previous_month_text() . ' </a>';
			$html .= '</span>';

			ob_start();
			tribe_month_year_dropdowns( "tribe-events-" );
			$html .= ob_get_clean();
	
			$html .= '<span class="tribe-events-next-month">';
			$html .= '<a href="' . tribe_get_next_month_link() . '"> ' . tribe_get_next_month_text() . ' &#x2192; </a>';
            $html .= '<img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="ajax-loading" id="ajax-loading" alt="" style="display: none" />';
			$html .= '</span>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_nav');
		}
		public function after_nav( $post_id ){
			$html = '</span>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_nav');
		}
		// calendar event buttons
		public function before_buttons( $post_id){
			$html = '<span class="tribe-events-calendar-buttons">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_before_buttons');
		}
		public function buttons( $post_id ){
			$html = '<a class="tribe-events-button-off" href="' . tribe_get_listview_link() . '">' . __( 'Event List', 'tribe-events-calendar' ) . '</a>';
			$html .= '<a class="tribe-events-button-on" href="' . tribe_get_gridview_link() . '">' . __( 'Calendar', 'tribe-events-calendar' ) . '</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_buttons');
		}
		public function after_buttons( $post_id ){
			$html = '</span>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_buttons');
		}
		public function after_template( $post_id ){
			if( function_exists( 'tribe_get_ical_link' ) )
				$html .= '<a title="' . esc_attr( 'iCal Import', 'tribe-events-calendar' ) . '" class="ical" href="' . tribe_get_ical_link() . '">' . __( 'iCal Import', 'tribe-events-calendar' ) . '</a>';
			if ( tribe_get_option( 'donate-link', false ) == true )
				$html = '<p class="tribe-promo-banner">' . apply_filters( 'tribe_promo_banner', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a href="http://tri.be/wordpress-events-calendar/">', '</a>' ) ) . '</p>';
			$html .= '</div><!-- #tribe-events-content -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_calendar_after_template');
		}
	}
	Tribe_Events_Calendar_Template::init();
}