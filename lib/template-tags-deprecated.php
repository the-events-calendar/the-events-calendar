<?php
/**
 * These are for backwards compatibility with the free The Events Calendar plugin.
 * Don't use them.
 *
 */

//*
function event_grid_view() {
	sp_calendar_grid();
}
function get_event_google_map_link( $postId = null ) {
	return sp_get_map_link( $postId );
}
function event_google_map_link( $postId = null ) {
	sp_the_map_link( $postId );
}
function tec_get_event_address( $postId = null, $includeVenue = false ) {
	return sp_get_full_address($postId, $includeVenue);
}
function tec_event_address( $postId = null ) {
	sp_the_full_address( $postId );
}
function tec_address_exists( $postId = null ) {
	return sp_address_exists( $postId );
}
function get_event_google_map_embed( $postId = null, $width = '', $height = '' ) {
	return sp_get_embedded_map( $postId, $width, $height );
}
function event_google_map_embed( $postId = null, $width = null, $height = null ) {
	sp_the_embedded_map( $postId, $width, $height );
}
function get_jump_to_date_calendar( $prefix = '' ) {
	sp_month_year_dropdowns( $prefix );
}
function the_event_start_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
	return sp_get_start_date( $postId, $showtime, $dateFormat );
}
function the_event_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
	return sp_get_end_date( $postId, $showtime, $dateFormat );
}
function the_event_cost( $postId = null) {
	return sp_get_cost($postId);
}
function the_event_venue( $postId = null) {
	return sp_get_venue( $postID );
}
function the_event_country( $postId = null) {
	return sp_get_country( $postID );
}
function the_event_address( $postId = null) {
	return sp_get_address( $postID );
}
function the_event_city( $postId = null) {
	return sp_get_city( $postID );
}
function the_event_state( $postId = null) {
	return sp_get_state( $postID );
}
function the_event_province( $postId = null) {
	return sp_get_province( $postID );
}
function the_event_zip( $postId = null) {
	return sp_get_zip( $postID );
}
function the_event_phone( $postId = null) {
	return sp_get_phone( $postID );
}
function the_event_region() {
	return sp_get_region( $postID );
}
function the_event_all_day( $postId = null ) {
	return sp_get_all_day( $postID );
}
function is_new_event_day() {
	return sp_is_new_event_day();
}
function get_events( $numResults = null, $catName = null ) {
	return sp_get_events( $numResults, $catName );
}
function events_displaying_past() {
	return sp_is_past();
}
function events_displaying_upcoming() {
	return sp_is_upcoming();
}
function events_displaying_month() {
	return sp_is_month();
}
function events_get_past_link() {
	return sp_get_past_link();
}
function events_get_upcoming_link() {
	return sp_get_upcoming_link();
}
function events_get_next_month_link() {
	return sp_get_next_month_link();
}
function events_get_previous_month_link() {
	return sp_get_previous_month_link();
}
function events_get_events_link() {
	return sp_get_events_link();
}
function events_get_gridview_link() {
	return sp_get_gridview_link();
}	
function events_get_listview_link() {
	return sp_get_listview_link();
}
function events_get_listview_past_link() {
	return sp_get_listview_past_link();
}
function events_get_previous_month_text() {
	return sp_get_previous_month_text();
}
function events_get_current_month_text(){ 
	return sp_get_current_month_text();
}
function events_get_next_month_text() {
	return sp_get_next_month_text();
}
function events_get_displayed_month() {
	return sp_get_displayed_month();
}
function events_get_this_month_link() {
	return sp_get_this_month_link();
}

/* SP Template Tags.  Deprecated in favor of return tribe_ */
function sp_get_option($optionName, $default = '') {
	return tribe_get_option($optionName, $default);
}

function sp_calendar_grid() {
	return tribe_calendar_grid();
}

function sp_calendar_mini_grid() {
	return tribe_calendar_mini_grid();
}

function sp_sort_by_month( $results, $date ) {
	return tribe_sort_by_month( $results, $date );
}

function sp_is_event( $postId = null ) {
	return tribe_is_event( $postId );
}

function sp_get_map_link( $postId = null ) {
	return tribe_get_map_link( $postId );
	}

function sp_the_map_link( $postId = null ) {
	return tribe_the_map_link( $postId );
}

function sp_get_full_address( $postId = null, $includeVenue = false ) {
	return tribe_get_full_address( $postId, $includeVenue );
}

function sp_the_full_address( $postId = null ) {
	return tribe_the_full_address( $postId );
}

function sp_address_exists( $postId = null ) {
	return tribe_address_exists( $postId );
}

function sp_get_embedded_map( $postId = null, $width = '', $height = '' ) {
	return tribe_get_embedded_map( $postId, $width, $height );
}

function sp_the_embedded_map( $postId = null, $width = null, $height = null ) {
	return tribe_the_embedded_map( $postId, $width, $height );
}

function sp_month_year_dropdowns( $prefix = '' ) {
	return tribe_month_year_dropdowns( $prefix );
}

