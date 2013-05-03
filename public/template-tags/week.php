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
		Tribe_Events_Pro_Week_Template::$loop_type = $loop_type;
	}

	function tribe_events_week_get_loop_type(){
		return apply_filters( 'tribe_events_week_get_loop_type', Tribe_Events_Pro_Week_Template::$loop_type );
	}

	function tribe_events_week_column_classes(){
		echo apply_filters('tribe_events_week_column_classes', Tribe_Events_Pro_Week_Template::column_classes());
	}

	function tribe_events_week_event_classes(){
		echo apply_filters('tribe_events_week_event_classes', Tribe_Events_Pro_Week_Template::event_classes());	
	}

	function tribe_events_week_get_days(){
		return apply_filters('tribe_events_week_get_days', Tribe_Events_Pro_Week_Template::get_week_days() );
	}

	function tribe_events_week_get_all_day_map(){
		return apply_filters('tribe_events_week_get_all_day_map', Tribe_Events_Pro_Week_Template::get_events('all_day_map') );
	}
	function tribe_events_week_get_all_day(){
		return apply_filters('tribe_events_week_get_all_day', Tribe_Events_Pro_Week_Template::get_events('all_day') );
	}

	function tribe_events_week_get_all_day_map_col(){
		$all_day_map  = Tribe_Events_Pro_Week_Template::get_events('all_day_map');
		return apply_filters('tribe_events_week_get_all_day_map_col', $all_day_map[ Tribe_Events_Pro_Week_Template::get_current_day() ]);
	}

	function tribe_events_week_get_hourly(){
		return apply_filters('tribe_events_week_get_hourly', Tribe_Events_Pro_Week_Template::get_events('hourly') );
	}

	function tribe_events_week_setup_current_day( $day_id = 0 ){
		Tribe_Events_Pro_Week_Template::set_current_day( $day_id );
	}

	function tribe_events_week_get_current_day(){
		Tribe_Events_Pro_Week_Template::get_current_day();
	}

	function tribe_events_week_setup_event( $event_id = false ){
		switch( Tribe_Events_Pro_Week_Template::$loop_type ) {
			case 'allday':
				Tribe_Events_Pro_Week_Template::set_event_id( $event_id );
				return true;
				break;
			case 'hourly':
				Tribe_Events_Pro_Week_Template::set_event_id( $event_id );
				$event = Tribe_Events_Pro_Week_Template::get_hourly_event();
				echo Tribe_Events_Pro_Week_Template::get_current_date();
				if ( !empty($event->EventStartDate) && date( 'Y-m-d', strtotime( $event->EventStartDate ) ) <= Tribe_Events_Pro_Week_Template::get_current_date() && date( 'Y-m-d', strtotime( $event->EventEndDate ) ) >= Tribe_Events_Pro_Week_Template::get_current_date() ) {
					return true; 
				} else {
					return false;
				}
				break;
		}
	}
	function tribe_events_week_get_event_id(){
		return apply_filters('tribe_events_week_get_event_id', Tribe_Events_Pro_Week_Template::get_event_id() );
	}

	function tribe_events_week_is_not_allday_event_field(){
		$event_key_id = Tribe_Events_Pro_Week_Template::get_event_id();
		if( is_null( $event_key_id ) || in_array( $event_key_id, Tribe_Events_Pro_Week_Template::$event_key_track ) ) {
			return true;
		} else {
			return false;
		}
	}

	function tribe_events_week_get_event(){
		switch( Tribe_Events_Pro_Week_Template::$loop_type ) {
			case 'allday':
				$event = Tribe_Events_Pro_Week_Template::get_allday_event();
				$event_id = Tribe_Events_Pro_Week_Template::get_event_id();	
				Tribe_Events_Pro_Week_Template::$event_key_track[] = $event_id;
				break;
			case 'hourly':
				$event = Tribe_Events_Pro_Week_Template::get_hourly_event();
				break;
		}

		return apply_filters( 'tribe_events_week_get_event', $event );
	}

}