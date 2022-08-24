<?php
/**
 * Conditional tag to check if current page is an event category page
 *
 * @return bool
 **/
function tribe_is_event_category() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$tribe_is_event_category = ! empty( $wp_query->tribe_is_event_category );

	return apply_filters( 'tribe_query_is_event_category', $tribe_is_event_category );
}

/**
 * Conditional tag to check if current page is an event venue page
 *
 * @return bool
 **/
function tribe_is_event_venue() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$tribe_is_event_venue = ! empty( $wp_query->tribe_is_event_venue );

	return apply_filters( 'tribe_query_is_event_venue', $tribe_is_event_venue );
}

/**
 * Conditional tag to check if current page is an event organizer page
 *
 * @return bool
 **/
function tribe_is_event_organizer() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$tribe_is_event_organizer = ! empty( $wp_query->tribe_is_event_organizer );

	return apply_filters( 'tribe_query_is_event_organizer', $tribe_is_event_organizer );
}

/**
 * Conditional tag to check if current page is displaying event query
 *
 * @return bool
 **/
function tribe_is_event_query() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$tribe_is_event_query = ! empty( $wp_query->tribe_is_event_query );

	return apply_filters( 'tribe_query_is_event_query', $tribe_is_event_query );
}