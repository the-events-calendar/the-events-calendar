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