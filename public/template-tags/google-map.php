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

if ( class_exists( 'TribeEvents' ) ) {

	/**
	 * Google Map Link
	 *
	 * Returns a url to google maps for the given event
	 *
	 * @param string $postId
	 *
	 * @return string A fully qualified link to http://maps.google.com/ for this event
	 */
	function tribe_get_map_link( $postId = null ) {
		$tribe_ecp = TribeEvents::instance();
		$output    = esc_url( $tribe_ecp->googleMapLink( $postId ) );

		return apply_filters( 'tribe_get_map_link', $output );
	}

	/**
	 * Returns a formed HTML link to Google Maps for the given event.
	 *
	 *
	 *
	 * @param string $postId
	 *
	 * @return string A fully qualified link to http://maps.google.com/ for this event
	 */
	function tribe_get_map_link_html( $postId = null ) {
		$link = sprintf(
			'<a class="tribe-events-gmap" href="%s" title="%s" target="_blank">%s</a>',
			tribe_get_map_link( $postId ),
			__( 'Click to view a Google Map', 'tribe-events-calendar' ),
			__( '+ Google Map', 'tribe-events-calendar' )
		);

		// @todo remove tribe_event_meta_gmap_link in 3.7
		return apply_filters( 'tribe_get_map_link_html', apply_filters( 'tribe_event_meta_gmap_link', $link ) );
	}

	/**
	 * Google Map Embed
	 *
	 * Returns an embedded google maps for an event
	 *
	 * @param string $post_id
	 * @param int    $width
	 * @param int    $height
	 * @param bool   $force_load If true, then load the map even if an address is not provided.
	 *
	 * @return string An iframe pulling http://maps.google.com/ for this event
	 */
	function tribe_get_embedded_map( $post_id = null, $width = null, $height = null, $force_load = false ) {
		$post_id = TribeEvents::postIdHelper( $post_id );

		if ( ! tribe_is_venue( $post_id ) && ! tribe_is_event( $post_id ) ) {
			return apply_filters( 'tribe_get_embedded_map', false );
		}

		$venue_id  = tribe_is_venue( $post_id ) ? $post_id : tribe_get_venue_id( $post_id );
		$location_parts = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
		$address = '';

		// Form the address string for the map
		foreach ( $location_parts as $val ) {
			$metaVal = call_user_func( 'tribe_get_' . $val );
			if ( $metaVal ) {
				$address .= $metaVal . ' ';
			}
		}

		if ( empty( $address ) && ! $force_load ) {
			return apply_filters( 'tribe_get_embedded_map', '' );
		}

		// Generate the HTML used to "house"  the map
		ob_start();

		tribe_get_template_part( 'modules/map', null, array(
			'width' => null === $width ? tribe_get_option( 'embedGoogleMapsWidth', '100%' ) : $width,
			'height' => null === $height ? tribe_get_option( 'embedGoogleMapsHeight', '350' ) : $height,
		) );

		$html = ob_get_clean();

		// Google Maps API (URL can be overridden)
		$url = apply_filters( 'tribe_events_google_maps_api_url', '//maps.googleapis.com/maps/api/js' );
		wp_enqueue_script( 'tribe_events_google_maps_api', $url, array(), false, true );

		// Setup the support scripts/data
		$resources_url = trailingslashit( TribeEvents::instance()->pluginUrl ) . 'resources/';
		$url = Tribe_Template_Factory::getMinFile( $resources_url . 'embedded-map.js', true );

		wp_enqueue_script( 'tribe_events_embedded_map', $url, array(), false, true );
		wp_localize_script( 'tribe_events_embedded_map', 'tribeEventsEmbeddedMap', array(
			'address' => $address,
			'title' => json_encode( tribe_get_venue( $venue_id ) ),
			'zoom' => tribe_get_option( 'embedGoogleMapsZoom', '10' )
		) );

		return apply_filters( 'tribe_get_embedded_map', $html );
	}

	/**
	 * Google Map Embed Test
	 *
	 * Check if embed google map is enabled for this event (or venue ).
	 *
	 * @param int $postId Id of the post, if none specified, current post is used
	 *
	 * @return bool True if google map option is set to embed the map
	 */
	function tribe_embed_google_map( $postId = null ) {

		$output    = false;
		$postId    = TribeEvents::postIdHelper( $postId );
		$post_type = get_post_type( $postId );

		if ( tribe_get_option( 'embedGoogleMaps', true ) ) {
			if ( $post_type == TribeEvents::POSTTYPE ) {
				$output = get_post_meta( $postId, '_EventShowMap', 1 ) == 1;
			} elseif ( $post_type == TribeEvents::VENUE_POST_TYPE ) {
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
	 * @param int $postId Id of the post, if none specified, current post is used
	 *
	 * @return bool True if google map link is set to display the event
	 */
	function tribe_show_google_map_link( $postId = null ) {

		$output    = false;
		$postId    = TribeEvents::postIdHelper( $postId );
		$post_type = get_post_type( $postId );

		if ( $post_type == TribeEvents::POSTTYPE ) {
			$output = get_post_meta( $postId, '_EventShowMapLink', 1 ) == 1;
		} elseif ( $post_type == TribeEvents::VENUE_POST_TYPE ) {
			$output = get_post_meta( $postId, '_VenueShowMapLink', 1 ) !== 'false' ? 1 : 0;
		}

		return apply_filters( 'tribe_show_google_map_link', $output );
	}

}
?>
