<?php
/**
 * Events Calendar Pro iCal Template Tags
 *
 * Display functions for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {

	/**
	 * Returns the link to export the whole recurring  series in iCal format.
	 *
	 * @param int|WP_Pos|null $event_id A event post object, an event post ID or null to use the globally defined post object.
	 *
	 * @return string The absolute URL to export the whole recurring series in iCal format.
	 */
	function tribe_get_recurrence_ical_link( $event_id = null ) {
		$event_id = Tribe__Events__Main::postIdHelper( $event_id );

		if ( empty( $event_id ) || ! tribe_is_event( $event_id ) ) {
			return '';
		}

		$event     = get_post( $event_id );
		$parent_id = empty( $event->post_parent ) ? $event_id : $event->post_parent;
		$url      = get_permalink( $parent_id );
		$url_vars  = array( 'ical' => '1' );

		if ( tribe_is_recurring_event( $parent_id ) ) {
			$child_events_ids = tribe_get_events( array(
				'fields'      => 'ids',
				'post_parent' => $parent_id
			) );

			$event_ids = array_merge( array( $parent_id ), $child_events_ids );

			$url_vars['event_ids'] = implode( ',', $event_ids );
		}

		$url = add_query_arg( $url_vars, $url );

		return apply_filters( 'tribe_get_recurrence_ical_link', $url, $event_id );
	}
}
