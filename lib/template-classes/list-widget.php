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
		public static function init(){
			// Start list widget template
			add_filter( 'tribe_events_list_widget_before_template', array( __CLASS__, 'before_template' ), 1, 1 );
	
			// Event dates
			add_filter( 'tribe_events_list_widget_before_the_date', array( __CLASS__, 'before_the_date' ), 1, 1 );
			add_filter( 'tribe_events_list_widget_the_date', array( __CLASS__, 'the_date' ), 1, 1 );
			add_filter( 'tribe_events_list_widget_after_the_date', array( __CLASS__, 'after_the_date' ), 1, 1 );

			// Event title
			add_filter( 'tribe_events_list_widget_before_the_title', array( __CLASS__, 'before_the_title' ), 1, 1 );
			add_filter( 'tribe_events_list_widget_the_title', array( __CLASS__, 'the_title' ), 1, 1 );
			add_filter( 'tribe_events_list_widget_after_the_title', array( __CLASS__, 'after_the_title' ), 1, 1 );

			// End list widget template
			add_filter( 'tribe_events_list_widget_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		// Start List Widget Template
		public static function before_template( $event, $class = '' ){
			$html = '<li '. $class .'>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_before_template');
		}
		// Event Dates	
		public static function before_the_date( $event ){
			$html = '<div class="duration">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_before_the_date');
		}
		public static function the_date( $event, $post_id = null, $start = true, $end = true ){
			$html = '<abbr class="tribe-events-abbr updated published dtstart" title="'. tribe_get_start_date( $post_id, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_get_start_date( $post_id, $start ) .'</abbr><!-- .dtstart -->';

			if( !tribe_is_multiday( $post_id ) && !tribe_get_all_day( $post_id ) ) {
				$html .= ' &ndash; <br/><abbr class="tribe-events-abbr dtend" title="'. tribe_get_end_date( $post_id, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_get_end_date( $post_id, false, 'g:i a' ) .'</abbr><!-- .dtend -->';
			} elseif( !tribe_get_all_day( $post_id ) || ( tribe_is_multiday( $post_id ) && tribe_get_all_day( $post_id ) ) ) {
				$html .= ' &ndash; <br/><abbr class="tribe-events-abbr dtend" title="'. tribe_get_end_date( $post_id, false, TribeDateUtils::DBDATEFORMAT ) .'">'. tribe_get_end_date( $post_id, $end ) .'</abbr><!-- .dtend -->';
			}

			if( tribe_get_all_day( $post_id ) )
				$html .= ' <small><em>('. __('All Day','tribe-events-calendar') .')</em></small>';

			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_the_date');
		}
		public static function after_the_date( $event ){
			$html = '</div><!-- .duration -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_after_the_date');
		}
		// Event Title
		public static function before_the_title( $event ){
			$html = '<p class="entry-title summary>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_before_the_title');
		}
		public static function the_title( $post ){
			$html = '<a href="'. get_permalink( $post ) .'" rel="bookmark">'. $post->post_title .'</a>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_the_title');
		}
		public static function after_the_title( $event ){
			$html = '</p><!-- .summary -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_after_the_title');
		}
		// End List Widget Template
		public static function after_template( $event ){
			$html = '</li><!-- .vevent -->';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_list_widget_after_template');		
		}
	}
}