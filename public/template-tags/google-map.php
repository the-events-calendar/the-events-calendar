<?php
/**
 * The Events Calendar Template Tags
 *
 * Display functions for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * Returns a url to google maps for the given event
	 *
	 * @param string $postId 
	 * @return string a fully qualified link to http://maps.google.com/ for this event
	 * @since 2.0
	 */
	function tribe_get_map_link( $postId = null )  {
		$tribe_ecp = TribeEvents::instance();
		$output = esc_url($tribe_ecp->googleMapLink( $postId ));
		return apply_filters( 'tribe_get_map_link', $output );
	}

	/**
	 * Returns an embedded google maps for an event
	 *
	 * @param string $postId 
	 * @param int $width 
	 * @param int $height
	 * @param bool $force_load
	 * @return string - an iframe pulling http://maps.google.com/ for this event
	 * @since 2.0
	 */
	function tribe_get_embedded_map( $postId = null, $width = '', $height = '', $force_load = false )  {
		$postId = TribeEvents::postIdHelper( $postId );
		if ( !tribe_is_venue( $postId ) && !tribe_is_event( $postId ) ) {
			return false;
		}

		$postId = tribe_is_venue( $postId ) ? $postId : tribe_get_venue_id( $postId );
		$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
		$toUrlEncode = "";

		foreach( $locationMetaSuffixes as $val ) {
			$metaVal = call_user_func('tribe_get_'.$val);
			if ( $metaVal ) 
				$toUrlEncode .= $metaVal . " ";
		}

		if ( $toUrlEncode ) 
			$address = $toUrlEncode;
		else
			$address = null;		


		if (!$height) $height = tribe_get_option('embedGoogleMapsHeight','350');
		if (!$width) $width = tribe_get_option('embedGoogleMapsWidth','100%');

		if ($address || $force_load) {
			ob_start();
			include(TribeEvents::instance()->pluginPath.'admin-views/event-map.php');
			$google_map = ob_get_contents();
			ob_get_clean();
			return $google_map;
		}
		else return '';
	}

	/**
	 * Check if embed google map is enabled for this event.
	 *
     * @param int $postId id of the post, if none specified, current post is used
	 * @return bool true if google map option is set to embed the map
	 * @since 2.0
	 */
	function tribe_embed_google_map($postId = null) {
		$postId = TribeEvents::postIdHelper( $postId );
		return get_post_meta( get_the_ID(), '_EventShowMap', 1) == 1;
	}

	/**
	 * Check if google map link is enabled for this event
	 *
     * @param int $postId id of the post, if none specified, current post is used
	 * @return bool true if google map link is set to display the event
	 * @since 2.0
	 */
	function tribe_show_google_map_link($postId = null) {
		return get_post_meta( get_the_ID(), '_EventShowMapLink', 1) == 1;
	}

}
?>