function sp_get_start_date( $postId = null, $showtime = true, $dateFormat = '' ) {
	return tribe_get_start_date( $postId, $showtime, $dateFormat );
}

function sp_event_format_date($date, $showtime = true,  $dateFormat = '') {
	return tribe_event_format_date($date, $showtime,  $dateFormat);
}

function sp_get_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
	return tribe_get_end_date( $postId, $showtime, $dateFormat );
}

function sp_get_cost( $postId = null) {
	return tribe_get_cost( $postId);
}

function sp_has_organizer( $postId = null) {
	return tribe_has_organizer( $postId);
}

function sp_get_organizer( $postId = null) {
	return tribe_get_organizer( $postId);
}

function sp_get_organizer_email( $postId = null) {
	return tribe_get_organizer_email( $postId);
}
function sp_get_organizer_website( $postId = null) {
	return tribe_get_organizer_website( $postId);
}
function sp_get_organizer_link( $postId = null) {
	return tribe_get_organizer_link( $postId);
}
function sp_get_organizer_phone( $postId = null) {
	return tribe_get_organizer_phone( $postId);
}
function sp_has_venue( $postId = null) {
	return tribe_has_venue( $postId);
}
function sp_get_venue( $postId = null) {
	return tribe_get_venue( $postId);
}
function sp_get_country( $postId = null) {
	return tribe_get_country( $postId);
}
function sp_get_address( $postId = null) {
	return tribe_get_address( $postId);
}
function sp_get_city( $postId = null) {
	return tribe_get_city( $postId);
}
function sp_get_stateprovince( $postId = null) {
	return tribe_get_stateprovince( $postId);
}
function sp_get_state( $postId = null) {
	return tribe_get_state( $postId);
}
function sp_get_province( $postId = null) {
	return tribe_get_province( $postId);
}
function sp_get_zip( $postId = null) {
	return tribe_get_zip( $postId);
}
function sp_get_phone( $postId = null) {
	return tribe_get_phone( $postId);
}
function sp_all_occurences_link( ) {
	return tribe_all_occurences_link( );
}
function sp_previous_event_link( $anchor = false ) {
	return tribe_previous_event_link( $anchor );
}
function sp_next_event_link( $anchor = false ) {
	return tribe_next_event_link( $anchor );
}
function sp_post_id_helper( $postId ) {
	return tribe_post_id_helper( $postId );
}
function sp_is_new_event_day() {
	return tribe_is_new_event_day();
}
function sp_get_events( $args = '' ) {
	return tribe_get_events( $args );
}
function sp_is_past() {
	return tribe_is_past();
}
function sp_is_upcoming() {
	return tribe_is_upcoming();
}
function sp_is_showing_all() {
	return tribe_is_showing_all();
}
function sp_is_month() {
	return tribe_is_month();
}
function sp_get_past_link() {
	return tribe_get_past_link();
}
function sp_get_upcoming_link() {
	return tribe_get_upcoming_link();
}
function sp_get_next_month_link() {
	return tribe_get_next_month_link();
}
function sp_get_previous_month_link() {
	return tribe_get_previous_month_link();
}

function sp_get_month_view_date() {
	return tribe_get_month_view_date();
}
function sp_get_single_ical_link() {
	return tribe_get_single_ical_link();
}
function sp_get_events_link() {
	return tribe_get_events_link();
}
function sp_get_gridview_link($term = null) {
	return tribe_get_gridview_link($term);
}
function sp_get_listview_link($term = null) {
	return tribe_get_listview_link($term);
}
function sp_get_listview_past_link() {
	return tribe_get_listview_past_link();
}

function sp_get_dropdown_link_prefix() {
	return tribe_get_dropdown_link_prefix();
}
function sp_get_ical_link() {
	return tribe_get_ical_link();
}

function sp_get_previous_month_text() {
	return tribe_get_previous_month_text();
}
function sp_get_current_month_text( ){
	return tribe_get_current_month_text();
}
function sp_get_next_month_text() {
	return tribe_get_next_month_text();
}
function sp_get_displayed_month() {
	return tribe_get_displayed_month();
}
function sp_get_this_month_link() {
	return tribe_get_this_month_link();
}
function sp_get_region( $postId = null ) {
	return tribe_get_region( $postId );
}
function sp_get_all_day( $postId = null ) {
	return tribe_get_all_day( $postId );
}
function sp_is_multiday( $postId = null) {
	return tribe_is_multiday( $postId );
}
function sp_events_title() {
	return tribe_events_title();
}
function sp_get_events_title() {
	return tribe_get_events_title();
}
function sp_meta_event_cats() {
	return tribe_meta_event_cats();
}
function sp_meta_event_category_name(){
	return tribe_meta_event_category_name();
}
function sp_is_recurring_event( $postId = null ) {
	if (function_exists('tribe_is_recurring_event')) {
		return tribe_is_recurring_event( $postId );
	}
}
function sp_get_recurrence_text( $postId = null ) {
	return tribe_get_recurrence_text( $postId );
}
function sp_get_add_to_gcal_link() {
	return tribe_get_add_to_gcal_link();
}
