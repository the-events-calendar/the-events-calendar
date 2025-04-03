<?php
/**
 * Venue Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

use Tribe\Events\Models\Post_Types\Venue;

/**
 * Fetches and returns a decorated post object representing a Venue.
 *
 * @since 4.9.9
 *
 * @param null|int|WP_Post $venue  The venue ID or post object or `null` to use the global one.
 * @param string|null      $output The required return type. One of `OBJECT`, `ARRAY_A`, or `ARRAY_N`, which
 *                                 correspond to a WP_Post object, an associative array, or a numeric array,
 *                                 respectively. Defaults to `OBJECT`.
 * @param string           $filter Type of filter to apply. Accepts 'raw'.
 * @param bool             $force  Whether to force a re-fetch ignoring cached results or not.
 *
 * @return array|mixed|void|WP_Post|null {
 *                              The Venue post object or array, `null` if not found.
 *
 *                              @type string $address The venue address field, normally street and number.
 *                              @type string $country Which country the venue happens, full name of the country, no abbr.
 *                              @type string $city The city for the venue.
 *                              @type string $state_province State or province for the venue, available for venues outside of the US.
 *                              @type string $state The state for the venue in case of a US based venue.
 *                              @type string $province Province for the venue, mostly deprecated, use state_province.
 *                              @type string $zip Zip code of the venue.
 *                              @type boolean $overwrite_coordinates Did this venue get it's coordinates overwritten manually.
 *                              @type string $latitude The latitude of the venue.
 *                              @type string $longitude The longitude of the venue.
 *                              @type string $geolocation_string The string we use to crawl and link to the maps provider.
 *                          }
 */
function tribe_get_venue_object( $venue = null, $output = OBJECT, $filter = 'raw', $force = false ) {
	/**
	 * Filters the venue result before any logic applies.
	 *
	 * Returning a non `null` value here will short-circuit the function and return the value.
	 * Note: this value will not be cached and the caching of this value is a duty left to the filtering function.
	 *
	 * @since 4.9.9
	 *
	 * @param mixed       $return      The venue object to return.
	 * @param mixed       $venue       The venue object to fetch.
	 * @param string|null $output      The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
	 *                                 correspond to a `WP_Post` object, an associative array, or a numeric array,
	 *                                 respectively. Defaults to `OBJECT`.
	 * @param string      $filter      Type of filter to apply. Accepts 'raw'.
	 */
	$return = apply_filters( 'tribe_get_venue_object_before', null, $venue, $output, $filter );

	if ( null !== $return ) {
		return $return;
	}

	/** @var Tribe__Cache $cache */
	$cache = tribe( 'cache' );
	$cache_key = 'tribe_get_venue_object_' . md5( json_encode( [ $venue, $output, $filter ] ) );

	// Try getting the memoized value.
	$post = $cache[ $cache_key ];

	if ( false === $post ) {
		// No memoized value, build from properties.
		$post = Venue::from_post( $venue )->to_post( $output, $filter );

		if ( empty( $post ) ) {
			return null;
		}

		/**
		 * Filters the venue post object before caching it and returning it.
		 *
		 * Note: this value will be cached; as such this filter might not run on each request.
		 * If you need to filter the output value on each call of this function then use the `tribe_get_venue_object_before`
		 * filter.
		 *
		 * @since 4.9.7
		 *
		 * @param WP_Post $post   The venue post object, decorated with a set of custom properties.
		 * @param string  $output The output format to use.
		 * @param string  $filter The filter, or context of the fetch.
		 */
		$post = apply_filters( 'tribe_get_venue_object', $post, $output, $filter );

		// Memoize the value.
		$cache[ $cache_key ] = $post;
	}

	if ( empty( $post ) ) {
		return null;
	}

	/**
	 * Filters the venue result after the venue has been built from the function.
	 *
	 * Note: this value will not be cached and the caching of this value is a duty left to the filtering function.
	 *
	 * @since 6.0.3.1
	 *
	 * @param WP_Post     $post        The venue post object to filter and return.
	 * @param int|WP_Post $venue       The venue object to fetch.
	 * @param string|null $output      The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
	 *                                 correspond to a `WP_Post` object, an associative array, or a numeric array,
	 *                                 respectively. Defaults to `OBJECT`.
	 * @param string      $filter      The filter, or context of the fetch.
	 */
	$post = apply_filters( 'tribe_get_venue_object_after', $post, $venue, $output, $filter );

	if ( OBJECT !== $output ) {
		$post = ARRAY_A === $output ? (array) $post : array_values( (array) $post );
	}

	return $post;
}


