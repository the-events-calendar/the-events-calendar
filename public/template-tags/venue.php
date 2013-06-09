<?php
/**
 * Venue Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * Venue ID
	 *
	 * Returns the event Venue ID.
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 * @return int Venue ID
	 * @since 2.0
	 */
	function tribe_get_venue_id( $postId = null ) {
		$postId = TribeEvents::postIdHelper( $postId );
		if ( tribe_is_venue( $postId ) ) {
			return $postId;
		} else {
			return apply_filters('tribe_get_venue_id', tribe_get_event_meta( $postId, '_EventVenueID', true ));
		}
	}

	/**
	 * Venue Test
	 *
	 * Returns true or false depending on if the post id for the event has a venue or if the post id is a venue
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return bool
	 * @since 2.0
	 */
	function tribe_has_venue( $postId = null) {
		$has_venue = (tribe_get_venue_id( $postId ) > 0 ) ? true : false;
		return apply_filters('tribe_has_venue', $has_venue);
	}

	/**
	 * Get Venue
	 *
	 * Returns the event venue name
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @param bool $with_link (deprecated in 2.0.1)
	 * @return string Venue Name
	 * @since 2.0
	 */
	function tribe_get_venue( $postId = null, $with_link = false )  {
		if ( $with_link ) {	_deprecated_argument( __FUNCTION__, '2.0.1' ); }
		$postId = tribe_get_venue_id( $postId );
		$venue = ($postId > 0) ? esc_html(get_post( $postId )->post_title) : null;
		return apply_filters('tribe_get_venue', $venue);
	}

	/**
	 * Venue Link
	 *
	 * Returns or display the event Venue Name with a link to the venue
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @param bool $display If true displays full html links around venue's name, if false returns just the link without displaying it
	 * @return string Venue if $display is set to false, void if it's set to true.
	 * @since 2.0
	 */
	function tribe_get_venue_link( $postId = null, $display = true )  {

		$url = '';

		if ( $venue_id = tribe_get_venue_id( $postId ) ) {
			$url = esc_url( get_permalink( $venue_id ) );
		}

		if( $display && $url != '' ) {
			$venue_name = tribe_get_venue($postId);
			$link = '<a href="'.$url.'">'.$venue_name.'</a>';
		} else {
			$link = $url;
		}
		$link = apply_filters( 'tribe_get_venue_link', $link, $postId, $display, $url );

		if ( $display ) {
			echo $link;
		} else {
			return $link;
		}
	}

	/**
	 * Country
	 *
	 * Returns the event country
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string Country
	 * @since 2.0
	 */
	function tribe_get_country( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueCountry', true ) );
		return apply_filters('tribe_get_country', $output);
	}

	/**
	 * Full Address
	 *
	 * Returns the full address for the venue. Function uses the views/full-address.php template which you can override in your theme (including google microformats etc).
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @param bool $includeVenueName
	 * @return string Formatted event address
	 * @since 2.0
	 */
	function tribe_get_full_address( $postId = null, $includeVenueName = false )  {
		$postId = tribe_get_venue_id( $postId );
		$tribe_ecp = TribeEvents::instance();
		return apply_filters('tribe_get_full_address', $tribe_ecp->fullAddress( $postId, $includeVenueName ) );
	}

	/**
	 * Address Test
	 *
	 * Returns true if any of the following exist: address, city, state/province (region), country or zip
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return bool True if any part of an address exists
	 * @since 2.0
	 */
	function tribe_address_exists( $postId = null )  {
		if (
			tribe_get_address( $postId ) ||
			tribe_get_city( $postId ) ||
			tribe_get_region( $postId ) ||
			tribe_get_country( $postId ) ||
			tribe_get_zip( $postId )
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Street Address
	 *
	 * Returns the venue street address
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string Street address
	 * @since 2.0
	 */
	function tribe_get_address( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueAddress', true ) );
		return apply_filters('tribe_get_address', $output);
	}

	/**
	 * City
	 *
	 * Returns the venue city
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string City
	 * @since 2.0
	 */
	function tribe_get_city( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueCity', true ) );
		return apply_filters('tribe_get_city', $output);
	}

	/**
	 * State or Province
	 *
	 * Returns the venue state or province
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string State
	 * @todo Depricate tribe_get_stateprovince or tribe_get_region
	 * @since 2.0
	 */
	function tribe_get_stateprovince( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueStateProvince', true ) );
		return apply_filters('tribe_get_stateprovince', $output);
	}

	/**
	 * State
	 *
	 * Returns the venue state
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string State
	 * @since 2.0
	 */
	function tribe_get_state( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueState', true ) );
		return apply_filters('tribe_get_state', $output);
	}

	/**
	 * Province
	 *
	 * Returns the venue province
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string Province
	 * @since 2.0
	 */
	function tribe_get_province( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueProvince', true ) );
		return apply_filters('tribe_get_province', $output);
	}

	/**
	 * Region
	 *
	 * Returns the state or province for US or non-US addresses (effectively the same thing as tribe_get_stateprovince())
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string
	 * @todo Depricate tribe_get_region or tribe_get_stateprovince
	 * @since 2.0
	 */
	function tribe_get_region( $postId = null )  {
		$postId = tribe_get_venue_id( $postId );
		if(tribe_get_event_meta($postId, '_VenueStateProvince', true )){
			$region = tribe_get_event_meta($postId, '_VenueStateProvince', true );
		} else {
			if ( tribe_get_country($postId) == __('United States', 'tribe-events-calendar' ) ) {
				$region = tribe_get_state($postId);
			} else {
				$region = tribe_get_province();
			}
		}
		return apply_filters('tribe_get_region', $region);
	}

	/**
	 * Zip Code
	 *
	 * Returns the event zip code
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string Zip code
	 * @since 2.0
	 */
	function tribe_get_zip( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html(tribe_get_event_meta( $postId, '_VenueZip', true ));
		return apply_filters('tribe_get_zip', $output);
	}

	/**
	 * Venue Phone Number
	 *
	 * Returns the venue phone number
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 * @return string Phone number
	 * @since 2.0
	 */
	function tribe_get_phone( $postId = null)  {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html(tribe_get_event_meta( $postId, '_VenuePhone', true ));
		return apply_filters('tribe_get_phone', $output);
	}

	/**
	 * Get all the venues
	 *
	 * @author PaulHughes01
	 * @since 2.1
	 * @param bool $only_with_upcoming Only return venues with upcoming events attached to them.
	 * @param $posts_per_page
	 * @param bool $suppress_filters
	 * @return array An array of venue post objects.
	 */
	function tribe_get_venues( $only_with_upcoming = false, $posts_per_page = -1, $suppress_filters = true ) {
		$venues = get_posts( array( 'post_type' => TribeEvents::VENUE_POST_TYPE, 'posts_per_page' => $posts_per_page, 'suppress_filters' => $suppress_filters ) );

		return $venues;
	}

	/**
	 * Get the link for the venue website.
	 * @param null $post_id
	 * @param null $label
	 *
	 * @return string Formatted link to the venue website
	 */
	function tribe_get_venue_website_link( $post_id = null, $label = null ){
		$post_id = tribe_get_venue_id( $post_id );
		$url = tribe_get_event_meta( $post_id, '_VenueURL', true );
		if( !empty($url) ) {
			$label = is_null($label) ? $url : $label;
			if( !empty( $url )) {
				$parseUrl = parse_url($url);
				if (empty($parseUrl['scheme']))
					$url = "http://$url";
			}
			$html = sprintf('<a href="%s" target="%s">%s</a>',
				$url,
				apply_filters('tribe_get_venue_website_link_target', 'self'),
				apply_filters('tribe_get_venue_website_link_label', $label)
				);
		} else {
			$html = '';
		}
		return apply_filters('tribe_get_venue_website_link', $html );
	}

}
