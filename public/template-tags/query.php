<?php

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	function tribe_is_event_category() {
		global $wp_query; 
		$tribe_is_event_category = !empty( $wp_query->tribe_is_event );
		return apply_filters( 'tribe_query_is_event_category', $tribe_is_event_category ); 
	}
	function tribe_is_event_venue() {
		global $wp_query; 
		$tribe_is_event_venue = !empty( $wp_query->tribe_is_event_venue );
		return apply_filters( 'tribe_query_is_event_venue', $tribe_is_event_venue ); 
	}
	function tribe_is_event_organizer() {
		global $wp_query; 
		$tribe_is_event_organizer = !empty( $wp_query->tribe_is_event_organizer );
		return apply_filters( 'tribe_query_is_event_organizer', $tribe_is_event_organizer ); 
	}
	function tribe_is_event_query() {
		global $wp_query; 
		$tribe_is_event_query = !empty( $wp_query->tribe_is_event_query );
		return apply_filters( 'tribe_query_is_event_query', $tribe_is_event_query ); 
	}
}