/**
 * Returns the event Venue ID.
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return int        The venue ID.
 */
function tribe_get_venue_id( $postId = null ) {
	$postId = Tribe__Events__Main::postIdHelper( $postId );
	if ( tribe_is_venue( $postId ) ) {
		return $postId;
	} else {
		/**
		 * Allow for customizing the Venue ID retrieved for this item.
		 *
		 * @since ??
		 * @since 4.5.12 Added docblock and venue ID to filter.
		 *
		 * @param int $venue_id The Venue ID for the specified event.
		 * @param int $postId The ID of the event whose venue is being looked for.
		 */
		return apply_filters( 'tribe_get_venue_id', tribe_get_event_meta( $postId, '_EventVenueID', true ), $postId );
	}
}

/**
 * Get the IDs of all venues associated with an event.
 *
 * @since 6.2.0
 *
 * @param int $event_id The event post ID. Defaults to the current event.
 *
 * @return array
 */
function tec_get_venue_ids( $event_id = null ) {
	$event_id = Tribe__Events__Main::postIdHelper( $event_id );

	$venue_ids = [];

	if ( Tribe__Events__Main::instance()->isEvent( $event_id ) ) {
		$venue_ids = tribe_get_event_meta( $event_id, '_EventVenueID', false );

		// Protect against storing array items that render false, such as `0`.
		$venue_ids = array_filter( (array) $venue_ids );
	}

	/**
	 * Allows customization of the venue IDs retrieved for a specified event.
	 *
	 * @since 6.2.0
	 *
	 * @param int[] $venue_ids The venue IDs for the specified event.
	 * @param int   $event_id  The ID of the event whose venues are being looked for.
	 */
	return (array) apply_filters( 'tec_get_venue_ids', $venue_ids, $event_id );
}

/**
 * Returns the singular version of the Venue Label.
 *
 * Note: the output of this function is not escaped.
 * You should escape it wherever you use it!
 *
 * @since 3.7
 * @since5.1.6 remove escaping.
 *
 * @return string The singular version of the Venue Label.
 */
function tribe_get_venue_label_singular() {
	/**
	 * Allows customization of the singular version of the Venue Label.
	 * Note: the output of this filter is not escaped!
	 *
	 * @since 3.7
	 * @since 4.5.12 Added docblock.
	 * @since5.1.6 Remove escaping.
	 *
	 * @param string $label The singular version of the Venue label, defaults to "Venue" (uppercase)
	 */
	return apply_filters(
		'tribe_venue_label_singular',
		__( 'Venue', 'the-events-calendar' )
	);
}

/**
 * Get Venue Label Singular lowercase.
 * Returns the lowercase singular version of the Venue Label.
 *
 * Note: the output of this function is not escaped.
 * You should escape it wherever you use it!
 *
 * @since 6.2.1
 *
 * @return string The lowercase singular version of the Venue Label.
 */
function tribe_get_venue_label_singular_lowercase() {
	/**
	 * Allows customization of the singular lowercase version of the Venue Label.
	 * Note: the output of this filter is not escaped!
	 *
	 * @since 6.2.1
	 *
	 * @param string $label The singular lowercase version of the Venue label, defaults to "venue" (lowercase)
	 */
	return apply_filters(
		'tribe_venue_label_singular_lowercase',
		__( 'venue', 'the-events-calendar' )
	);
}

/**
 * Returns the plural version of the Venue Label.
 *
 * Note: the output of this function is not escaped.
 * You should escape it wherever you use it!
 *
 * @since 3.7
 * @since5.1.6 remove escaping.
 *
 * @return string The plural version of the Venue Label.
 */
