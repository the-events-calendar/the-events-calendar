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
		return apply_filters( 'tribe_venue_label_singular', esc_html__( 'Venue', 'the-events-calendar' ) );
	}

	/**
	 * Get Venue Label Plural
	 *
	 * Returns the plural version of the Venue Label
	 *
	 * @return string
	 */
	function tribe_get_venue_label_plural() {
		return apply_filters( 'tribe_venue_label_plural', esc_html__( 'Venues', 'the-events-calendar' ) );
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
	 * @param bool $full_link If true outputs a complete HTML <a> link, otherwise only the URL is output
	 *
	 * @return string Venue if $display is set to false, void if it's set to true.
	 */
	function tribe_get_venue_link( $postId = null, $full_link = true ) {

		$ven_id = tribe_get_venue_id( $postId );
		$url = esc_url_raw( get_permalink( $ven_id ) );

		if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
			$link = tribe_get_venue( $ven_id );
		} elseif ( $full_link ) {
			$name       = tribe_get_venue( $ven_id );
			$attr_title = the_title_attribute( array( 'post' => $ven_id, 'echo' => false ) );
			$link       = ! empty( $url ) && ! empty( $name ) ? '<a href="' . esc_url( $url ) . '" title="' . $attr_title . '">' . $name . '</a>' : false;
		} else {
			$link = $url;
		}

		return apply_filters( 'tribe_get_venue_link', $link, $postId, $full_link, $url );
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
		$venue_country = tribe_get_event_meta( $postId, '_VenueCountry', true );

		// _VenueCountry should hold an array of [ 'country_id', 'country_name' ]. Let's get the country
		// name from that array and output that
		if ( is_array( $venue_country ) ) {
			$venue_country = array_pop( $venue_country );
		}
		$output = esc_html( $venue_country );

		return apply_filters( 'tribe_get_country', $output );
	}

	/**
	 * Full Address
	 *
	 * Returns the full address for the venue. Function uses the views/modules/address.php template which you can override in your theme.
	 *
	 * @param int  $postId Can supply either event id or venue id, if none specified, current post is used
	 * @param bool $includeVenueName
	 *
	 * @return string Formatted event address
	 */
	function tribe_get_full_address( $postId = null, $includeVenueName = false ) {
		$postId    = tribe_get_venue_id( $postId );
		$tec = Tribe__Events__Main::instance();

		return apply_filters( 'tribe_get_full_address', $tec->fullAddress( $postId, $includeVenueName ) );
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
			if ( tribe_get_country( $postId ) == esc_html__( 'United States', 'the-events-calendar' ) ) {
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
		$states = Tribe__View_Helpers::loadStates();

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
		if ( class_exists( 'Tribe__Events__Pro__Geo_Loc' ) ) {
			$output[ 'lat' ] = (float) get_post_meta( $postId, Tribe__Events__Pro__Geo_Loc::LAT, true );
			$output[ 'lng' ] = (float) get_post_meta( $postId, Tribe__Events__Pro__Geo_Loc::LNG, true );
		} else {
			$output = array(
				'lat' => 0,
				'lng' => 0,
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

		if ( class_exists( 'Tribe__Events__Pro__Geo_Loc' ) ) {
			$output = (int) get_post_meta( $postId, Tribe__Events__Pro__Geo_Loc::OVERWRITE, true );
		} else{
			$output = 0;
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
		$venues = get_posts(
			array(
				'post_type' => Tribe__Events__Main::VENUE_POST_TYPE,
				'posts_per_page'   => $posts_per_page,
				'suppress_filters' => $suppress_filters,
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
		$url = tribe_get_venue_website_url( $post_id );

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
				esc_attr( esc_url( $url ) ),
				apply_filters( 'tribe_get_venue_website_link_target', '_self' ),
				apply_filters( 'tribe_get_venue_website_link_label', esc_html( $label ) )
			);
		} else {
			$html = '';
		}

		return apply_filters( 'tribe_get_venue_website_link', $html );
	}

	/**
	 * Returns the venue website URL related to the current post or for the optionally
	 * specified post.
	 *
	 * @param int|null $post_id
	 *
	 * @return string
	 */
	function tribe_get_venue_website_url( $post_id = null ) {
		return (string) tribe_get_event_meta(
			tribe_get_venue_id( $post_id ),
			'_VenueURL',
			true
		);
	}

	/**
	 * Gets venue details for use in some single-event templates.
	 *
	 * @param null $post_id
	 *
	 * @return array The venue name and venue address.
	 */
	function tribe_get_venue_details( $post_id = null ) {
		$post_id = Tribe__Main::post_id_helper( $post_id );

		if ( ! $post_id ) {
			return array();
		}

		$venue_details = array();

		if ( $venue_link = tribe_get_venue_link( $post_id ) ) {
			$venue_details['linked_name'] = $venue_link;
		}

		if ( $venue_address = tribe_get_full_address( $post_id ) ) {
			$venue_details['address'] = $venue_address;
		}

		return apply_filters( 'tribe_get_venue_details', $venue_details );
	}

	/**
	 * Gets the venue name and address on a single line
	 *
	 * @param int $event_id Event ID
	 * @param boolean $link Whether or not to wrap the text in a venue link
	 *
	 * @return string
	 */
	function tribe_get_venue_single_line_address( $event_id, $link = true ) {
		$venue = null;
		if ( tribe_has_venue( $event_id ) ) {
			$venue_id = tribe_get_venue_id( $event_id );
			$venue_name = tribe_get_venue( $event_id );
			$venue_url = tribe_get_venue_link( $event_id, false );
			$venue_address = array(
				'city' => tribe_get_city( $event_id ),
				'stateprovince' => tribe_get_stateprovince( $event_id ),
				'zip' => tribe_get_zip( $event_id ),
			);

			/**
			 * Filters the parts of a venue address
			 *
			 * @var array Array of address parts
			 * @var int Event ID
			 */
			$venue_address = apply_filters( 'tribe_events_venue_single_line_address_parts', $venue_address, $event_id );

			// get rid of blank elements
			$venue_address = array_filter( $venue_address );

			$venue = $venue_name;

			$separator = _x( ', ', 'Address separator', 'the-events-calendar' );
			if ( $venue_address ) {
				$venue .= $separator . implode( $separator, $venue_address );
			}

			if ( $link && $venue_url ) {
				$attr_title = the_title_attribute( array( 'post' => $venue_id, 'echo' => false ) );

				$venue = '<a href="' . esc_url( $venue_url ) . '" title="' . $attr_title . '">' . $venue . '</a>';
			}
		}

		/**
		 * Filters the venue single-line address
		 *
		 * @var string Venue address line
		 * @var int Event ID
		 * @var boolean Whether or not the venue should be linked
		 */
		return apply_filters( 'tribe_events_get_venue_single_line_address', $venue, $event_id, $link );
	}
}
