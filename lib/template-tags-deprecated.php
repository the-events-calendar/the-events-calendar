<?php
/**
 * These are for backwards compatibility with the free The Events Calendar plugin.
 * Don't use them.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( class_exists('TribeEvents') ) {

	function event_grid_view() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_grid()' );
		tribe_calendar_grid();
	}

	function get_event_google_map_link( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_map_link()' );
		return tribe_get_map_link( $postId );
	}

	function event_google_map_link( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_map_link()' );
		echo tribe_get_map_link( $postId );
	}

	function tec_get_event_address( $postId = null, $includeVenue = false ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
		return tribe_get_full_address($postId, $includeVenue);
	}

	function tec_event_address( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_full_address()' );
		echo tribe_get_full_address( $postId );
	}

	function tec_address_exists( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_address_exists()' );
		return tribe_address_exists( $postId );
	}

	function get_event_google_map_embed( $postId = null, $width = '', $height = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_embedded_map()' );
		return tribe_get_embedded_map( $postId, $width, $height );
	}

	function event_google_map_embed( $postId = null, $width = null, $height = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_embedded_map()' );
		echo tribe_get_embedded_map( $postId, $width, $height );
	}

	function get_jump_to_date_calendar( $prefix = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_month_year_dropdowns()' );
		tribe_month_year_dropdowns( $prefix );
	}

	function the_event_start_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_start_date()' );
		echo tribe_get_start_date( $postId, $showtime, $dateFormat );
	}

	function the_event_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_end_date()' );
		echo tribe_get_end_date( $postId, $showtime, $dateFormat );
	}

	function the_event_cost( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_cost()' );
		echo tribe_get_cost($postId);
	}

	function the_event_venue( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_venue()' );
		echo tribe_get_venue( $postID );
	}

	function the_event_country( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_country()' );
		echo tribe_get_country( $postID );
	}

	function the_event_address( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_address()' );
		echo tribe_get_address( $postID );
	}

	function the_event_city( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_city()' );
		echo tribe_get_city( $postID );
	}

	function the_event_state( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_state()' );
		echo tribe_get_state( $postID );
	}

	function the_event_province( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_province()' );
		echo tribe_get_province( $postID );
	}

	function the_event_zip( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_zip()' );
		echo tribe_get_zip( $postID );
	}

	function the_event_phone( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_phone()' );
		echo tribe_get_phone( $postID );
	}

	function the_event_region() {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_region()' );
		echo tribe_get_region( $postID );
	}

	function the_event_all_day( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_all_day()' );
		echo tribe_get_all_day( $postID );
	}

	function is_new_event_day() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_new_event_day()' );
		return tribe_is_new_event_day();
	}

	function get_events( $numResults = null, $catName = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
		return tribe_get_events( $numResults, $catName );
	}

	function events_displaying_past() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_past()' );
		return tribe_is_past();
	}

	function events_displaying_upcoming() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_upcoming()' );
		return tribe_is_upcoming();
	}

	function events_displaying_month() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_month()' );
		return tribe_is_month();
	}

	function events_get_past_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_past_link()' );
		return tribe_get_past_link();
	}

	function events_get_upcoming_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_upcoming_link()' );
		return tribe_get_upcoming_link();
	}

	function events_get_next_month_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_link()' );
		return tribe_get_next_month_link();
	}

	function events_get_previous_month_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_link()' );
		return tribe_get_previous_month_link();
	}

	function events_get_events_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events_link()' );
		return tribe_get_events_link();
	}

	function events_get_gridview_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gridview_link()' );
		return tribe_get_gridview_link();
	}
	
	function events_get_listview_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_link()' );
		return tribe_get_listview_link();
	}

	function events_get_listview_past_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
		return tribe_get_listview_past_link();
	}

	function events_get_previous_month_text() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_text()' );
		return tribe_get_previous_month_text();
	}

	function events_get_current_month_text() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_current_month_text()' );
		return tribe_get_current_month_text();
	}

	function events_get_next_month_text() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_text()' );
		return tribe_get_next_month_text();
	}

	function events_get_displayed_month() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_displayed_month()' );
		return tribe_get_displayed_month();
	}

	function events_get_this_month_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_this_month_link()' );
		return tribe_get_this_month_link();
	}

	/* SP Template Tags.  Deprecated in favor of return tribe_ */
	if ( !function_exists( 'sp_get_option' ) ) {
		function sp_get_option($optionName, $default = '') {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_option()' );
			return tribe_get_option($optionName, $default);
		}
	}

	function sp_calendar_grid() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_grid()' );
		return tribe_calendar_grid();
	}

	function sp_calendar_mini_grid() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_mini_grid()' );
		return tribe_calendar_mini_grid();
	}

	function sp_sort_by_month( $results, $date ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_sort_by_month()' );
		return tribe_sort_by_month( $results, $date );
	}

	function sp_is_event( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_event()' );
		return tribe_is_event( $postId );
	}

	function sp_get_map_link( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_map_link()' );
		return tribe_get_map_link( $postId );
	}

	function sp_the_map_link( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_map_link()' );
		echo tribe_get_map_link( $postId );
	}

	function sp_get_full_address( $postId = null, $includeVenue = false ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
		return tribe_get_full_address( $postId );
	}

	function sp_the_full_address( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
		echo tribe_get_full_address( $postId );
	}

	function sp_address_exists( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_address_exists()' );
		return tribe_address_exists( $postId );
	}

	function sp_get_embedded_map( $postId = null, $width = '', $height = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_embedded_map()' );
		return tribe_get_embedded_map( $postId, $width, $height );
	}

	function sp_the_embedded_map( $postId = null, $width = null, $height = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_embedded_map()' );
		echo tribe_get_embedded_map( $postId, $width, $height );
	}

	function sp_month_year_dropdowns( $prefix = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_month_year_dropdowns()' );
		return tribe_month_year_dropdowns( $prefix );
	}

	function sp_get_start_date( $postId = null, $showtime = true, $dateFormat = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_start_date()' );
		return tribe_get_start_date( $postId, $showtime, $dateFormat );
	}

	function sp_get_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_end_date()' );
		return tribe_get_end_date( $postId, $showtime, $dateFormat );
	}

	function sp_get_cost( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_cost()' );
		return tribe_get_cost( $postId);
	}

	function sp_has_organizer( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_has_organizer()' );
		return tribe_has_organizer( $postId);
	}

	function sp_get_organizer( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer()' );
		return tribe_get_organizer( $postId);
	}

	function sp_get_organizer_email( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_email()' );
		return tribe_get_organizer_email( $postId);
	}

	function sp_get_organizer_website( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_link( $postId, false )' );
		return tribe_get_organizer_link( $postId, false );
	}

	function sp_get_organizer_link( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_link()' );
		return tribe_get_organizer_link( $postId);
	}

	function sp_get_organizer_phone( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_phone()' );
		return tribe_get_organizer_phone( $postId);
	}

	function sp_has_venue( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_venue_id()' );
		return tribe_get_venue_id( $postId);
	}

	function sp_get_venue( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_venue()' );
		return tribe_get_venue( $postId);
	}

	function sp_get_country( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_country()' );
		return tribe_get_country( $postId);
	}

	function sp_get_address( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_address()' );
		return tribe_get_address( $postId);
	}

	function sp_get_city( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_city()' );
		return tribe_get_city( $postId);
	}

	function sp_get_stateprovince( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_stateprovince()' );
		return tribe_get_stateprovince( $postId);
	}

	function sp_get_state( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_state()' );
		return tribe_get_state( $postId);
	}

	function sp_get_province( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_province()' );
		return tribe_get_province( $postId);
	}

	function sp_get_zip( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_zip()' );
		return tribe_get_zip( $postId);
	}

	function sp_get_phone( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_phone()' );
		return tribe_get_phone( $postId);
	}

	function sp_previous_event_link( $anchor = false ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_previous_event_link()' );
		return tribe_previous_event_link( $anchor );
	}

	function sp_next_event_link( $anchor = false ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_next_event_link()' );
		return tribe_next_event_link( $anchor );
	}

	function sp_post_id_helper( $postId ) {
		_deprecated_function( __FUNCTION__, '2.0' );
		return TribeEvents::postIdHelper( $postId );
	}

	function sp_is_new_event_day() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_new_event_day()' );
		return tribe_is_new_event_day();
	}

	function sp_get_events( $args = '' ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
		return tribe_get_events( $args );
	}

	function sp_is_past() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_past()' );
		return tribe_is_past();
	}

	function sp_is_upcoming() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_upcoming()' );
		return tribe_is_upcoming();
	}

	function sp_is_month() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_month()' );
		return tribe_is_month();
	}

	function sp_get_past_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_past_link()' );
		return tribe_get_past_link();
	}

	function sp_get_upcoming_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_upcoming_link()' );
		return tribe_get_upcoming_link();
	}

	function sp_get_next_month_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_link()' );
		return tribe_get_next_month_link();
	}

	function sp_get_previous_month_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_link()' );
		return tribe_get_previous_month_link();
	}

	function sp_get_month_view_date() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_month_view_date()' );
		return tribe_get_month_view_date();
	}

	function sp_get_single_ical_link() {
		if( function_exists('tribe_get_single_ical_link') ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_single_ical_link()' );
			return tribe_get_single_ical_link();
		}
	}

	function sp_get_events_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
		return tribe_get_events_link();
	}

	function sp_get_gridview_link($term = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gridview_link()' );
		return tribe_get_gridview_link($term);
	}

	function sp_get_listview_link($term = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_link()' );
		return tribe_get_listview_link($term);
	}

	function sp_get_listview_past_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
		return tribe_get_listview_past_link();
	}

	function sp_get_dropdown_link_prefix() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
		return tribe_get_dropdown_link_prefix();
	}

	function sp_get_ical_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_ical_link()' );
		return tribe_get_ical_link();
	}

	function sp_get_previous_month_text() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_text()' );
		return tribe_get_previous_month_text();
	}

	function sp_get_current_month_text(){
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_current_month_text()' );
		return tribe_get_current_month_text();
	}

	function sp_get_next_month_text() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_text()' );
		return tribe_get_next_month_text();
	}

	function sp_get_displayed_month() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_displayed_month()' );
		return tribe_get_displayed_month();
	}

	function sp_get_this_month_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_this_month_link()' );
		return tribe_get_this_month_link();
	}

	function sp_get_region( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_region()' );
		return tribe_get_region( $postId );
	}

	function sp_get_all_day( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_all_day()' );
		return tribe_get_all_day( $postId );
	}

	function sp_is_multiday( $postId = null) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_multiday()' );
		return tribe_is_multiday( $postId );
	}

	function sp_events_title() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_events_title()' );
		return tribe_events_title();
	}

	function sp_meta_event_cats() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_meta_event_cats()' );
		return tribe_meta_event_cats();
	}

	function sp_meta_event_category_name(){
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_meta_event_category_name()' );
		return tribe_meta_event_category_name();
	}

	function sp_get_add_to_gcal_link() {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gcal_link()' );
		return tribe_get_gcal_link();
	}
	
	function eventsGetOptionValue($optionName) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_option()' );
		return tribe_get_option($optionName);
	}
	
	function events_by_month( $results, $date ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_sort_by_month()' );
		return tribe_sort_by_month( $results, $date );
	}
	
	function is_event( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_event()' );
		return tribe_is_event( $postId );
	}

	function getEventMeta( $id, $meta, $single = true ){
		_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_event_meta()' );
		return tribe_get_event_meta( $id, $meta, $single );
	}

	function tribe_the_map_link( $postId = null ) {
		_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_map_link()' );
		echo tribe_get_map_link( $postId );
	}

	function tribe_the_embedded_map( $postId = null, $width = null, $height = null ) {
		_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_embedded_map()' );
		echo tribe_get_embedded_map( $postId, $width, $height );
	}

	function tribe_the_full_address( $postId = null, $includeVenueName = false ) {
		_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_full_address()' );
		echo tribe_get_full_address( $postId );
	}
	
	function tribe_get_organizer_website( $postId = null)  {
		_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_organizer_link($postId,false)' );
		$output = tribe_get_organizer_link( $postId, false );
		return apply_filters( 'tribe_get_organizer_website', $output);
	}

	function tribe_get_venue_permalink( $postId = null)  {
		_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_venue_link($postId,false)' );
		return tribe_get_venue_link( $postId, false );
	}

}
?>