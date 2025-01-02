<?php
/**
 * Google Maps Integration
 *
 * Display functions (template-tags) for use in WordPress templates.
 */


/**
 * Determines if the current site is using The Events Calendar's default Google Maps API
 * Key, in which case only basic Maps Embed API requests are allowed.
 *
 * See https://developers.google.com/maps/documentation/embed/usage-and-billing#embed for
 * more info.
 *
 * @since 4.6.24
 *
 * @return boolean
 */
function tribe_is_using_basic_gmaps_api() {
	$user_api_key = (string) tribe_get_option( Tribe__Events__Google__Maps_API_Key::$api_key_option_name );
	$tec_api_key  = (string) Tribe__Events__Google__Maps_API_Key::$default_api_key;

	/**
	 * Filters whether or not only basic Google Maps Embed API requests are allowed on this site, which happens
	 * when the site is using The Events Calendar's default Google Maps API key.
	 *
	 * @since 4.6.24
	 *
	 * @param boolean $using_basic_maps_api Whether basic Google Maps Embed API requests are allowed on this site.
	 * @param string $user_api_key The value of the Google Maps API Key setting in TEC.
	 * @param string $tec_api_key The default Google Maps API Key provided by The Events Calendar for basic functionality.
	 */
	return apply_filters( 'tribe_is_using_basic_gmaps_api', $user_api_key === $tec_api_key, $user_api_key, $tec_api_key );
}

/**
 * Google Map Link
 *
 * Returns a URL to Google Maps for the given event.
 * 
 * @since 4.6.24
 *
 * @category Events
 *
 * @param int $post_id The event post ID.
 *
 * @return string A fully qualified link to https://maps.google.com/ for this event.
 */
