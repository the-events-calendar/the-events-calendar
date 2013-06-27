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
		/**
		 * @deprecated
		 */
		function event_grid_view() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_grid()' );
			tribe_calendar_grid();
		}
	}
	if ( !function_exists( 'get_event_google_map_link' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @return string
		 */
		function get_event_google_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_map_link()' );
			return tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'event_google_map_link' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function event_google_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_map_link()' );
			echo tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'tec_get_event_address' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @param bool $includeVenue
		 * @return string
		 */
		function tec_get_event_address( $postId = null, $includeVenue = false ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
			return tribe_get_full_address( $postId, $includeVenue );
		}
	}
	if ( !function_exists( 'tec_event_address' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function tec_event_address( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_full_address()' );
			echo tribe_get_full_address( $postId );
		}
	}
	if ( !function_exists( 'tec_address_exists' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @return bool
		 */
		function tec_address_exists( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_address_exists()' );
			return tribe_address_exists( $postId );
		}
	}
	if ( !function_exists( 'get_event_google_map_embed' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @param string $width
		 * @param string $height
		 * @return string
		 */
		function get_event_google_map_embed( $postId = null, $width = '', $height = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_embedded_map()' );
			return tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'event_google_map_embed' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @param null $width
		 * @param null $height
		 */
		function event_google_map_embed( $postId = null, $width = null, $height = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_embedded_map()' );
			echo tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'get_jump_to_date_calendar' ) ) {
		/**
		 * @deprecated
		 * @param string $prefix
		 */
		function get_jump_to_date_calendar( $prefix = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_month_year_dropdowns()' );
			tribe_month_year_dropdowns( $prefix );
		}
	}
	if ( !function_exists( 'the_event_start_date' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @param string $showtime
		 * @param string $dateFormat
		 */
		function the_event_start_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_start_date()' );
			echo tribe_get_start_date( $postId, $showtime, $dateFormat );
		}
	}
	if ( !function_exists( 'the_event_end_date' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @param string $showtime
		 * @param string $dateFormat
		 */
		function the_event_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_end_date()' );
			echo tribe_get_end_date( $postId, $showtime, $dateFormat );
		}
	}
	if ( !function_exists( 'the_event_cost' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_cost( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_cost()' );
			echo tribe_get_cost( $postId );
		}
	}
	if ( !function_exists( 'the_event_venue' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_venue( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_venue()' );
			echo tribe_get_venue( $postID );
		}
	}
	if ( !function_exists( 'the_event_country' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_country( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_country()' );
			echo tribe_get_country( $postID );
		}
	}
	if ( !function_exists( 'the_event_address' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_address( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_address()' );
			echo tribe_get_address( $postID );
		}
	}
	if ( !function_exists( 'the_event_city' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_city( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_city()' );
			echo tribe_get_city( $postID );
		}
	}
	if ( !function_exists( 'the_event_state' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_state( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_state()' );
			echo tribe_get_state( $postID );
		}
	}
	if ( !function_exists( 'the_event_province' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_province( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_province()' );
			echo tribe_get_province( $postID );
		}
	}
	if ( !function_exists( 'the_event_zip' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_zip( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_zip()' );
			echo tribe_get_zip( $postID );
		}
	}
	if ( !function_exists( 'the_event_phone' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_phone( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_phone()' );
			echo tribe_get_phone( $postID );
		}
	}
	if ( !function_exists( 'the_event_region' ) ) {
		/**
		 * @deprecated
		 */
		function the_event_region() {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_region()' );
			echo tribe_get_region( $postID );
		}
	}
	if ( !function_exists( 'the_event_all_day' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function the_event_all_day( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_all_day()' );
			echo tribe_get_all_day( $postID );
		}
	}
	if ( !function_exists( 'is_new_event_day' ) ) {
		/**
		 * @deprecated
		 * @return bool
		 */
		function is_new_event_day() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_new_event_day()' );
			return tribe_is_new_event_day();
		}
	}
	if ( !function_exists( 'get_events' ) ) {
		/**
		 * @deprecated
		 * @param null $numResults
		 * @param null $catName
		 * @return array
		 */
		function get_events( $numResults = null, $catName = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
			return tribe_get_events( $numResults, $catName );
		}
	}
	if ( !function_exists( 'events_displaying_past' ) ) {
		/**
		 * @deprecated
		 * @return bool
		 */
		function events_displaying_past() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_past()' );
			return tribe_is_past();
		}
	}
	if ( !function_exists( 'events_displaying_upcoming' ) ) {
		/**
		 * @deprecated
		 * @return bool
		 */
		function events_displaying_upcoming() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_upcoming()' );
			return tribe_is_upcoming();
		}
	}
	if ( !function_exists( 'events_displaying_month' ) ) {
		/**
		 * @deprecated
		 * @return bool
		 */
		function events_displaying_month() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_month()' );
			return tribe_is_month();
		}
	}
	if ( !function_exists( 'events_get_past_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_past_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_past_link()' );
			return tribe_get_past_link();
		}
	}
	if ( !function_exists( 'events_get_upcoming_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_upcoming_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_upcoming_link()' );
			return tribe_get_upcoming_link();
		}
	}
	if ( !function_exists( 'events_get_next_month_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_next_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_link()' );
			return tribe_get_next_month_link();
		}
	}
	if ( !function_exists( 'events_get_previous_month_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_previous_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_link()' );
			return tribe_get_previous_month_link();
		}
	}
	if ( !function_exists( 'events_get_events_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_events_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events_link()' );
			return tribe_get_events_link();
		}
	}
	if ( !function_exists( 'events_get_gridview_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_gridview_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gridview_link()' );
			return tribe_get_gridview_link();
		}
	}
	if ( !function_exists( 'events_get_listview_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_listview_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_link()' );
			return tribe_get_listview_link();
		}
	}
	if ( !function_exists( 'events_get_listview_past_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_listview_past_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
			return tribe_get_listview_past_link();
		}
	}
	if ( !function_exists( 'events_get_previous_month_text' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_previous_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_text()' );
			return tribe_get_previous_month_text();
		}
	}
	if ( !function_exists( 'events_get_current_month_text' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_current_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_current_month_text()' );
			return tribe_get_current_month_text();
		}
	}
	if ( !function_exists( 'events_get_next_month_text' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_next_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_text()' );
			return tribe_get_next_month_text();
		}
	}
	if ( !function_exists( 'events_get_displayed_month' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_displayed_month() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_displayed_month()' );
			return tribe_get_displayed_month();
		}
	}
	if ( !function_exists( 'events_get_this_month_link' ) ) {
		/**
		 * @deprecated
		 * @return string
		 */
		function events_get_this_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_this_month_link()' );
			return tribe_get_this_month_link();
		}
	}
	if ( !function_exists( 'sp_get_option' ) ) {
		/**
		 * @deprecated
		 * @param $optionName
		 * @param string $default
		 * @return mixed
		 */
		function sp_get_option( $optionName, $default = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_option()' );
			return tribe_get_option( $optionName, $default );
		}
	}
	if ( !function_exists( 'sp_calendar_grid' ) ) {
		/**
		 * @deprecated
		 * @return mixed
		 */
		function sp_calendar_grid() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_grid()' );
			return tribe_calendar_grid();
		}
	}
	if ( !function_exists( 'sp_calendar_mini_grid' ) ) {
		/**
		 * @deprecated
		 */
		function sp_calendar_mini_grid() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_calendar_mini_grid()' );
			return tribe_calendar_mini_grid();
		}
	}
	if ( !function_exists( 'sp_sort_by_month' ) ) {
		/**
		 * @deprecated
		 * @param $results
		 * @param $date
		 * @return array
		 */
		function sp_sort_by_month( $results, $date ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_sort_by_month()' );
			return tribe_sort_by_month( $results, $date );
		}
	}
	if ( !function_exists( 'sp_is_event' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @return bool
		 */
		function sp_is_event( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_event()' );
			return tribe_is_event( $postId );
		}
	}
	if ( !function_exists( 'sp_get_map_link' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 * @return string
		 */
		function sp_get_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_map_link()' );
			return tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'sp_the_map_link' ) ) {
		/**
		 * @deprecated
		 * @param null $postId
		 */
		function sp_the_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_map_link()' );
			echo tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'sp_get_full_address' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_full_address( $postId = null, $includeVenue = false ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
			return tribe_get_full_address( $postId );
		}
	}
	if ( !function_exists( 'sp_the_full_address' ) ) {
		/**
		 * @deprecated
		 */
		function sp_the_full_address( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_full_address()' );
			echo tribe_get_full_address( $postId );
		}
	}
	if ( !function_exists( 'sp_address_exists' ) ) {
		/**
		 * @deprecated
		 */
		function sp_address_exists( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_address_exists()' );
			return tribe_address_exists( $postId );
		}
	}
	if ( !function_exists( 'sp_get_embedded_map' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_embedded_map( $postId = null, $width = '', $height = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_embedded_map()' );
			return tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'sp_the_embedded_map' ) ) {
		/**
		 * @deprecated
		 */
		function sp_the_embedded_map( $postId = null, $width = null, $height = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'echo tribe_get_embedded_map()' );
			echo tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'sp_month_year_dropdowns' ) ) {
		/**
		 * @deprecated
		 */
		function sp_month_year_dropdowns( $prefix = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_month_year_dropdowns()' );
			return tribe_month_year_dropdowns( $prefix );
		}
	}
	if ( !function_exists( 'sp_get_start_date' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_start_date( $postId = null, $showtime = true, $dateFormat = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_start_date()' );
			return tribe_get_start_date( $postId, $showtime, $dateFormat );
		}
	}
	if ( !function_exists( 'sp_get_end_date' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_end_date( $postId = null, $showtime = 'true', $dateFormat = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_end_date()' );
			return tribe_get_end_date( $postId, $showtime, $dateFormat );
		}
	}
	if ( !function_exists( 'sp_get_cost' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_cost( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_cost()' );
			return tribe_get_cost( $postId );
		}
	}
	if ( !function_exists( 'sp_has_organizer' ) ) {
		/**
		 * @deprecated
		 */
		function sp_has_organizer( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_has_organizer()' );
			return tribe_has_organizer( $postId );
		}
	}
	if ( !function_exists( 'sp_get_organizer' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_organizer( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer()' );
			return tribe_get_organizer( $postId );
		}
	}
	if ( !function_exists( 'sp_get_organizer_email' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_organizer_email( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_email()' );
			return tribe_get_organizer_email( $postId );
		}
	}
	if ( !function_exists( 'sp_get_organizer_website' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_organizer_website( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_link( $postId, false )' );
			return tribe_get_organizer_link( $postId, false );
		}
	}
	if ( !function_exists( 'sp_get_organizer_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_organizer_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_link()' );
			return tribe_get_organizer_link( $postId );
		}
	}
	if ( !function_exists( 'sp_get_organizer_phone' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_organizer_phone( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_organizer_phone()' );
			return tribe_get_organizer_phone( $postId );
		}
	}
	if ( !function_exists( 'sp_has_venue' ) ) {
		/**
		 * @deprecated
		 */
		function sp_has_venue( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_venue_id()' );
			return tribe_get_venue_id( $postId );
		}
	}
	if ( !function_exists( 'sp_get_venue' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_venue( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_venue()' );
			return tribe_get_venue( $postId );
		}
	}
	if ( !function_exists( 'sp_get_country' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_country( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_country()' );
			return tribe_get_country( $postId );
		}
	}
	if ( !function_exists( 'sp_get_address' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_address( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_address()' );
			return tribe_get_address( $postId );
		}
	}
	if ( !function_exists( 'sp_get_city' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_city( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_city()' );
			return tribe_get_city( $postId );
		}
	}
	if ( !function_exists( 'sp_get_stateprovince' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_stateprovince( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_stateprovince()' );
			return tribe_get_stateprovince( $postId );
		}
	}
	if ( !function_exists( 'sp_get_state' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_state( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_state()' );
			return tribe_get_state( $postId );
		}
	}
	if ( !function_exists( 'sp_get_province' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_province( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_province()' );
			return tribe_get_province( $postId );
		}
	}
	if ( !function_exists( 'sp_get_zip' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_zip( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_zip()' );
			return tribe_get_zip( $postId );
		}
	}
	if ( !function_exists( 'sp_get_phone' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_phone( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_phone()' );
			return tribe_get_phone( $postId );
		}
	}
	if ( !function_exists( 'sp_previous_event_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_previous_event_link( $anchor = false ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_previous_event_link()' );
			return tribe_previous_event_link( $anchor );
		}
	}
	if ( !function_exists( 'sp_next_event_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_next_event_link( $anchor = false ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_next_event_link()' );
			return tribe_next_event_link( $anchor );
		}
	}
	if ( !function_exists( 'sp_post_id_helper' ) ) {
		/**
		 * @deprecated
		 */
		function sp_post_id_helper( $postId ) {
			_deprecated_function( __FUNCTION__, '2.0' );
			return TribeEvents::postIdHelper( $postId );
		}
	}
	if ( !function_exists( 'sp_is_new_event_day' ) ) {
		/**
		 * @deprecated
		 */
		function sp_is_new_event_day() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_new_event_day()' );
			return tribe_is_new_event_day();
		}
	}
	if ( !function_exists( 'sp_get_events' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_events( $args = '' ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
			return tribe_get_events( $args );
		}
	}
	if ( !function_exists( 'sp_is_past' ) ) {
		/**
		 * @deprecated
		 */
		function sp_is_past() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_past()' );
			return tribe_is_past();
		}
	}
	if ( !function_exists( 'sp_is_upcoming' ) ) {
		/**
		 * @deprecated
		 */
		function sp_is_upcoming() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_upcoming()' );
			return tribe_is_upcoming();
		}
	}
	if ( !function_exists( 'sp_is_month' ) ) {
		/**
		 * @deprecated
		 */
		function sp_is_month() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_month()' );
			return tribe_is_month();
		}
	}
	if ( !function_exists( 'sp_get_past_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_past_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_past_link()' );
			return tribe_get_past_link();
		}
	}
	if ( !function_exists( 'sp_get_upcoming_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_upcoming_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_upcoming_link()' );
			return tribe_get_upcoming_link();
		}
	}
	if ( !function_exists( 'sp_get_next_month_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_next_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_link()' );
			return tribe_get_next_month_link();
		}
	}
	if ( !function_exists( 'sp_get_previous_month_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_previous_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_link()' );
			return tribe_get_previous_month_link();
		}
	}
	if ( !function_exists( 'sp_get_month_view_date' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_month_view_date() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_month_view_date()' );
			return tribe_get_month_view_date();
		}
	}
	if ( !function_exists( 'sp_get_single_ical_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_single_ical_link() {
			if ( function_exists( 'tribe_get_single_ical_link' ) ) {
				_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_single_ical_link()' );
				return tribe_get_single_ical_link();
			}
		}
	}
	if ( !function_exists( 'sp_get_events_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_events_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_events()' );
			return tribe_get_events_link();
		}
	}
	if ( !function_exists( 'sp_get_gridview_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_gridview_link( $term = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gridview_link()' );
			return tribe_get_gridview_link( $term );
		}
	}
	if ( !function_exists( 'sp_get_listview_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_listview_link( $term = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_link()' );
			return tribe_get_listview_link( $term );
		}
	}
	if ( !function_exists( 'sp_get_listview_past_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_listview_past_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
			return tribe_get_listview_past_link();
		}
	}
	if ( !function_exists( 'sp_get_dropdown_link_prefix' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_dropdown_link_prefix() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_listview_past_link()' );
			return tribe_get_dropdown_link_prefix();
		}
	}
	if ( !function_exists( 'sp_get_ical_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_ical_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_ical_link()' );
			return tribe_get_ical_link();
		}
	}
	if ( !function_exists( 'sp_get_previous_month_text' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_previous_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_previous_month_text()' );
			return tribe_get_previous_month_text();
		}
	}
	if ( !function_exists( 'sp_get_current_month_text' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_current_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_current_month_text()' );
			return tribe_get_current_month_text();
		}
	}
	if ( !function_exists( 'sp_get_next_month_text' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_next_month_text() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_next_month_text()' );
			return tribe_get_next_month_text();
		}
	}
	if ( !function_exists( 'sp_get_displayed_month' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_displayed_month() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_displayed_month()' );
			return tribe_get_displayed_month();
		}
	}
	if ( !function_exists( 'sp_get_this_month_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_this_month_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_this_month_link()' );
			return tribe_get_this_month_link();
		}
	}
	if ( !function_exists( 'sp_get_region' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_region( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_region()' );
			return tribe_get_region( $postId );
		}
	}
	if ( !function_exists( 'sp_get_all_day' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_all_day( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_all_day()' );
			return tribe_get_all_day( $postId );
		}
	}
	if ( !function_exists( 'sp_is_multiday' ) ) {
		/**
		 * @deprecated
		 */
		function sp_is_multiday( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_multiday()' );
			return tribe_is_multiday( $postId );
		}
	}
	if ( !function_exists( 'sp_events_title' ) ) {
		/**
		 * @deprecated
		 */
		function sp_events_title() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_events_title()' );
			return tribe_events_title();
		}
	}
	if ( !function_exists( 'sp_meta_event_cats' ) ) {
		/**
		 * @deprecated
		 */
		function sp_meta_event_cats() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_meta_event_cats()' );
			return tribe_meta_event_cats();
		}
	}
	if ( !function_exists( 'sp_meta_event_category_name' ) ) {
		/**
		 * @deprecated
		 */
		function sp_meta_event_category_name() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_meta_event_category_name()' );
			return tribe_meta_event_category_name();
		}
	}
	if ( !function_exists( 'sp_get_add_to_gcal_link' ) ) {
		/**
		 * @deprecated
		 */
		function sp_get_add_to_gcal_link() {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_gcal_link()' );
			return tribe_get_gcal_link();
		}
	}
	if ( !function_exists( 'eventsGetOptionValue' ) ) {
		/**
		 * @deprecated
		 */
		function eventsGetOptionValue( $optionName ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_option()' );
			return tribe_get_option( $optionName );
		}
	}
	if ( !function_exists( 'events_by_month' ) ) {
		/**
		 * @deprecated
		 */
		function events_by_month( $results, $date ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_sort_by_month()' );
			return tribe_sort_by_month( $results, $date );
		}
	}
	if ( !function_exists( 'is_event' ) ) {
		/**
		 * @deprecated
		 */
		function is_event( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_is_event()' );
			return tribe_is_event( $postId );
		}
	}
	if ( !function_exists( 'getEventMeta' ) ) {
		/**
		 * @deprecated
		 */
		function getEventMeta( $id, $meta, $single = true ) {
			_deprecated_function( __FUNCTION__, '2.0', 'tribe_get_event_meta()' );
			return tribe_get_event_meta( $id, $meta, $single );
		}
	}
	if ( !function_exists( 'tribe_the_map_link' ) ) {
		/**
		 * @deprecated
		 */
		function tribe_the_map_link( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_map_link()' );
			echo tribe_get_map_link( $postId );
		}
	}
	if ( !function_exists( 'tribe_the_embedded_map' ) ) {
		/**
		 * @deprecated
		 */
		function tribe_the_embedded_map( $postId = null, $width = null, $height = null ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_embedded_map()' );
			echo tribe_get_embedded_map( $postId, $width, $height );
		}
	}
	if ( !function_exists( 'tribe_the_full_address' ) ) {
		/**
		 * @deprecated
		 */
		function tribe_the_full_address( $postId = null, $includeVenueName = false ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_full_address()' );
			echo tribe_get_full_address( $postId );
		}
	}
	if ( !function_exists( 'tribe_get_organizer_website' ) ) {
		/**
		 * @deprecated
		 */
		function tribe_get_organizer_website( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_organizer_link($postId,false)' );
			$output = tribe_get_organizer_link( $postId, false );
			return apply_filters( 'tribe_get_organizer_website', $output );
		}
	}
	if ( !function_exists( 'tribe_get_venue_permalink' ) ) {
		/**
		 * @deprecated
		 */
		function tribe_get_venue_permalink( $postId = null ) {
			_deprecated_function( __FUNCTION__, '2.0.1', 'echo tribe_get_venue_link($postId,false)' );
			return tribe_get_venue_link( $postId, false );
		}
	}
	if ( !function_exists( 'tribe_previous_event_link' ) ) {
		/**
		 * @deprecated
		 */
		function tribe_previous_event_link( $anchor = false ) {
			_deprecated_function( __FUNCTION__, '2.1', 'echo tribe_the_prev_event_link( $anchor )' );
			echo apply_filters( 'tribe_previous_event_link', tribe_get_prev_event_link( $anchor ) );
		}
	}
	if ( !function_exists( 'tribe_next_event_link' ) ) {
		/**
		 * @deprecated
		 */
		function tribe_next_event_link( $anchor = false ) {
			_deprecated_function( __FUNCTION__, '2.1', 'echo tribe_the_next_event_link( $anchor )' );
			echo apply_filters( 'tribe_next_event_link', tribe_get_next_event_link( $anchor ) );
		}
	}
	if ( !function_exists( 'display_day_title' ) ) {
		/**
		 * @deprecated
		 */
		function display_day_title( $day, $monthView, $date ) {
			_deprecated_function( __FUNCTION__, '2.1', 'echo tribe_get_display_day_title( $day, $monthView, $date )' );
			return tribe_get_display_day_title( $day, $monthView, $date );
		}
	}
	if ( !function_exists( 'display_day' ) ) {
		/**
		 * @deprecated
		 */
		function display_day( $day, $monthView ) {
			_deprecated_function( __FUNCTION__, '2.1', 'tribe_the_display_day( $day, $monthView )' );
			tribe_the_display_day( $day, $monthView );
		}
	}
	if ( !function_exists( 'tribe_meta_event_cats' ) ) {
		/**
		 * @deprecated
		 */
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
		/**
		 * @deprecated
		 */
		function tribe_get_all_day( $postId = null ) {
			_deprecated_function( __FUNCTION__, '3.0', 'tribe_event_is_all_day( $postId )' );
			return apply_filters( 'tribe_get_all_day', tribe_event_is_all_day( $postId ) );
		}
	}

	if ( !function_exists( 'tribe_is_multiday' ) ) {
		/**
		 * @deprecated
		 */
		function tribe_is_multiday( $postId = null ) {
			_deprecated_function( __FUNCTION__, '3.0', 'tribe_event_is_multiday( $postId )' );
			return apply_filters( 'tribe_is_multiday', tribe_event_is_multiday( $postId ) );
		}
	}


	/**** CALENDAR / GRID / MONTH VIEW DEPRECATED TAGS *****/

	/**
	 * Calendar Grid (Display)
	 *
	 * Display the full size grid calendar table
	 *
	 * @deprecated
	 * @uses load_template()
	 * @since 2.0
	 */
	function tribe_calendar_grid()  {
		_deprecated_function( __FUNCTION__, '3.0', 'tribe_show_month()' );
		return tribe_show_month();
	}

	/**
	 * Calendar Mini Grid (Display)
	 *
	 * Displays the mini grid calendar table (usually in a widget)
	 *
	 * @deprecated
	 * @uses load_template()
	 * @since 2.0
	 */
	function tribe_calendar_mini_grid() {
		_deprecated_function( __FUNCTION__, '3.0' );
	}

	/**
	 * Sort Events by Day
	 *
	 * Maps events to days of the month.
	 *
	 * @deprecated
	 * @param array $results Array of events from tribe_get_events()
	 * @param string $date
	 * @return array Days of the month with events as values
	 * @since 2.0
	 */
	function tribe_sort_by_month( $results, $date )  {
		_deprecated_function( __FUNCTION__, '3.0' );
	}

	/**
	 * Month / Year Dropdown Selector (Display)
	 *
	 * Display the year & month dropdowns. JavaScript in the resources/events-admin.js file will autosubmit on the change event.
	 *
	 * @deprecated
	 * @param string $prefix A prefix to add to the ID of the calendar elements.  This allows you to reuse the calendar on the same page.
	 * @param string|null $date
	 * @since 2.0
	 */
	function tribe_month_year_dropdowns( $prefix = '', $date = null )  {
		_deprecated_function( __FUNCTION__, '3.0' );
	}

	/**
	 * Link to This Month
	 *
	 * Returns a link to the currently displayed month (if in "jump to month" mode)
	 *
	 * @deprecated
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_this_month_link()  {
		_deprecated_function( __FUNCTION__, '3.0' );
	}

	/**
	 * Current Month Date
	 *
	 * Returns a formatted date string of the currently displayed month (in "jump to month" mode)
	 *
	 * @deprecated
	 * @return string Name of the displayed month.
	 * @since 2.0
	 */
	function tribe_get_displayed_month()  {
		_deprecated_function( __FUNCTION__, '3.0' );
	}

	/**
	 * @deprecated
	 */
	function tribe_get_display_day_title( $day, $monthView, $date ){
		_deprecated_function( __FUNCTION__, '3.0' );
	}

	/**
	 * @deprecated
	 */
	function tribe_the_display_day( $day, $monthView ){
		_deprecated_function( __FUNCTION__, '3.0' );
	}

	/**
	 * @deprecated
	 */
	function tribe_get_display_day( $day, $monthView ){
		_deprecated_function( __FUNCTION__, '3.0' );
	}


	/**** GENERAL DEPRECATED TAGS *****/

	/**
	 * tribe_get_object_property_from_array loop through an array of objects to retrieve a single property
	 *
	 * @deprecated
	 * @param array   $array_objects
	 * @param string  $property
	 * @return array
	 */
	function tribe_get_object_property_from_array( $array_objects = array(), $property = null ) {
		_deprecated_function( __FUNCTION__, '3.0', 'wp_list_pluck()' );
		return wp_list_pluck($array_objects, $property);
	}

	/**** WIDGET DEPRECATED TAGS *****/

	/**
	 * @deprecated
	 */
	function tribe_mini_display_day( $day, $monthView ) {
		_deprecated_function( __FUNCTION__, '3.0' );
	}


}
