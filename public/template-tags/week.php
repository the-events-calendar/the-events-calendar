<?php
/**
 * Events Calendar Pro Week Template Tags
 *
 * Display functions for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if( class_exists('TribeEventsPro')) {

	function tribe_events_week_set_loop_type( $loop_type ){
		Tribe_Events_Pro_Week_Template::instance()->loop_type = $loop_type;
	}

	function tribe_events_week_get_loop_type(){
		return apply_filters( 'tribe_events_week_get_loop_type', Tribe_Events_Pro_Week_Template::instance()->loop_type );
	}

	function tribe_events_week_column_classes(){
		echo apply_filters('tribe_events_week_column_classes', Tribe_Events_Pro_Week_Template::instance()->column_classes());
	}

	function tribe_events_week_event_classes(){
		echo apply_filters('tribe_events_week_event_classes', Tribe_Events_Pro_Week_Template::instance()->event_classes());	
	}

	function tribe_events_week_get_days(){
		return apply_filters('tribe_events_week_get_days', Tribe_Events_Pro_Week_Template::instance()->get_week_days() );
	}

	function tribe_events_week_get_all_day_map(){
		return apply_filters('tribe_events_week_get_all_day_map', Tribe_Events_Pro_Week_Template::instance()->get_events('all_day_map') );
	}
	function tribe_events_week_get_all_day(){
		return apply_filters('tribe_events_week_get_all_day', Tribe_Events_Pro_Week_Template::instance()->get_events('all_day') );
	}

	function tribe_events_week_get_all_day_map_col(){
		$all_day_map  = Tribe_Events_Pro_Week_Template::instance()->get_events('all_day_map');
		return apply_filters('tribe_events_week_get_all_day_map_col', $all_day_map[ Tribe_Events_Pro_Week_Template::instance()->get_current_day() ]);
	}

	function tribe_events_week_get_hourly(){
		return apply_filters('tribe_events_week_get_hourly', Tribe_Events_Pro_Week_Template::instance()->get_events('hourly') );
	}

	function tribe_events_week_setup_current_day( $day_id = 0 ){
		Tribe_Events_Pro_Week_Template::instance()->set_current_day( $day_id );
	}

	function tribe_events_week_get_current_day(){
		Tribe_Events_Pro_Week_Template::instance()->get_current_day();
	}

	function tribe_events_week_setup_event( $event_id = false ){
		$instance = Tribe_Events_Pro_Week_Template::instance();
		switch( $instance->loop_type ) {
			case 'allday':
				$instance->set_event_id( $event_id );
				return true;
				break;
			case 'hourly':
				$instance->set_event_id( $event_id );
				$event = $instance->get_hourly_event();
				if ( !empty($event->EventStartDate) && date( 'Y-m-d', strtotime( $event->EventStartDate ) ) <= $instance->get_current_date() && date( 'Y-m-d', strtotime( $event->EventEndDate ) ) >= $instance->get_current_date() ) {
					return true; 
				} else {
					return false;
				}
				break;
		}
	}
	function tribe_events_week_get_event_id(){
		return apply_filters('tribe_events_week_get_event_id', Tribe_Events_Pro_Week_Template::instance()->get_event_id() );
	}

	function tribe_events_week_is_not_allday_event_field(){
		$instance = Tribe_Events_Pro_Week_Template::instance();
		$event_key_id = $instance->get_event_id();
		if( is_null( $event_key_id ) || in_array( $event_key_id, $instance->event_key_track ) ) {
			return true;
		} else {
			return false;
		}
	}

	function tribe_events_week_get_event(){
		
		$instance = Tribe_Events_Pro_Week_Template::instance();
		
		switch( $instance->loop_type ) {
			case 'allday':
				$event = $instance->get_allday_event();
				$event_id = $instance->get_event_id();	
				$instance->event_key_track[] = $event_id;
				break;
			case 'hourly':
				$event = $instance->get_hourly_event();
				break;
		}

		return apply_filters( 'tribe_events_week_get_event', $event );
	}

}