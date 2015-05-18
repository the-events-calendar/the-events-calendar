<?php
/**
 * Venue Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Venue ID
	 *
	 * Returns the event Venue ID.
	 *
	 * @param int $postId can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return int Venue ID
	 */
	function tribe_get_venue_id( $postId = null ) {
		$postId = Tribe__Events__Main::postIdHelper( $postId );
		if ( tribe_is_venue( $postId ) ) {
			return $postId;
		} else {
			return apply_filters( 'tribe_get_venue_id', tribe_get_event_meta( $postId, '_EventVenueID', true ) );
		}
	}

	/**
	 * Get Venue Label Singular
	 *
	 * Returns the singular version of the Venue Label
	 *
	 * @return string
	 */
	function tribe_get_venue_label_singular() {
		return apply_filters( 'tribe_venue_label_singular', __( 'Venue', 'tribe-events-calendar' ) );
	}

	/**
	 * Get Venue Label Plural
	 *
	 * Returns the plural version of the Venue Label
	 *
	 * @return string
	 */
	function tribe_get_venue_label_plural() {
		return apply_filters( 'tribe_venue_label_plural', __( 'Venues', 'tribe-events-calendar' ) );
	}

	/**
	 * Venue Test
	 *
	 * Returns true or false depending on if the post id for the event has a venue or if the post id is a venue
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return bool
	 */
	function tribe_has_venue( $postId = null ) {
		$has_venue = ( tribe_get_venue_id( $postId ) > 0 ) ? true : false;

		return apply_filters( 'tribe_has_venue', $has_venue );
	}

	/**
	 * Get Venue
	 *
	 * Returns the event venue name
	 *
	 * @param int  $postId    Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return string Venue Name
	 */
	function tribe_get_venue( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$venue  = ( $postId > 0 ) ? esc_html( get_the_title( $postId ) ) : null;

		return apply_filters( 'tribe_get_venue', $venue );
	}

	/**
	 * Venue Link
	 *
	 * Returns or display the event Venue Name with a link to the venue
	 *
	 * @param int  $postId  Can supply either event id or venue id, if none specified, current post is used
	 * @param bool $display If true displays full html links around venue's name, if false returns just the link without displaying it
	 *
	 * @return string Venue if $display is set to false, void if it's set to true.
	 */
	function tribe_get_venue_link( $postId = null, $display = true ) {

		$url = '';

		if ( $venue_id = tribe_get_venue_id( $postId ) ) {
			$url = esc_url_raw( get_permalink( $venue_id ) );
		}

		if ( $display && $url != '' ) {
			$venue_name = tribe_get_venue( $postId );
			$link       = '<a href="' . esc_url( $url ) . '">' . $venue_name . '</a>';
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
	 *
	 * @return string Country
	 */
	function tribe_get_country( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueCountry', true ) );

		return apply_filters( 'tribe_get_country', $output );
	}

	/**
	 * Full Address
	 *
	 * Returns the full address for the venue. Function uses the views/full-address.php template which you can override in your theme (including google microformats etc).
	 *
	 * @param int  $postId Can supply either event id or venue id, if none specified, current post is used
	 * @param bool $includeVenueName
	 *
	 * @return string Formatted event address
	 */
	function tribe_get_full_address( $postId = null, $includeVenueName = false ) {
		$postId    = tribe_get_venue_id( $postId );
		$tribe_ecp = Tribe__Events__Main::instance();

		return apply_filters( 'tribe_get_full_address', $tribe_ecp->fullAddress( $postId, $includeVenueName ) );
	}

	/**
	 * Address Test
	 *
	 * Returns true if any of the following exist: address, city, state/province (region), country or zip
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return bool True if any part of an address exists
	 */
	function tribe_address_exists( $postId = null ) {
		if (
			tribe_get_address( $postId ) ||
			tribe_get_city( $postId ) ||
			tribe_get_region( $postId ) ||
			tribe_get_country( $postId ) ||
			tribe_get_zip( $postId ) ||
			( tribe_is_venue_overwrite( $postId ) && tribe_get_coordinates( $postId ) )
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
	 *
	 * @return string Street address
	 */
	function tribe_get_address( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueAddress', true ) );

		return apply_filters( 'tribe_get_address', $output );
	}

	/**
	 * City
	 *
	 * Returns the venue city
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return string City
	 */
	function tribe_get_city( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueCity', true ) );

		return apply_filters( 'tribe_get_city', $output );
	}

	/**
	 * State or Province
	 *
	 * Returns the venue state or province
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return string State
	 * @todo Depricate tribe_get_stateprovince or tribe_get_region
	 */
	function tribe_get_stateprovince( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueStateProvince', true ) );

		return apply_filters( 'tribe_get_stateprovince', $output );
	}

	/**
	 * State
	 *
	 * Returns the venue state
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return string State
	 */
	function tribe_get_state( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueState', true ) );

		return apply_filters( 'tribe_get_state', $output );
	}

	/**
	 * Province
	 *
	 * Returns the venue province
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return string Province
	 */
	function tribe_get_province( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueProvince', true ) );

		return apply_filters( 'tribe_get_province', $output );
	}

	/**
	 * Region
	 *
	 * Returns the state or province for US or non-US addresses (effectively the same thing as tribe_get_stateprovince())
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return string
	 * @todo Depricate tribe_get_region or tribe_get_stateprovince
	 */
	function tribe_get_region( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		if ( tribe_get_event_meta( $postId, '_VenueStateProvince', true ) ) {
			$region = tribe_get_event_meta( $postId, '_VenueStateProvince', true );
		} else {
			if ( tribe_get_country( $postId ) == __( 'United States', 'tribe-events-calendar' ) ) {
				$region = tribe_get_state( $postId );
			} else {
				$region = tribe_get_province();
			}
		}

		return apply_filters( 'tribe_get_region', $region );
	}

	/**
	 * Zip Code
	 *
	 * Returns the event zip code
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return string Zip code
	 */
	function tribe_get_zip( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenueZip', true ) );

		return apply_filters( 'tribe_get_zip', $output );
	}

	/**
	 * Gets the full region name of a given event's Venue address.
	 *
	 * @param int $event_id
	 *
	 * @return string The full region for this event's address.
	 */
	function tribe_get_full_region( $event_id ) {
		$province = tribe_get_event_meta( $event_id, '_VenueStateProvince', true );
		$states = Tribe__Events__View_Helpers::loadStates();
		
		$full_region = isset( $states[ $province ] ) ? $states[ $province ] : $province;
		
		return apply_filters( 'tribe_get_full_region', $full_region );
	}


	/**
	 * Coordinates
	 *
	 * Returns the coordinates of the venue
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return array An Array with the Latitute and Longitude of the venue
	 */
	function tribe_get_coordinates( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		if( class_exists( 'Tribe__Events__Pro__Geo_Loc' ) ){
			$output[ 'lat' ] = (float) get_post_meta( $postId, Tribe__Events__Pro__Geo_Loc::LAT, true );
			$output[ 'lng' ] = (float) get_post_meta( $postId, Tribe__Events__Pro__Geo_Loc::LNG, true );
		} else {
			$output = array(
				'lat' => (float)'',
				'lng' => (float)''
			);
		}

		return apply_filters( 'tribe_get_coordinates', $output );
	}


	/**
	 * Coordinates Overwrite
	 *
	 * Conditional if the venue has it's coordinates overwritten
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return bool Depending on the venue checkbox of overwrite coordinates
	 */
	function tribe_is_venue_overwrite( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );

		if( class_exists( 'Tribe__Events__Pro__Geo_Loc' ) ){
			$output = (int) get_post_meta( $postId, Tribe__Events__Pro__Geo_Loc::OVERWRITE, true );
		} else{
			$output = (int)'';
		}

		return apply_filters( 'tribe_is_venue_overwrite', (bool) $output );
	}


	/**
	 * Venue Phone Number
	 *
	 * Returns the venue phone number
	 *
	 * @param int $postId Can supply either event id or venue id, if none specified, current post is used
	 *
	 * @return string Phone number
	 */
	function tribe_get_phone( $postId = null ) {
		$postId = tribe_get_venue_id( $postId );
		$output = esc_html( tribe_get_event_meta( $postId, '_VenuePhone', true ) );

		return apply_filters( 'tribe_get_phone', $output );
	}

	/**
	 * Get all the venues
	 *
	 * @param bool $only_with_upcoming Only return venues with upcoming events attached to them.
	 * @param      $posts_per_page
	 * @param bool $suppress_filters
	 *
	 * @return array An array of venue post objects.
	 */
	function tribe_get_venues( $only_with_upcoming = false, $posts_per_page = -1, $suppress_filters = true ) {
		$venues = get_posts( array( 'post_type'        => Tribe__Events__Main::VENUE_POST_TYPE,
									'posts_per_page'   => $posts_per_page,
									'suppress_filters' => $suppress_filters
			)
		);

		return $venues;
	}

	/**
	 * Get the link for the venue website.
	 *
	 * @param null $post_id
	 * @param null $label
	 *
	 * @return string Formatted link to the venue website
	 */
	function tribe_get_venue_website_link( $post_id = null, $label = null ) {
		$post_id = tribe_get_venue_id( $post_id );
		$url     = tribe_get_event_meta( $post_id, '_VenueURL', true );
		if ( ! empty( $url ) ) {
			$label = is_null( $label ) ? $url : $label;
			if ( ! empty( $url ) ) {
				$parseUrl = parse_url( $url );
				if ( empty( $parseUrl['scheme'] ) ) {
					$url = "http://$url";
				}
			}
			$html = sprintf(
				'<a href="%s" target="%s">%s</a>',
				esc_url( $url ),
				apply_filters( 'tribe_get_venue_website_link_target', 'self' ),
				apply_filters( 'tribe_get_venue_website_link_label', $label )
			);
		} else {
			$html = '';
		}

		return apply_filters( 'tribe_get_venue_website_link', $html );
	}

	/**
	* Gets venue details for use in some single-event templates.
	*
	* @param null $post_id
	*
	* @return array The venue name and venue address.
	*/
	function tribe_get_venue_details() {
	
		$venue_details = array();
		
		if ( $venue_name = tribe_get_meta( 'tribe_event_venue_name' ) ) {
			$venue_details['name'] = $venue_name;
		}
		
		if ( $venue_address = tribe_get_meta( 'tribe_event_venue_address' ) ) {
			$venue_details['address'] = $venue_address;
		}
		
		return apply_filters( 'tribe_get_venue_details', $venue_details );
	}

}
