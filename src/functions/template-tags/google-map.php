<?php
/**
 * Google Maps Integration
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Google Map Link
	 *
	 * Returns a url to google maps for the given event
	 *
	 * @category Events
	 *
	 * @param string $postId
	 *
	 * @return string A fully qualified link to http://maps.google.com/ for this event
	 */
	function tribe_get_map_link( $postId = null ) {
		$tribe_ecp = Tribe__Events__Main::instance();
		$output    = esc_url( $tribe_ecp->googleMapLink( $postId ) );

		return apply_filters( 'tribe_get_map_link', $output );
	}

	/**
	 * Returns a formed HTML link to Google Maps for the given event.
	 *
	 * @category Events
	 *
	 * @param string $postId
	 *
	 * @return string A fully qualified link to http://maps.google.com/ for this event
	 */
	function tribe_get_map_link_html( $postId = null ) {
		$link = sprintf(
			'<a class="tribe-events-gmap" href="%s" title="%s" target="_blank">%s</a>',
			esc_url( tribe_get_map_link( $postId ) ),
			__( 'Click to view a Google Map', 'the-events-calendar' ),
			__( '+ Google Map', 'the-events-calendar' )
		);

		return apply_filters( 'tribe_get_map_link_html', $link );
	}

	/**
	 * Google Map Embed
	 *
	 * Returns an embedded google maps for an event
	 *
	 * @category Events
	 *
	 * @param string $post_id
	 * @param int    $width
	 * @param int    $height
	 * @param bool   $force_load If true, then load the map even if an address is not provided.
	 *
	 * @return string An iframe pulling http://maps.google.com/ for this event
	 */
	function tribe_get_embedded_map( $post_id = null, $width = null, $height = null, $force_load = false ) {
		return Tribe__Events__Embedded_Maps::instance()->get_map( $post_id, $width, $height, $force_load );
	}

	/**
	 * Google Map Embed Test
	 *
	 * Check if embed google map is enabled for this event (or venue ).
	 *
	 * @category Events
	 *
	 * @param int $postId Id of the post, if none specified, current post is used
	 *
	 * @return bool True if google map option is set to embed the map
	 */
	function tribe_embed_google_map( $postId = null ) {

		$output    = false;
		$postId    = Tribe__Events__Main::postIdHelper( $postId );
		$post_type = get_post_type( $postId );

		if ( tribe_get_option( 'embedGoogleMaps', true ) ) {
			if ( $post_type == Tribe__Events__Main::POSTTYPE ) {
				$output = get_post_meta( $postId, '_EventShowMap', 1 ) == 1;
			} elseif ( $post_type == Tribe__Events__Main::VENUE_POST_TYPE ) {
				$output = get_post_meta( $postId, '_VenueShowMap', 1 ) !== 'false' ? 1 : 0;
			}
		}

		return apply_filters( 'tribe_embed_google_map', $output );
	}

	/**
	 * Google Map Link Test
	 *
	 * Check if google map link is enabled for this event
	 *
	 * @category Events
	 *
	 * @param int $postId Id of the post, if none specified, current post is used
	 *
	 * @return bool True if google map link is set to display the event
	 */
	function tribe_show_google_map_link( $postId = null ) {

		$output    = false;
		$postId    = Tribe__Events__Main::postIdHelper( $postId );
		$post_type = get_post_type( $postId );

		if ( $post_type == Tribe__Events__Main::POSTTYPE ) {
			$output = get_post_meta( $postId, '_EventShowMapLink', 1 ) == 1;
		} elseif ( $post_type == Tribe__Events__Main::VENUE_POST_TYPE ) {
			$output = get_post_meta( $postId, '_VenueShowMapLink', 1 ) !== 'false' ? 1 : 0;
		}

		return apply_filters( 'tribe_show_google_map_link', $output );
	}
}
