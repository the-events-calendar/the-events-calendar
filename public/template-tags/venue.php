<?php
/**
 * Events Calendar Pro Week Template Tags
 *
 * Display functions for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if( class_exists('TribeEventsPro')) {

	/**
	 * Output the upcoming events associated with a venue
	 *
	 * @return void
	 * @since 3.0
	 */
	function tribe_venue_upcoming_events( $post_id = false ) {

		$post_id = TribeEvents::postIdHelper( $post_id );

		if ($post_id) {
			// turn off the venue group
			tribe_set_the_meta_visibility( 'tribe_event_venue', false, 'meta_group');

			$args = array(
				'venue' => $post_id,
				'eventDisplay' => 'upcoming' );

			$html = tribe_include_view_list( $args );

			// housekeeping: turn on the venue meta group before we leave
			tribe_set_the_meta_visibility( 'tribe_event_venue', true, 'meta_group');
			return apply_filters( 'tribe_venue_upcoming_events', $html );
		}
	}
}