function tribe_get_map_link( $post_id = null ) {
	if ( $post_id === null || ! is_numeric( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}

	$locationMetaSuffixes = [ 'address', 'city', 'region', 'zip', 'country' ];
	$to_encode = '';
	$url = '';

	foreach ( $locationMetaSuffixes as $val ) {
		$metaVal = call_user_func( 'tribe_get_' . $val, $post_id );
		if ( $metaVal ) {
			$to_encode .= $metaVal . ' ';
		}
	}

	if ( $to_encode ) {
		$url = 'https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . urlencode( trim( $to_encode ) );
	}

	/**
	 * Allows filtering the Google Maps URL for the given event.
	 * 
	 * @since 4.6.24
	 * 
	 * @param string $url     The URL to Google Maps.
	 * @param int    $post_id The event post ID.
	 */
	$url = apply_filters( 'tribe_events_google_map_link', $url, $post_id );
	$output = esc_url( $url );

	/**
	 * Allows filtering the escaped Google Maps URL for the given event.
	 * 
	 * @since 4.6.24
	 * 
	 * @param string $output The escaped URL to Google Maps.
	 */
	return apply_filters( 'tribe_get_map_link', $output );
}

/**
 * Returns a formed HTML link to Google Maps for the given event.
 * 
 * @since 4.6.24
 *
 * @category Events
 *
 * @param int $postId The event post ID.
 *
 * @return string A fully qualified link to https://maps.google.com/ for this event.
 */
function tribe_get_map_link_html( $postId = null ) {
	$map_link = esc_url( tribe_get_map_link( $postId ) );

	$link = '';

	if ( ! empty( $map_link ) ) {
		$link = sprintf(
			'<a class="tribe-events-gmap" href="%s" title="%s" target="_blank" rel="noreferrer noopener">%s</a>',
			$map_link,
			esc_html__( 'Click to view a Google Map', 'the-events-calendar' ),
			esc_html__( '+ Google Map', 'the-events-calendar' )
		);
	}

	/**
	 * Allows filtering the formed HTML link to Google Maps for the given event.
	 * 
	 * @since 4.6.24
	 * 
	 * @param string $link The fully formed HTML link to Google Maps.
	 */
	return apply_filters( 'tribe_get_map_link_html', $link );
}

/**
 * Returns an embedded Google Maps map for an event.
 * 
 * @since 4.6.24
 *
 * @category Events
 *
 * @param id   $post_id    The event post ID.
 * @param int  $width      The width of the iframe containing the map in pixels.
 * @param int  $height     The height of the iframe containing the map in pixels.
 * @param bool $force_load If true, then load the map even if an address is not provided.
 *
 * @return string An iframe pulling https://maps.google.com/ for this event.
 */
function tribe_get_embedded_map( $post_id = null, $width = null, $height = null, $force_load = false ) {
	return Tribe__Events__Embedded_Maps::instance()->get_map( $post_id, $width, $height, $force_load );
}

/**
 * Check if the "Show Map" option is enabled for the event or venue.
 *
 * @category Events
 *
 * @param int $post_id The Post ID. If none specified, current post is used.
 *
 * @return bool True if the "Show Map" option is enabled.
 */
function tribe_embed_google_map( $post_id = null ) {

	$output    = false;
	$post_id   = Tribe__Events__Main::postIdHelper( $post_id );
	$post_type = get_post_type( $post_id );

	if ( tribe_get_option( 'embedGoogleMaps', true ) ) {
		if ( $post_type == Tribe__Events__Main::POSTTYPE ) {
			$output = tribe_is_truthy( get_post_meta( $post_id, '_EventShowMap', 1 ) );
		} elseif ( $post_type == Tribe__Events__Main::VENUE_POST_TYPE ) {
			$output = tribe_is_truthy( get_post_meta( $post_id, '_VenueShowMap', 1 ) );
		}
	}

	/**
	 * Allows filtering the "Show Map" setting globally.
	 *
	 * @since 4.6.24
	 *
	 * @param bool $output The value of the "Show Map" setting.
	 */
	return apply_filters( 'tribe_embed_google_map', $output );
}

/**
 * Check if the "Show Map Link" option is enabled for the event or venue.
 *
 * @since 4.6.24
 *
 * @category Events
 *
 * @param int $post_id The post ID. If none specified, current post is used.
 *
 * @return bool True if the "Show Map Link" option is enabled.
 */
function tribe_show_google_map_link( $post_id = null ) {

	$output    = false;
	$post_id   = Tribe__Events__Main::postIdHelper( $post_id );
	$post_type = get_post_type( $post_id );

	if ( $post_type == Tribe__Events__Main::POSTTYPE ) {
		$output = tribe_is_truthy( get_post_meta( $post_id, '_EventShowMapLink', 1 ) );
	} elseif ( $post_type == Tribe__Events__Main::VENUE_POST_TYPE ) {
		$output = tribe_is_truthy( get_post_meta( $post_id, '_VenueShowMapLink', 1 ) );
	}

	/**
	 * Allows filtering the "Show Map Link" setting globally.
	 *
	 * @since 4.6.24
	 *
	 * @param bool $output The value of the "Show Map Link" setting.
	 */
	return apply_filters( 'tribe_show_google_map_link', $output );
}

/**
 * Gets a full URL for a basic Google Maps embed.
 * See https://developers.google.com/maps/documentation/embed/guide for more info.
 *
 * @since 4.6.24
 *
 * @param string $address_string The full address for the marker to be shown on the map (e.g. an event venue).
 * 
 * @return string The full URL for a basic Google Maps embed.
 */
function tribe_get_basic_gmap_embed_url( $address_string ) {

	$api_key = tribe_get_option( Tribe__Events__Google__Maps_API_Key::$api_key_option_name, Tribe__Events__Google__Maps_API_Key::$default_api_key );

	$embed_url_args = [
		'key'  => $api_key,
		'q'    => urlencode( $address_string ),
		'zoom' => (int) tribe_get_option( 'embedGoogleMapsZoom', 15 ),
	];

	$embed_url = add_query_arg(
		/**
		 * Allows filtering the URL parameters passed to the basic Google Maps embed URL via add_query_arg().
		 * See https://developers.google.com/maps/documentation/embed/guide for all available URL parameters.
		 *
		 * @since 4.6.24
		 *
		 * @param array $embed_url_args The URL parameters being passed to the Google Maps embed URL.
		 */
		apply_filters( 'tribe_get_basic_gmap_embed_url_args', $embed_url_args ),
		/**
		 * Allows filtering the root Google Maps URL used for the basic map embeds; determines what Map Mode is used.
		 * See https://developers.google.com/maps/documentation/embed/guide for available map modes.
		 *
		 * @since 4.6.24
		 *
		 * @param string $gmaps_embed_url The root Google Maps embed URL.
		 */
		apply_filters( 'tribe_get_basic_gmap_embed_url', 'https://www.google.com/maps/embed/v1/place' )
	);

	return $embed_url;
}
