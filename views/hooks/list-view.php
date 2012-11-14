<?php
/**
 *
 *
 * @for     Single Venue Template
 *          This file contains the hook logic required to create an effective single venue view.
 *
 * @package TribeEventsCalendarPro
 * @since   2.1
 * @author  Modern Tribe Inc.
 *
 */

if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( !class_exists( 'Tribe_Events_Pro_List_View_Template' ) ) {
	class Tribe_Events_Pro_List_View_Template extends Tribe_Template_Factory {

		public static function init() {
			add_filter( 'tribe_events_list_view_before_template', array( __CLASS__, 'before_template' ), 1 );
			add_filter( 'tribe_events_list_view_events', array( __CLASS__, 'upcoming_events' ), 1 );
			add_filter( 'tribe_events_list_view_after_template', array( __CLASS__, 'after_template' ), 1 );
			add_filter( 'tribe_events_list_pagination', array( __CLASS__, 'clear_module_pagination' ), 10 );
		}

		public static function before_template() {
			$html = '<input type="hidden" id="tribe-events-list-hash" value="">';

			$html .= '<div id="tribe-events-list-view">';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_view_before_template' );
		}

		// Event List View
		public static function upcoming_events() {
			$args = array( 'eventDisplay' => 'upcoming' );
			$html = tribe_include_view_list( $args );
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_view_upcoming_events' );
		}

		// End Single Venue Template
		public static function after_template() {
			$html = '</div><!-- #tribe-events-list-view-wrapper -->';
			return apply_filters( 'tribe_template_factory_debug', $html, 'tribe_events_list_view_after_template' );
		}

		public static function clear_module_pagination( $html ) {
			global $wp_query;
			$html = "";
			if ( $wp_query->query_vars['paged'] > 1 ) {
				$html .= '<li class="tribe-nav-previous"><a href="#" id="tribe_paged_prev" class="tribe_paged">' . __( '<< Previous Events' ) . '</a></li>';
			}
			if ( $wp_query->max_num_pages > ( $wp_query->query_vars['paged'] + 1 ) ) {
				$html .= '<li class="tribe-nav-next"><a href="#" id="tribe_paged_next" class="tribe_paged">' . __( 'Next Events >>' ) . '</a></li>';
			}
			return $html;
		}

	}

	Tribe_Events_Pro_List_View_Template::init();
}
