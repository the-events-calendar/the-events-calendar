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



//*/