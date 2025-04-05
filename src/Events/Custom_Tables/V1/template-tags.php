<?php
/**
 * The plugin template tags.
 *
 * @since   6.0.0
 */

use TEC\Events_Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Events_Pro\Custom_Tables\V1\Series\Post_Type as Series;

/**
 * Whether a post is a valid Event Series or not.
 *
 * @since 6.0.0
 *
 * @param int|WP_Post $post_id The post ID or object to check.
 *
 * @return bool Whether the post is an Event Series or not.
 */
function tribe_is_event_series( $post_id ) {
	return Series::POSTTYPE === get_post_type( $post_id );
	/*
	 * @todo add some model checks here
	return get_post_type( $post_id ) === Event_Series::POST_TYPE
	       && TEC\Events\Custom_Tables\V1\Models\Event_Series::find_by_post_id($post_id) instanceof TEC\Events\Custom_Tables\V1\Models\Event_Series;
	*/
}

/**
 * Return the first series associated with an event, if the event is private make sure to return `null` if the user
 * is not logged in.
 *
 * TODO: A more flexible approach to get the nth() series of an event or N series of an event.
 *
 * @since 6.0.0
 *
 * @param int $event_post_id The ID of the post ID event we are looking for.
 *
 * @return WP_Post|null The post representing the series otherwise `null`
 */
function tec_event_series( $event_post_id ) {
	$cache = tribe_cache();
	$cache_key = Series_Relationship::get_cache_key( $event_post_id );

	if ( isset( $cache[ $cache_key ] ) ) {
		$relationship = $cache[ Series_Relationship::get_cache_key( $event_post_id ) ];
	} else {
		$relationship = Series_Relationship::where( 'event_post_id', $event_post_id )->first();
		$cache[ $cache_key ] = $relationship;
	}

	if ( ! $relationship instanceof Series_Relationship ) {
		return null;
	}

	$series = get_post( $relationship->series_post_id );

	if ( ! $series instanceof WP_Post ) {
		return null;
	}

	// Show private series only if the user is logged in.
	if ( 'private' === $series->post_status && is_user_logged_in() ) {
		return $series;
	}

	// Status considered invalid, meaning those post_status indicate a non relationship for public visibility.
	$invalid_status = [
		'draft'   => true,
		'pending' => true,
		'future'  => true,
		'trash'   => true,
	];

	if ( isset( $invalid_status[ $series->post_status ] ) ) {
		return null;
	}

	return $series;
}

/**
 * Determines if we should show the series title in the series marker.
 *
 * @since 6.0.0
 *
 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
 *
 * @return boolean
 */
function tec_should_show_series_title( $series = null, $event = null ) {
	$show_title = false;
	if ( is_numeric( $series ) ) {
		$series = get_post( $series );
	}

	// If we have the series, check and see if the editor checkbox has been toggled.
	if ( ! empty( $series->ID ) ) {
		$show_title = (bool) get_post_meta( $series->ID, '_tec-series-show-title', true );
	}

	/**
	 * Allows filtering whether to show the series event title in the series marker.
	 *
	 * @6.0.0
	 *
	 * @param boolean          $show_title Should we (visually) hide the title.
	 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
	 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
	 */
	return apply_filters( 'tec_events_custom_tables_v1_show_series_title', $show_title, $series, $event );
}

/**
 * Generates a list of classes for the marker label.
 *
 * @since 6.0.0
 *
 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
 *
 * @return array<string> $classes A list of classes for the marker label.
 */
function tec_get_series_marker_label_classes( $series = null, $event = null  ) {
	$classes = [ 'tec_series_marker__title' ];

	/**
	 * If this returns false, we  hide the series marker event title.
	 * (via the `tribe-common-a11y-visual-hide` class which leaves the title for screen readers for additional context.)
	 */
	if ( ! tec_should_show_series_title( $series, $event ) ) {
		$classes[] = 'tribe-common-a11y-visual-hide';
	}

	/**
	 * Allows filtering the series title classes.
	 *
	 * @6.0.0
	 *
	 * @param array<string> A list of classes to apply to the series title.
	 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
	 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
	 */
	return apply_filters( 'tec_events_custom_tables_v1_series_marker_label_classes', $classes, $series, $event );
}