function tribe_get_venue_label_plural() {
	/**
	 * Allows customization of the plural version of the Venue Label.
	 * Note: the output of this filter is not escaped!
	 *
	 * @since 3.7
	 * @since 4.5.12 Added docblock
	 * @since5.1.6 Remove escaping.
	 *
	 * @param string $label The plural version of the Venue label, defaults to "Venues" (uppercase)
	 */
	return apply_filters(
		'tribe_venue_label_plural',
		__( 'Venues', 'the-events-calendar' )
	);
}

/**
 * Returns true or false depending on if the post id for the event has a venue or if the post id is a venue
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return bool
 */
function tribe_has_venue( $postId = null ) {
	$has_venue = ( tribe_get_venue_id( $postId ) > 0 ) ? true : false;

	/**
	 * Allows customization of whether a given event has a venue.
	 *
	 * @since ??
	 * @since 4.5.12 Added docblock and venue ID to filter.
	 *
	 * @param bool $has_venue Whether the specified event has a venue.
	 * @param int $postId Can be either the event ID or its venue ID
	 */
	return apply_filters( 'tribe_has_venue', $has_venue, $postId );
}

/**
 * Returns the event venue name
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string Venue Name
 */
function tribe_get_venue( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	$venue    = ( $venue_id > 0 ) ? esc_html( get_the_title( $venue_id ) ) : null;

	/**
	 * Allows customization of the retrieved venue name for a specified event.
	 *
	 * @since ??
	 * @since 4.5.12 Added docblock and venue ID to filter.
	 *
	 * @param string $venue The name of the retrieved venue.
	 * @param int $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_venue', $venue, $venue_id );
}

/**
 * Returns or display the event Venue Name with a link to the venue
 *
 * @since ??
 *
 * @param int  $postId    Either event id or venue id, if none specified, current post is used.
 * @param bool $full_link If true outputs a complete HTML <a> link, otherwise only the URL is output
 * @return string         HTML link if $full_link is set to true, URL string if it's set to false.
 */
function tribe_get_venue_link( $postId = null, $full_link = true ) {

	$venue_id = tribe_get_venue_id( $postId );
	$url      = esc_url_raw( get_permalink( $venue_id ) );

	if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
		$link = tribe_get_venue( $venue_id );
	} elseif ( $full_link ) {
		$name       = tribe_get_venue( $venue_id );
		$attr_title = the_title_attribute( [ 'post' => $venue_id, 'echo' => false ] );
		$link       = ! empty( $url ) && ! empty( $name ) ? '<a href="' . esc_url( $url ) . '" title="' . $attr_title . '">' . $name . '</a>' : false;
	} else {
		$link = $url;
	}

	/**
	 * Allows customization of the "Venue name with link" retrieved for a specified event.
	 *
	 * @since ??
	 * @since 4.5.12 Added docblock and function args to filter.
	 *
	 * @param string $link      The assembled "Venue name with link" string.
	 * @param int    $venue_id  The venue's ID.
	 * @param bool   $full_link If true outputs a complete HTML <a> link, otherwise only the URL is output.
	 * @param string $url       The raw permalink to the venue.
	 */
	return apply_filters( 'tribe_get_venue_link', $link, $venue_id, $full_link, $url );
}

/**
 * Returns the venue's country
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The escaped country name of the venue.
 */
