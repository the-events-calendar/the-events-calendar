<?php
/**
 * These are for backwards compatibility with the free The Events Calendar plugin.
 * Don't use them.
 *
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( class_exists( 'TribeEvents' ) ) {
	if ( !function_exists( 'event_grid_view' ) ) {
		function event_grid_view() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_grid()' );
			tribe_calendar_grid();
		}
	}
	if ( !function_exists( 'get_event_google_map_link' ) ) {
		function get_event_google_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_map_link()' );
			return tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'event_google_map_link' ) ) {
		function event_google_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_map_link()' );
			echo tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'tec_get_event_address' ) ) {
		function tec_get_event_address( $postId = null, $includeVenue = false ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
			return tribe_get_full_address( $postId, $includeVenue );
		}
	}
	if ( !function_exists( 'tec_event_address' ) ) {
		function tec_event_address( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_full_address()' );
			echo tribe_get_full_address( $postId );
		}
	}
	if ( !function_exists( 'tec_address_exists' ) ) {
		function tec_address_exists( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_address_exists()' );
			return tribe_address_exists( $postId );
		}
	}
	if ( !function_exists( 'get_event_google_map_embed' ) ) {
		function get_event_google_map_embed( $postId = null, $width = '', $height = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_embedded_map()' );
			return tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'event_google_map_embed' ) ) {
		function event_google_map_embed( $postId = null, $width = null, $height = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_embedded_map()' );
			echo tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'get_jump_to_date_calendar' ) ) {
		function get_jump_to_date_calendar( $prefix = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_month_year_dropdowns()' );
			tribe_month_year_dropdowns( $prefix );
		}
	}
	if ( !function_exists( 'the_event_start_date' ) ) {
		function the_event_start_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_start_date()' );
			echo tribe_get_start_date( $postId, $showtime, $dateFormat );
		}
	}
	if ( !function_exists( 'the_event_end_date' ) ) {
		function the_event_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_end_date()' );
			echo tribe_get_end_date( $postId, $showtime, $dateFormat );
		}
	}
	if ( !function_exists( 'the_event_cost' ) ) {
		function the_event_cost( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_cost()' );
			echo tribe_get_cost( $postId );
		}
	}
	if ( !function_exists( 'the_event_venue' ) ) {
		function the_event_venue( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_venue()' );
			echo tribe_get_venue( $postID );
		}
	}
	if ( !function_exists( 'the_event_country' ) ) {
		function the_event_country( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_country()' );
			echo tribe_get_country( $postID );
		}
	}
	if ( !function_exists( 'the_event_address' ) ) {
		function the_event_address( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_address()' );
			echo tribe_get_address( $postID );
		}
	}
	if ( !function_exists( 'the_event_city' ) ) {
		function the_event_city( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_city()' );
			echo tribe_get_city( $postID );
		}
	}
	if ( !function_exists( 'the_event_state' ) ) {
		function the_event_state( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_state()' );
			echo tribe_get_state( $postID );
		}
	}
	if ( !function_exists( 'the_event_province' ) ) {
		function the_event_province( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_province()' );
			echo tribe_get_province( $postID );
		}
	}
	if ( !function_exists( 'the_event_zip' ) ) {
		function the_event_zip( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_zip()' );
			echo tribe_get_zip( $postID );
		}
	}
	if ( !function_exists( 'the_event_phone' ) ) {
		function the_event_phone( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_phone()' );
			echo tribe_get_phone( $postID );
		}
	}
	if ( !function_exists( 'the_event_region' ) ) {
		function the_event_region() {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_region()' );
			echo tribe_get_region( $postID );
		}
	}
	if ( !function_exists( 'the_event_all_day' ) ) {
		function the_event_all_day( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_all_day()' );
			echo tribe_get_all_day( $postID );
		}
	}
	if ( !function_exists( 'is_new_event_day' ) ) {
		function is_new_event_day() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_new_event_day()' );
			return tribe_is_new_event_day();
		}
	}
	if ( !function_exists( 'get_events' ) ) {
		function get_events( $numResults = null, $catName = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
			return tribe_get_events( $numResults, $catName );
		}
	}
	if ( !function_exists( 'events_displaying_past' ) ) {
		function events_displaying_past() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_past()' );
			return tribe_is_past();
		}
	}
	if ( !function_exists( 'events_displaying_upcoming' ) ) {
		function events_displaying_upcoming() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_upcoming()' );
			return tribe_is_upcoming();
		}
	}
	if ( !function_exists( 'events_displaying_month' ) ) {
		function events_displaying_month() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_month()' );
			return tribe_is_month();
		}
	}
	if ( !function_exists( 'events_get_past_link' ) ) {
		function events_get_past_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_past_link()' );
			return tribe_get_past_link();
		}
	}
	if ( !function_exists( 'events_get_upcoming_link' ) ) {
		function events_get_upcoming_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_upcoming_link()' );
			return tribe_get_upcoming_link();
		}
	}
	if ( !function_exists( 'events_get_next_month_link' ) ) {
		function events_get_next_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_link()' );
			return tribe_get_next_month_link();
		}
	}
	if ( !function_exists( 'events_get_previous_month_link' ) ) {
		function events_get_previous_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_link()' );
			return tribe_get_previous_month_link();
		}
	}
	if ( !function_exists( 'events_get_events_link' ) ) {
		function events_get_events_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events_link()' );
			return tribe_get_events_link();
		}
	}
	if ( !function_exists( 'events_get_gridview_link' ) ) {
		function events_get_gridview_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gridview_link()' );
			return tribe_get_gridview_link();
		}
	}
	if ( !function_exists( 'events_get_listview_link' ) ) {
		function events_get_listview_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_link()' );
			return tribe_get_listview_link();
		}
	}
	if ( !function_exists( 'events_get_listview_past_link' ) ) {
		function events_get_listview_past_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
			return tribe_get_listview_past_link();
		}
	}
	if ( !function_exists( 'events_get_previous_month_text' ) ) {
		function events_get_previous_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_text()' );
			return tribe_get_previous_month_text();
		}
	}
	if ( !function_exists( 'events_get_current_month_text' ) ) {
		function events_get_current_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_current_month_text()' );
			return tribe_get_current_month_text();
		}
	}
	if ( !function_exists( 'events_get_next_month_text' ) ) {
		function events_get_next_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_text()' );
			return tribe_get_next_month_text();
		}
	}
	if ( !function_exists( 'events_get_displayed_month' ) ) {
		function events_get_displayed_month() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_displayed_month()' );
			return tribe_get_displayed_month();
		}
	}
	if ( !function_exists( 'events_get_this_month_link' ) ) {
		function events_get_this_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_this_month_link()' );
			return tribe_get_this_month_link();
		}
	}
	if ( !function_exists( 'sp_get_option' ) ) {
		function sp_get_option( $optionName, $default = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_option()' );
			return tribe_get_option( $optionName, $default );
		}
	}
	if ( !function_exists( 'sp_calendar_grid' ) ) {
		function sp_calendar_grid() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_grid()' );
			return tribe_calendar_grid();
		}
	}
	if ( !function_exists( 'sp_calendar_mini_grid' ) ) {
		function sp_calendar_mini_grid() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_mini_grid()' );
			return tribe_calendar_mini_grid();
		}
	}
	if ( !function_exists( 'sp_sort_by_month' ) ) {
		function sp_sort_by_month( $results, $date ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_sort_by_month()' );
			return tribe_sort_by_month( $results, $date );
		}
	}
	if ( !function_exists( 'sp_is_event' ) ) {
		function sp_is_event( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_event()' );
			return tribe_is_event( $postId );
		}
	}
	if ( !function_exists( 'sp_get_map_link' ) ) {
		function sp_get_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_map_link()' );
			return tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'sp_the_map_link' ) ) {
		function sp_the_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_map_link()' );
			echo tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'sp_get_full_address' ) ) {
		function sp_get_full_address( $postId = null, $includeVenue = false ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
			return tribe_get_full_address( $postId );
		}
	}
	if ( !function_exists( 'sp_the_full_address' ) ) {
		function sp_the_full_address( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
			echo tribe_get_full_address( $postId );
		}
	}
	if ( !function_exists( 'sp_address_exists' ) ) {
		function sp_address_exists( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_address_exists()' );
			return tribe_address_exists( $postId );
		}
	}
	if ( !function_exists( 'sp_get_embedded_map' ) ) {
		function sp_get_embedded_map( $postId = null, $width = '', $height = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_embedded_map()' );
			return tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'sp_the_embedded_map' ) ) {
		function sp_the_embedded_map( $postId = null, $width = null, $height = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_embedded_map()' );
			echo tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'sp_month_year_dropdowns' ) ) {
		function sp_month_year_dropdowns( $prefix = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_month_year_dropdowns()' );
			return tribe_month_year_dropdowns( $prefix );
		}
	}
	if ( !function_exists( 'sp_get_start_date' ) ) {
		function sp_get_start_date( $postId = null, $showtime = true, $dateFormat = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_start_date()' );
			return tribe_get_start_date( $postId, $showtime, $dateFormat );
		}
	}
	if ( !function_exists( 'sp_get_end_date' ) ) {
		function sp_get_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_end_date()' );
			return tribe_get_end_date( $postId, $showtime, $dateFormat );
		}
	}
	if ( !function_exists( 'sp_get_cost' ) ) {
		function sp_get_cost( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_cost()' );
			return tribe_get_cost( $postId );
		}
	}
	if ( !function_exists( 'sp_has_organizer' ) ) {
		function sp_has_organizer( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_has_organizer()' );
			return tribe_has_organizer( $postId );
		}
	}
	if ( !function_exists( 'sp_get_organizer' ) ) {
		function sp_get_organizer( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer()' );
			return tribe_get_organizer( $postId );
		}
	}
	if ( !function_exists( 'sp_get_organizer_email' ) ) {
		function sp_get_organizer_email( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_email()' );
			return tribe_get_organizer_email( $postId );
		}
	}
	if ( !function_exists( 'sp_get_organizer_website' ) ) {
		function sp_get_organizer_website( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_link( $postId, false )' );
			return tribe_get_organizer_link( $postId, false );
		}
	}
	if ( !function_exists( 'sp_get_organizer_link' ) ) {
		function sp_get_organizer_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_link()' );
			return tribe_get_organizer_link( $postId );
		}
	}
	if ( !function_exists( 'sp_get_organizer_phone' ) ) {
		function sp_get_organizer_phone( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_phone()' );
			return tribe_get_organizer_phone( $postId );
		}
	}
	if ( !function_exists( 'sp_has_venue' ) ) {
		function sp_has_venue( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_venue_id()' );
			return tribe_get_venue_id( $postId );
		}
	}
	if ( !function_exists( 'sp_get_venue' ) ) {
		function sp_get_venue( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_venue()' );
			return tribe_get_venue( $postId );
		}
	}
	if ( !function_exists( 'sp_get_country' ) ) {
		function sp_get_country( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_country()' );
			return tribe_get_country( $postId );
		}
	}
	if ( !function_exists( 'sp_get_address' ) ) {
		function sp_get_address( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_address()' );
			return tribe_get_address( $postId );
		}
	}
	if ( !function_exists( 'sp_get_city' ) ) {
		function sp_get_city( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_city()' );
			return tribe_get_city( $postId );
		}
	}
	if ( !function_exists( 'sp_get_stateprovince' ) ) {
		function sp_get_stateprovince( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_stateprovince()' );
			return tribe_get_stateprovince( $postId );
		}
	}
	if ( !function_exists( 'sp_get_state' ) ) {
		function sp_get_state( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_state()' );
			return tribe_get_state( $postId );
		}
	}
	if ( !function_exists( 'sp_get_province' ) ) {
		function sp_get_province( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_province()' );
			return tribe_get_province( $postId );
		}
	}
	if ( !function_exists( 'sp_get_zip' ) ) {
		function sp_get_zip( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_zip()' );
			return tribe_get_zip( $postId );
		}
	}
	if ( !function_exists( 'sp_get_phone' ) ) {
		function sp_get_phone( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_phone()' );
			return tribe_get_phone( $postId );
		}
	}
	if ( !function_exists( 'sp_previous_event_link' ) ) {
		function sp_previous_event_link( $anchor = false ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_previous_event_link()' );
			return tribe_previous_event_link( $anchor );
		}
	}
	if ( !function_exists( 'sp_next_event_link' ) ) {
		function sp_next_event_link( $anchor = false ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_next_event_link()' );
			return tribe_next_event_link( $anchor );
		}
	}
	if ( !function_exists( 'sp_post_id_helper' ) ) {
		function sp_post_id_helper( $postId ) {
			_deprecated_function( __FUNCTION__, '2.0' );
			return TribeEvents::postIdHelper( $postId );
		}
	}
	if ( !function_exists( 'sp_is_new_event_day' ) ) {
		function sp_is_new_event_day() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_new_event_day()' );
			return tribe_is_new_event_day();
		}
	}
	if ( !function_exists( 'sp_get_events' ) ) {
		function sp_get_events( $args = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
			return tribe_get_events( $args );
		}
	}
	if ( !function_exists( 'sp_is_past' ) ) {
		function sp_is_past() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_past()' );
			return tribe_is_past();
		}
	}
	if ( !function_exists( 'sp_is_upcoming' ) ) {
		function sp_is_upcoming() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_upcoming()' );
			return tribe_is_upcoming();
		}
	}
	if ( !function_exists( 'sp_is_month' ) ) {
		function sp_is_month() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_month()' );
			return tribe_is_month();
		}
	}
	if ( !function_exists( 'sp_get_past_link' ) ) {
		function sp_get_past_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_past_link()' );
			return tribe_get_past_link();
		}
	}
	if ( !function_exists( 'sp_get_upcoming_link' ) ) {
		function sp_get_upcoming_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_upcoming_link()' );
			return tribe_get_upcoming_link();
		}
	}
	if ( !function_exists( 'sp_get_next_month_link' ) ) {
		function sp_get_next_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_link()' );
			return tribe_get_next_month_link();
		}
	}
	if ( !function_exists( 'sp_get_previous_month_link' ) ) {
		function sp_get_previous_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_link()' );
			return tribe_get_previous_month_link();
		}
	}
	if ( !function_exists( 'sp_get_month_view_date' ) ) {
		function sp_get_month_view_date() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_month_view_date()' );
			return tribe_get_month_view_date();
		}
	}
	if ( !function_exists( 'sp_get_single_ical_link' ) ) {
		function sp_get_single_ical_link() {
			if ( function_exists( 'tribe_get_single_ical_link' ) ) {
				_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_single_ical_link()' );
				return tribe_get_single_ical_link();
			}
		}
	}
	if ( !function_exists( 'sp_get_events_link' ) ) {
		function sp_get_events_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
			return tribe_get_events_link();
		}
	}
	if ( !function_exists( 'sp_get_gridview_link' ) ) {
		function sp_get_gridview_link( $term = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gridview_link()' );
			return tribe_get_gridview_link( $term );
		}
	}
	if ( !function_exists( 'sp_get_listview_link' ) ) {
		function sp_get_listview_link( $term = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_link()' );
			return tribe_get_listview_link( $term );
		}
	}
	if ( !function_exists( 'sp_get_listview_past_link' ) ) {
		function sp_get_listview_past_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
			return tribe_get_listview_past_link();
		}
	}
	if ( !function_exists( 'sp_get_dropdown_link_prefix' ) ) {
		function sp_get_dropdown_link_prefix() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
			return tribe_get_dropdown_link_prefix();
		}
	}
	if ( !function_exists( 'sp_get_ical_link' ) ) {
		function sp_get_ical_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_ical_link()' );
			return tribe_get_ical_link();
		}
	}
	if ( !function_exists( 'sp_get_previous_month_text' ) ) {
		function sp_get_previous_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_text()' );
			return tribe_get_previous_month_text();
		}
	}
	if ( !function_exists( 'sp_get_current_month_text' ) ) {
		function sp_get_current_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_current_month_text()' );
			return tribe_get_current_month_text();
		}
	}
	if ( !function_exists( 'sp_get_next_month_text' ) ) {
		function sp_get_next_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_text()' );
			return tribe_get_next_month_text();
		}
	}
	if ( !function_exists( 'sp_get_displayed_month' ) ) {
		function sp_get_displayed_month() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_displayed_month()' );
			return tribe_get_displayed_month();
		}
	}
	if ( !function_exists( 'sp_get_this_month_link' ) ) {
		function sp_get_this_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_this_month_link()' );
			return tribe_get_this_month_link();
		}
	}
	if ( !function_exists( 'sp_get_region' ) ) {
		function sp_get_region( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_region()' );
			return tribe_get_region( $postId );
		}
	}
	if ( !function_exists( 'sp_get_all_day' ) ) {
		function sp_get_all_day( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_all_day()' );
			return tribe_get_all_day( $postId );
		}
	}
	if ( !function_exists( 'sp_is_multiday' ) ) {
		function sp_is_multiday( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_multiday()' );
			return tribe_is_multiday( $postId );
		}
	}
	if ( !function_exists( 'sp_events_title' ) ) {
		function sp_events_title() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_events_title()' );
			return tribe_events_title();
		}
	}
	if ( !function_exists( 'sp_meta_event_cats' ) ) {
		function sp_meta_event_cats() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_meta_event_cats()' );
			return tribe_meta_event_cats();
		}
	}
	if ( !function_exists( 'sp_meta_event_category_name' ) ) {
		function sp_meta_event_category_name() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_meta_event_category_name()' );
			return tribe_meta_event_category_name();
		}
	}
	if ( !function_exists( 'sp_get_add_to_gcal_link' ) ) {
		function sp_get_add_to_gcal_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gcal_link()' );
			return tribe_get_gcal_link();
		}
	}
	if ( !function_exists( 'eventsGetOptionValue' ) ) {
		function eventsGetOptionValue( $optionName ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_option()' );
			return tribe_get_option( $optionName );
		}
	}
	if ( !function_exists( 'events_by_month' ) ) {
		function events_by_month( $results, $date ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_sort_by_month()' );
			return tribe_sort_by_month( $results, $date );
		}
	}
	if ( !function_exists( 'is_event' ) ) {
		function is_event( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_event()' );
			return tribe_is_event( $postId );
		}
	}
	if ( !function_exists( 'getEventMeta' ) ) {
		function getEventMeta( $id, $meta, $single = true ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_event_meta()' );
			return tribe_get_event_meta( $id, $meta, $single );
		}
	}
	if ( !function_exists( 'tribe_the_map_link' ) ) {
		function tribe_the_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_map_link()' );
			echo tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'tribe_the_embedded_map' ) ) {
		function tribe_the_embedded_map( $postId = null, $width = null, $height = null ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_embedded_map()' );
			echo tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'tribe_the_full_address' ) ) {
		function tribe_the_full_address( $postId = null, $includeVenueName = false ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_full_address()' );
			echo tribe_get_full_address( $postId );
		}
	}
	if ( !function_exists( 'tribe_get_organizer_website' ) ) {
		function tribe_get_organizer_website( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_organizer_link($postId,false)' );
			$output = tribe_get_organizer_link( $postId, false );
			return apply_filters( 'tribe_get_organizer_website', $output );
		}
	}
	if ( !function_exists( 'tribe_get_venue_permalink' ) ) {
		function tribe_get_venue_permalink( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_venue_link($postId,false)' );
			return tribe_get_venue_link( $postId, false );
		}
	}
	if ( !function_exists( 'tribe_previous_event_link' ) ) {
		function tribe_previous_event_link( $anchor = false ) {
			_deprecated_function( __FUNCTION__, '2.1', 'echo tribe_the_prev_event_link( $anchor )' );
			echo apply_filters( 'tribe_previous_event_link', tribe_get_prev_event_link( $anchor ) );
		}
	}
	if ( !function_exists( 'tribe_next_event_link' ) ) {
		function tribe_next_event_link( $anchor = false ) {
			_deprecated_function( __FUNCTION__, '2.1', 'echo tribe_the_next_event_link( $anchor )' );
			echo apply_filters( 'tribe_next_event_link', tribe_get_next_event_link( $anchor ) );
		}
	}
	if ( !function_exists( 'display_day_title' ) ) {
		function display_day_title( $day, $monthView, $date ) {
			_deprecated_function( __FUNCTION__, '2.1', 'echo tribe_get_display_day_title( $day, $monthView, $date )' );
			return tribe_get_display_day_title( $day, $monthView, $date );
		}
	}
	if ( !function_exists( 'display_day' ) ) {
		function display_day( $day, $monthView ) {
			_deprecated_function( __FUNCTION__, '2.1', 'tribe_the_display_day( $day, $monthView )' );
			tribe_the_display_day( $day, $monthView );
		}
	}
	if ( !function_exists( 'tribe_meta_event_cats' ) ) {
		function tribe_meta_event_cats( $label = null, $separator = null ) {
			_deprecated_function( __FUNCTION__, '3.0', 'tribe_get_event_categories( $post_id, $args )' );
			$args = array(
				'before' => '<dd class="tribe-event-categories">',
				'sep' => ', ',
				'after' => '</dd>',
				'label' => __( 'Category', 'tribe-events-calendar' ),
				'label_before' => '<dt>',
				'label_after' => '</dt>',
				'wrap_before' => '',
				'wrap_after' => ''
			);
			echo apply_filters( 'tribe_meta_event_cats', tribe_get_event_categories( get_the_ID(), $args ) );
		}
	}

	if ( !function_exists( 'tribe_get_all_day' ) ) {
		function tribe_get_all_day( $postId = null ) {
			_deprecated_function( __FUNCTION__, '3.0', 'tribe_event_is_all_day( $postId )' );
			return apply_filters( 'tribe_get_all_day', tribe_event_is_all_day( $postId ) );
		}
	}

	if ( !function_exists( 'tribe_is_multiday' ) ) {
		function tribe_is_multiday( $postId = null ) {
			_deprecated_function( __FUNCTION__, '3.0', 'tribe_event_is_multiday( $postId )' );
			return apply_filters( 'tribe_is_multiday', tribe_event_is_multiday( $postId ) );
		}
	}
}
