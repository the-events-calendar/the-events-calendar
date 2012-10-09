<?php

/**
 * @for Buttons Module Template
 * This file contains the hook logic required to create an effective buttons module view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Buttons_Template')){
	class Tribe_Events_Buttons_Template extends Tribe_Template_Factory {
		public static function init(){
			// Start address template
			add_filter( 'tribe_events_buttons_before_template', array( __CLASS__, 'before_template' ), 1, 1 );
	
			// Address meta
			add_filter( 'tribe_events_buttons_before_the_buttons', array( __CLASS__, 'before_the_buttons' ), 1, 1 );
			add_filter( 'tribe_events_buttons_the_buttons', array( __CLASS__, 'the_buttons' ), 1, 1 );
			add_filter( 'tribe_events_buttons_after_the_buttons', array( __CLASS__, 'after_the_buttons' ), 1, 1 );

			// End address template
			add_filter( 'tribe_events_buttons_after_template', array( __CLASS__, 'after_template' ), 1, 2 );
		}
		function before_template(){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_buttons_before_template');
		}
		function before_the_buttons(){
			$html = '<span class="tribe-events-calendar-buttons">';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_buttons_before_the_buttons');
		}
		function the_buttons(){
			global $wp_query;
			if( tribe_is_upcoming() || tribe_is_past() || (is_single() && tribe_is_showing_all()) ){
				$list_class = 'tribe-events-button-on';
				$grid_class = 'tribe-events-button-off';
			} else {
				$list_class = 'tribe-events-button-off';
				$grid_class = 'tribe-events-button-on';
			}
			$html = sprintf('<a class="%s" href="%s">%s</a><a class="%s" href="%s">%s</a>',
				$list_class,
				tribe_get_listview_link(),
				__( 'Event List', 'tribe-events-calendar' ),
				$grid_class,
				tribe_get_gridview_link(),
				__( 'Calendar', 'tribe-events-calendar' )
				);
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_buttons_the_buttons');
		}
		function after_the_buttons(){
			$html = '</span>';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_buttons_after_the_buttons');
		}
		function after_template(){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_buttons_after_template');
		}
	}
	Tribe_Events_Buttons_Template::init();
}