function tribe_get_country( $postId = null ) {
	$venue_id      = tribe_get_venue_id( $postId );
	$venue_country = tribe_get_event_meta( $venue_id, '_VenueCountry', true );

	// _VenueCountry should hold an array of [ 'country_id', 'country_name' ]. Let's get the country
	// name from that array and output that
	if ( is_array( $venue_country ) ) {
		$venue_country = array_pop( $venue_country );
	}
	$output = esc_html( $venue_country );

	/**
	 * Allows customization of the retrieved venue country for a specified event.
	 *
	 * @since ??
	 * @since 4.5.12 Added docblock and venue ID to filter.
	 *
	 * @param string $output   The escaped country name of the venue.
	 * @param int    $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_country', $output, $venue_id );
}

/**
 * Returns the full address for the venue. Function uses the views/modules/address.php template which you can override in your theme.
 *
 * @since ??
 *
 * @param int  $post_id           Either event id or venue id, if none specified, current post is used.
 * @param bool $includeVenueName To include the venue name or not.
 * @return string                Formatted event address.
 */
function tribe_get_full_address( $event_id = null, $includeVenueName = false ) {
	$post_id  = tribe_get_venue_id( $event_id );

	global $post;
	if ( ! is_null( $post_id ) ) {
		$tmp_post = $post;
		$post     = get_post( $post_id );
	}

	ob_start();
	tribe_get_template_part( 'modules/address' );
	$address = ob_get_clean();

	if ( ! empty( $tmp_post ) ) {
		$post = $tmp_post;
	}

	/**
	 * Allows customization of the venue's full address.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock; also added $venue_id and $includeVenueName to filter.
	 *
	 * @param string $address The formatted event address
	 * @param int    $post_id The venue ID.
	 * @param bool   $includeVenueName To include the venue name or not.
	 */
	return apply_filters( 'tribe_get_full_address', $address, $post_id, $includeVenueName );
}

/**
 * Returns true if any of the following exist: address, city, state/province (region), country or zip
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return bool True if any part of an address exists.
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
 * Returns the venue street address
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The escaped venue street address.
 */
function tribe_get_address( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	$output   = esc_html( tribe_get_event_meta( $venue_id, '_VenueAddress', true ) );

	/**
	 * Allows customization of the venue's street address.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param string $output The escaped venue street address
	 * @param int $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_address', $output, $venue_id );
}

/**
 * Returns the venue city
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The escaped venue city
 */
function tribe_get_city( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	$output   = esc_html( tribe_get_event_meta( $venue_id, '_VenueCity', true ) );

	/**
	 * Allows customization of the venue's city.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param string $output The escaped venue city
	 * @param int $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_city', $output, $venue_id );
}

/**
 * Returns the venue state or province
 *
 * @since ??
 *
 * @todo Deprecate tribe_get_stateprovince or tribe_get_region
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The escaped venue state or province.
 */
function tribe_get_stateprovince( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	$output   = esc_html( tribe_get_event_meta( $venue_id, '_VenueStateProvince', true ) );

	/**
	 * Allows customization of the venue's state or province.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param string $output The escaped venue state or province.
	 * @param int $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_stateprovince', $output, $venue_id );
}

/**
 * Returns the venue state
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The escaped venue state or province.
 */
function tribe_get_state( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	$output   = esc_html( tribe_get_event_meta( $venue_id, '_VenueState', true ) );

	/**
	 * Allows customization of the venue's state.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param string $output The escaped venue state or province.
	 * @param int $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_state', $output, $venue_id );
}

/**
 * Returns the venue province
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The escaped venue province.
 */
function tribe_get_province( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	$output   = esc_html( tribe_get_event_meta( $venue_id, '_VenueProvince', true ) );

	/**
	 * Allows customization of the venue's province.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param string $output The escaped venue province.
	 * @param int $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_province', $output, $venue_id );
}

/**
 * Returns the state or province for US or non-US addresses (effectively the same thing as tribe_get_stateprovince())
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The state or province for the event.
 * @todo Deprecate tribe_get_region or tribe_get_stateprovince
 */
function tribe_get_region( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	if ( tribe_get_event_meta( $venue_id, '_VenueStateProvince', true ) ) {
		$region = tribe_get_event_meta( $venue_id, '_VenueStateProvince', true );
	} else {
		if ( tribe_get_country( $venue_id ) == esc_html__( 'United States', 'the-events-calendar' ) ) {
			$region = tribe_get_state( $venue_id );
		} else {
			$region = tribe_get_province( $venue_id );
		}
	}

	/**
	 * Allows customization of the venue's state or province for US, or non-US addresses.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter.
	 *
	 * @param string $region The venue province.
	 * @param int $venue_id  The venue ID.
	 */
	return apply_filters( 'tribe_get_region', $region, $venue_id );
}

/**
 * Returns the event zip code
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The venue zip code.
 */
function tribe_get_zip( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	$output   = esc_html( tribe_get_event_meta( $venue_id, '_VenueZip', true ) );

	/**
	 * Allows customization of the venue's zip code.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param string $output The venue zip code.
	 * @param int $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_zip', $output, $venue_id );
}

/**
 * Gets the full region name of a given event's Venue address.
 *
 * @since ??
 *
 * @param int $event_id The post ID of the event.
 * @return string       The full region for this event's address.
 */
function tribe_get_full_region( $event_id ) {
	$province = tribe_get_event_meta( $event_id, '_VenueStateProvince', true );
	$states   = Tribe__View_Helpers::loadStates();

	$full_region = isset( $states[ $province ] ) ? $states[ $province ] : $province;

	/**
	 * Allows customization of the venue address's full region name.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and event ID to filter
	 *
	 * @param string $full_region The full region name of the given event's Venue address
	 * @param int  $event_id The ID of the event whose venue is being accessed
	 */
	return apply_filters( 'tribe_get_full_region', $full_region, $event_id );
}


/**
 * Returns the coordinates of the venue
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return array      An Array with the Latitude and Longitude of the venue.
 */
function tribe_get_coordinates( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );

	if ( class_exists( 'Tribe__Events__Pro__Geo_Loc' ) ) {
		$output[ 'lat' ] = (float) get_post_meta( $venue_id, Tribe__Events__Pro__Geo_Loc::LAT, true );
		$output[ 'lng' ] = (float) get_post_meta( $venue_id, Tribe__Events__Pro__Geo_Loc::LNG, true );
	} else {
		$output = [
			'lat' => 0,
			'lng' => 0,
		];
	}

	/**
	 * Allows customization of a venue's coordinates.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param array $output The latitude and longitude of the venue.
	 * @param int $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_get_coordinates', $output, $venue_id );
}


/**
 * Conditional if the venue has it's coordinates overwritten
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return bool       Depending on the venue checkbox of overwrite coordinates.
 */
function tribe_is_venue_overwrite( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );

	if ( class_exists( 'Tribe__Events__Pro__Geo_Loc' ) ) {
		$output = (int) get_post_meta( $venue_id, Tribe__Events__Pro__Geo_Loc::OVERWRITE, true );
	} else{
		$output = 0;
	}

	/**
	 * Allows customization of a venue's coordinates.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param bool $output Whether the venue's coordinates are overwritten or not.
	 * @param int  $venue_id The venue ID.
	 */
	return apply_filters( 'tribe_is_venue_overwrite', (bool) $output, $venue_id );
}


/**
 * Returns the venue phone number
 *
 * @since ??
 *
 * @param int $postId Either event id or venue id, if none specified, current post is used.
 * @return string     The escaped phone number for the venue.
 */
function tribe_get_phone( $postId = null ) {
	$venue_id = tribe_get_venue_id( $postId );
	$output   = esc_html( tribe_get_event_meta( $venue_id, '_VenuePhone', true ) );

	/**
	 * Allows customization of a venue's phone number.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter
	 *
	 * @param string $output The escaped phone number for the venue.
	 * @param int    $venue_id The venue ID
	 */
	return apply_filters( 'tribe_get_phone', $output, $venue_id );
}

/**
 * Get all the venues
 *
 * @since 6.2.9 Applied the `tec_events_custom_tables_v1_normalize_occurrence_id` filter to convert provisional IDs into regular IDs.
 *
 * @param bool  $only_with_upcoming Only return venues with upcoming events attached to them.
 * @param int   $posts_per_page
 * @param bool  $suppress_filters
 * @param array $args {
 *      Optional. Array of Query parameters.
 *
 *      @type int  $event       Only venues linked to this event post ID.
 *      @type bool $has_events  Only venues that have events.
 *      @type bool $found_posts Return the number of found venues.
 * }
 *
 * @return array An array of venue post objects.
 */
function tribe_get_venues( $only_with_upcoming = false, $posts_per_page = -1, $suppress_filters = true, array $args = [] ) {
	// filter out the `null` values
	$args = array_diff_key( $args, array_filter( $args, 'is_null' ) );

	/**
	 * Convert provisional IDs into regular post IDs.
	 *
	 * @since 6.2.9
	 *
	 * @param int $event The provisional event ID.
	 *
	 * @return int The normalized event ID.
	 */
	if ( isset( $args['event'] ) ) {
		$args['event'] = apply_filters( 'tec_events_custom_tables_v1_normalize_occurrence_id', $args['event'] );
	}

	if ( tribe_is_truthy( $only_with_upcoming ) ) {
		$args['only_with_upcoming'] = true;
	}

	$filter_args = [
		'event'              => 'find_for_event',
		'has_events'         => 'find_with_events',
		'only_with_upcoming' => 'find_with_upcoming_events',
	];

	foreach ( $filter_args as $filter_arg => $method ) {
		if ( ! isset( $args[ $filter_arg ] ) ) {
			continue;
		}

		if ( 'only_with_upcoming' !== $filter_arg ) {
			$found = tribe( 'tec.linked-posts.venue' )->$method( $args[ $filter_arg ] );
		} else {
			$found = tribe( 'tec.linked-posts.venue' )->find_with_upcoming_events(
				$args[ $filter_arg ],
				isset( $args['post_status'] ) ? $args['post_status'] : null
			);
		}

		if ( empty( $found ) ) {
			return [];
		}

		$args['post__in'] = ! empty( $args['post__in'] )
			? array_intersect( (array) $args['post__in'], $found )
			: $found;

		if ( empty( $args['post__in'] ) ) {
			return [];
		}
	}

	$parsed_args = wp_parse_args(
		$args,
		[
			'post_type'        => Tribe__Events__Main::VENUE_POST_TYPE,
			'posts_per_page'   => $posts_per_page,
			'suppress_filters' => $suppress_filters,
		]
	);

	$return_found_posts = ! empty( $args['found_posts'] );

	if ( $return_found_posts ) {
		$parsed_args['posts_per_page'] = 1;
		$parsed_args['paged']          = 1;
	}

	$query = new WP_Query( $parsed_args );

	if ( $return_found_posts ) {
		if ( $query->have_posts() ) {

			return $query->found_posts;
		}

		return 0;
	}

	return $query->have_posts() ? $query->posts : [];
}

/**
 * Get the link for the venue website.
 *
 * @since 3.0
 *
 * @param ?int    $post_id The event or venue ID.
 * @param ?string $label   The label for the link.
 * @param string  $target  The target attribute for the link.
 *
 * @return string Formatted link to the venue website
 */
function tribe_get_venue_website_link( $post_id = null, $label = null, $target = '_self' ): string {
	$post_id = tribe_get_venue_id( $post_id );
	$url     = tribe_get_venue_website_url( $post_id );

	if ( ! empty( $url ) ) {
		$label = is_null( $label ) ? $url : $label;
		$parse_url = parse_url( $url );
		if ( empty( $parse_url['scheme'] ) ) {
			$url = 'http://' . $url;
		}

		/**
		 * Allows customization of a venue's website link target.
		 *
		 * @since 3.0
		 * @since 4.5.11 Added docblock and venue ID to filter.
		 *
		 * @param string $target  The target attribute string. Defaults to "_self".
		 * @param string $url     The link URL.
		 * @param int    $post_id The venue ID.
		 */
		$target = apply_filters( 'tribe_get_venue_website_link_target', $target, $url, $post_id );

		// Ensure the target is given a valid value.
		$allowed = [ '_self', '_blank', '_parent', '_top', '_unfencedTop' ];
		if ( ! in_array( $target, $allowed ) ) {
			$target = '_self';
		}

		$rel = ( '_blank' === $target ) ? 'noopener noreferrer' : 'external';

		/**
		 * Allows customization of a venue's website link label.
		 *
		 * @since 3.0
		 * @since 4.5.11 Added docblock and venue ID to filter.
		 *
		 * @param string $label   The venue's website link label.
		 * @param int    $post_id The venue ID.
		 */
		$website_link_label = apply_filters( 'tribe_get_venue_website_link_label', esc_html( $label ), $post_id );
		$html = sprintf(
			'<a href="%1$s" target="%2$s" rel="%3$s">%4$s</a>',
			esc_attr( esc_url( $url ) ),
			esc_attr( $target ),
			esc_attr( $rel ),
			esc_html( $website_link_label )
		);
	} else {
		$html = '';
	}

	/**
	 * Allows customization of a venue's website link.
	 *
	 * @since 3.0
	 * @since 4.5.11 Added docblock.
	 *
	 * @param string $html The assembled HTML link tag of venue's website link.
	 * @param int $post_id The venue ID.
	 */
	return apply_filters( 'tribe_get_venue_website_link', $html, $post_id );
}

/**
 * Get the link for the venue website.
 *
 * @since 5.5.0
 *
 * @param null|int $post_id The event or venue ID.
 * @return string  Formatted title for the venue website link
 */
function tribe_events_get_venue_website_title( $post_id = null ) {
	$post_id = tribe_get_venue_id( $post_id );

	/**
	 * Allows customization of a venue's website title link.
	 *
	 * @since 5.5.0
	 *
	 * @param string $title   The title of the venue's website link.
	 * @param int    $post_id The venue ID.
	 */
	return apply_filters( 'tribe_events_get_venue_website_title', __( 'Website', 'the-events-calendar' ), $post_id );
}

/**
 * Returns the venue website URL related to the current post or for the optionally
 * specified post.
 *
 * @since ??
 *
 * @param int|null $post_id The event ID.
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
 * @since ??
 *
 * @param int|null $post_id The venue ID.
 * @return array            The venue name and venue address.
 */
function tribe_get_venue_details( $post_id = null ) {
	$post_id = Tribe__Main::post_id_helper( $post_id );

	if ( ! $post_id ) {
		return [];
	}

	$venue_details = [];

	if ( $venue_link = tribe_get_venue_link( $post_id ) ) {
		$venue_details['linked_name'] = $venue_link;
	}

	if ( $venue_address = tribe_get_full_address( $post_id ) ) {
		$venue_details['address'] = $venue_address;
	}

	/**
	 * Allows customization of the retrieved venue details.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and venue ID to filter.
	 *
	 * @param array $venue_details An array of the venue's details.
	 * @param int   $post_id       The venue ID.
	 */
	return apply_filters( 'tribe_get_venue_details', $venue_details, $post_id );
}

/**
 * Gets the venue name and address on a single line.
 *
 * @since ??
 *
 * @param int     $event_id The event ID.
 * @param boolean $link     Whether or not to wrap the text in a venue link.
 * @return string           Single-line address string.
 */
function tribe_get_venue_single_line_address( $event_id, $link = true ) {
	$venue = null;

	if ( tribe_has_venue( $event_id ) ) {
		$venue_id      = tribe_get_venue_id( $event_id );
		$venue_name    = tribe_get_venue( $event_id );
		$venue_url     = tribe_get_venue_link( $event_id, false );
		$venue_address = [
			'city'          => tribe_get_city( $event_id ),
			'stateprovince' => tribe_get_stateprovince( $event_id ),
			'zip'           => tribe_get_zip( $event_id ),
		];

		/**
		 * Filters the parts of a venue address.
		 *
		 * @since ??
		 * @since 4.5.11 Added docblock and event ID to filter.
		 *
		 * @var array $venue_address An array of address parts.
		 * @var int   $event_id      The event ID.
		 */
		$venue_address = apply_filters( 'tribe_events_venue_single_line_address_parts', $venue_address, $event_id );

		// Get rid of blank elements.
		$venue_address = array_filter( $venue_address );

		$venue = $venue_name;

		$separator = _x( ', ', 'Address separator', 'the-events-calendar' );
		if ( $venue_address ) {
			$venue .= $separator . implode( $separator, $venue_address );
		}

		if ( $link && $venue_url ) {
			$attr_title = the_title_attribute( [ 'post' => $venue_id, 'echo' => false ] );

			$venue = '<a href="' . esc_url( $venue_url ) . '" title="' . $attr_title . '">' . $venue . '</a>';
		}
	}

	/**
	 * Filters the venue single-line address.
	 *
	 * @since ??
	 * @since 4.5.11 Added docblock and function args to filter.
	 *
	 * @var string  $venue    Venue address line.
	 * @var int     $event_id The event ID.
	 * @var boolean $link     Whether or not the venue should be linked.
	 */
	return apply_filters( 'tribe_events_get_venue_single_line_address', $venue, $event_id, $link );
}
