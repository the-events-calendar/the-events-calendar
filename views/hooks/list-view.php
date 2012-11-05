<?php
/**
 *
 *
 * @for Single Venue Template
 * This file contains the hook logic required to create an effective single venue view.
 *
 * @package TribeEventsCalendarPro
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'Tribe_Events_Pro_List_View_Template' ) ) {
	class Tribe_Events_Pro_List_View_Template extends Tribe_Template_Factory {
		public static function init() {
			add_filter( 'tribe_events_list_view_before_template', array( __CLASS__, 'before_template' ), 1 );
			add_filter( 'tribe_events_list_view_events', array( __CLASS__, 'upcoming_events' ), 1 );
			add_filter( 'tribe_events_list_view_after_template', array( __CLASS__, 'after_template' ), 1 );
		}
		// Start Single Venue Template
		public static function before_template( ) {
			$html = '<div id="tribe-events-list-view">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_view_before_template' );
		}

		// Event List View
		public static function upcoming_events( ) {
			$args = array( 'eventDisplay' => 'upcoming' );
			$html = tribe_include_view_list( $args );
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_view_upcoming_events' );
		}

		// End Single Venue Template
		public static function after_template( ) {
			$html = '</div><!-- #tribe-events-list-view -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_view_after_template' );
		}
	}
	Tribe_Events_Pro_List_View_Template::init();
}
