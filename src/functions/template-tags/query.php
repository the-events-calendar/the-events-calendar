<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Conditional tag to check if current page is an event category page
	 *
	 * @return bool
	 **/
	function tribe_is_event_category() {
		global $wp_query;
		$tribe_is_event_category = ! empty( $wp_query->tribe_is_event_category );

		return apply_filters( 'tribe_query_is_event_category', $tribe_is_event_category );
	}

	/**
	 * Conditional tag to check if current page is an event venue page
	 *
	 * @return bool
	 **/
	function tribe_is_event_venue() {
		global $wp_query;
		$tribe_is_event_venue = ! empty( $wp_query->tribe_is_event_venue );

		return apply_filters( 'tribe_query_is_event_venue', $tribe_is_event_venue );
	}

	/**
	 * Conditional tag to check if current page is an event organizer page
	 *
	 * @return bool
	 **/
	function tribe_is_event_organizer() {
		global $wp_query;
		$tribe_is_event_organizer = ! empty( $wp_query->tribe_is_event_organizer );

		return apply_filters( 'tribe_query_is_event_organizer', $tribe_is_event_organizer );
	}

	/**
	 * Conditional tag to check if current page is displaying event query
	 *
	 * @return bool
	 **/
	function tribe_is_event_query() {
		global $wp_query;
		$tribe_is_event_query = ! empty( $wp_query->tribe_is_event_query );

		return apply_filters( 'tribe_query_is_event_query', $tribe_is_event_query );
	}
}

if ( ! function_exists( 'tribe_is_community_my_events_page' ) ) {
	/**
	 * Tests if the current page is the My Events page
	 *
	 * @return bool whether it is the My Events page.
	 * @since 1.0.1
	 */
	function tribe_is_community_my_events_page() {
		if ( ! class_exists( 'Tribe__Events__Community__Main' ) ) {
			return false;
		}

		return Tribe__Events__Community__Main::instance()->isMyEvents;
	}
}

if ( ! function_exists( 'tribe_is_community_edit_event_page' ) ) {
	/**
	 * Tests if the current page is the Edit Event page
	 *
	 * @return bool whether it is the Edit Event page.
	 * @author Paul Hughes
	 * @since 1.0.1
	 */
	function tribe_is_community_edit_event_page() {
		if ( ! class_exists( 'Tribe__Events__Community__Main' ) ) {
			return false;
		}


		return Tribe__Events__Community__Main::instance()->isEditPage;
	}
}
