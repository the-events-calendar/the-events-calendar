<?php
/**
 * Organizer Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
use Tribe\Events\Models\Post_Types\Organizer;

/**
 * Organizer ID
 *
 * Returns the event Organizer ID.
 *
 * @param int $postId Can supply either event id or organizer id.
 *                    If none specified, current post is used.
 *                    If given an event with multiple organizers,
 *                    the first organizer ID is returned.
 *
 * @return int Organizer
 */
function tribe_get_organizer_id( $postId = null ) {
	$postId       = Tribe__Events__Main::postIdHelper( $postId );
	$organizer_id = null;
	if ( is_numeric( $postId ) && $postId > 0 ) {
		$tribe_ecp = Tribe__Events__Main::instance();
		// check if $postId is an organizer id
		if ( $tribe_ecp->isOrganizer( $postId ) ) {
			$organizer_id = $postId;
		} else {
			$organizer_id = tribe_get_event_meta( $postId, '_EventOrganizerID', true );
		}
	}

	return apply_filters( 'tribe_get_organizer_id', $organizer_id, $postId );
}

/**
 * Get the IDs of all organizers associated with an event.
 *
 * @param int $event_id The event post ID. Defaults to the current event.
 *
 * @return array
 */
function tribe_get_organizer_ids( $event_id = null ) {
	$event_id = Tribe__Events__Main::postIdHelper( $event_id );

	$organizer_ids = [];

	if ( Tribe__Events__Main::instance()->isEvent( $event_id ) ) {
		$organizer_ids = tribe_get_event_meta( $event_id, '_EventOrganizerID', false );

		// Protect against storing array items that render false, such as `0`.
		$organizer_ids = array_filter( (array) $organizer_ids );
	}

	return apply_filters( 'tribe_get_organizer_ids', $organizer_ids, $event_id );
}

/**
 * An organizers can have two sources the list of ordered items and the meta field associated with organizers,
 * where the meta field takes precedence we need to respect the order of the meta order only when the present items
 * on the meta field.
 *
 * @deprecated 4.6.23
 * @todo Remove on 4.7
 *
 * @since 4.6.15
 *
 * @param array $current
 * @param array $ordered
 *
 * @return array
 */
function tribe_sanitize_organizers( $current = [], $ordered = [] ) {
	_deprecated_function( __METHOD__, '4.6.23', 'No longer needed after removing reliance on a separate postmeta field to store the ordering.' );

	if ( empty( $ordered ) ) {
		return $current;
	}

	$order    = [];
	$excluded = [];
	foreach ( (array) $current as $post_id ) {
		$key = array_search( $post_id, $ordered );
		if ( false === $key ) {
			$excluded[] = $post_id;
		} else {
			$order[ $key ] = $post_id;
		}
	}

	// Make sure before the merge the order is ordered by the keys.
	ksort( $order );

	return array_merge( $order, $excluded );
}

/**
 * Get Organizer Label Singular.
 * Returns the singular version of the Organizer Label.
 *
 * Note: the output of this function is not escaped.
 * You should escape it wherever you use it!
 *
 * @since 3.7
 * @since5.1.6 remove escaping.
 *
 * @return string The singular version of the Organizer Label.
 */
function tribe_get_organizer_label_singular() {
	/**
	 * Allows customization of the singular version of the Organizer Label.
	 * Note: the output of this filter is not escaped!
	 *
	 * @since 3.7
	 * @since5.1.6 Added docblock, remove escaping.
	 *
	 * @param string $label The singular version of the Organizer label, defaults to "Organizer" (uppercase)
	 */
	return apply_filters(
		'tribe_organizer_label_singular',
		__( 'Organizer', 'the-events-calendar' )
	);
}

/**
 * Get Organizer Label Plural
 * Returns the plural version of the Organizer Label.
 *
 * Note: the output of this function is not escaped.
 * You should escape it wherever you use it!
 *
 * @since 3.7
 * @since5.1.6 remove escaping.
 *
 * @return string The plural version of the Organizer Label.
 */
function tribe_get_organizer_label_plural() {
	/**
	 * Allows customization of the plural version of the Organizer Label.
	 * Note: the output of this filter is not escaped!
	 *
	 * @since 3.7
	 * @since5.1.6 Added docblock, remove escaping.
	 *
	 * @param string $label The plural version of the Organizer label, defaults to "Organizers" (uppercase).
	 */
	return apply_filters(
		'tribe_organizer_label_plural',
		__( 'Organizers', 'the-events-calendar' )
	);
}

/**
 * Get Organizer Label Singular lowercase.
 * Returns the lowercase singular version of the Organizer Label.
 *
 * Note: the output of this function is not escaped.
 * You should escape it wherever you use it!
 *
 * @since 6.2.1
 *
 * @return string The lowercase singular version of the Organizer Label.
 */
function tribe_get_organizer_label_singular_lowercase() {
	/**
	 * Allows customization of the singular lowercase version of the Organizer Label.
	 * Note: the output of this filter is not escaped!
	 *
	 * @since 6.2.1
	 *
	 * @param string $label The singular lowercase version of the Organizer label, defaults to "organizer" (lowercase)
	 */
	return apply_filters(
		'tribe_organizer_label_singular_lowercase',
		__( 'organizer', 'the-events-calendar' )
	);
}

/**
 * Get the organizer label.
 *
 * Note: the output of this function is not escaped.
 * You should escape it wherever you use it!
 *
 * @param bool $singular TRUE to return the singular label, FALSE to return plural.
 *
 * @return string
 */
function tribe_get_organizer_label( $singular = true ) {
	if ( $singular ) {
		return tribe_get_organizer_label_singular();
	} else {
		return tribe_get_organizer_label_plural();
	}
}

/**
 * Returns a summary of key information for the specified organizer.
 *
 * Typically this is a pipe separated format containing the organizer's telephone
 * number, email address and website where available.
 *
 * @param int $post_id Either the organizer or event ID, if none specified, current post is used.
 *
 * @return string
 */
function tribe_get_organizer_details( $post_id = null ) {
	$post_id      = Tribe__Events__Main::postIdHelper( $post_id );
	$organizer_id = (int) tribe_get_organizer_id( $post_id );
	$details      = [];

	if ( $organizer_id && $tel = tribe_get_organizer_phone() ) {
		$details[] = '<span class="tel">' . $tel . '</span>';
	}

	if ( $organizer_id && $email = tribe_get_organizer_email() ) {
		$details[] = '<span class="email"> <a href="mailto:' . esc_attr( $email ) . '">' . $email . '</a> </span>';
	}

	if ( $organizer_id && $link = tribe_get_organizer_website_link() ) {
		// $link is a full HTML string (<a>) whose components are already escaped, so we don't need create an anchor tag or escape again here
		$details[] = '<span class="link">' . $link . '</span>';
	}

	$html = join( '<span class="tribe-events-divider">|</span>', $details );

	if ( ! empty( $html ) ) {
		$html = '<address class="organizer-address">' . $html . '</address>';
	}

	/**
	 * Provides an opportunity to modify the organizer details HTML.
	 *
	 * @param string $html         Organizer details HTML.
	 * @param int    $post_id      Either the organizer or event ID.
	 * @param int    $organizer_id The organizer ID.
	 */
	return apply_filters( 'tribe_get_organizer_details', $html, $post_id, $organizer_id );
}

/**
 * Get Organizer
 *
 * Returns the name of the Organizer
 *
 * @param int $postId Either event id or organizer id, if none specified, current post is used.
 *
 * @return string Organizer's Name
 */
function tribe_get_organizer( $postId = null ) {
	$postId       = Tribe__Events__Main::postIdHelper( $postId );
	$organizer_id = (int) tribe_get_organizer_id( $postId );
	$output       = '';
	if ( $organizer_id > 0 ) {
		$output = esc_html( get_the_title( $organizer_id ) );
	}

	return apply_filters( 'tribe_get_organizer', $output, $organizer_id );
}

/**
 * Organizer Test
 *
 * Returns true or false depending on if the post id has/is an organizer
 *
 * @param int $postId Either event id or organizer id, if none specified, current post is used.
 *
 * @return bool
 */
function tribe_has_organizer( $postId = null ) {
	$postId        = Tribe__Events__Main::postIdHelper( $postId );
	$has_organizer = ( tribe_get_organizer_id( $postId ) > 0 ) ? true : false;

	return apply_filters( 'tribe_has_organizer', $has_organizer );
}

/**
 * Organizer Email
 *
 * Returns the Organizer's Email.
 *
 * @param int  $postId      Either event id or organizer id, if none specified, current post is used.
 * @param bool $antispambot Whether the email should pass through the `antispambot` function or not.
 *
 * @return string Organizer's Email
 */
function tribe_get_organizer_email( $postId = null, $antispambot = true ) {
	$postId = Tribe__Events__Main::postIdHelper( $postId );
	$unfiltered_email  = esc_html( tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerEmail', true ) );
	$filtered_email = $antispambot ? antispambot( $unfiltered_email ) : $unfiltered_email;

	/**
	 * Allows for the organizer email to be filtered.
	 *
	 * Please note that obfuscation of email is already done in a previous line using the `antispambot` function.
	 *
	 * @param string $filtered_email   The organizer email obfuscated using the `antispambot` function.
	 * @param string $unfiltered_email The organizer email as stored in the database before any filtering or obfuscation is applied.
	 */
	$filtered_email = apply_filters( 'tribe_get_organizer_email', $filtered_email, $unfiltered_email );

	return $filtered_email;
}

/**
 * Organizer Page Link
 *
 * Returns the event Organizer Name with a link to their single organizer page.
 *
 * @param int  $post_id   Either event id or organizer id, if none specified, current post is used.
 * @param bool $full_link If true outputs a complete HTML <a> link, otherwise only the URL is output.
 * @param bool $echo      Deprecated. If true, echo the link, otherwise return.
 *
 * @return string Organizer Name and Url
 */
function tribe_get_organizer_link( $post_id = null, $full_link = true, $echo = false ) {

	// As of TEC 4.0 this argument is deprecated.
	// If needed precede the call to this function with echo.
	if ( false != $echo ) {
		_deprecated_argument( __FUNCTION__, '4.0', 'As of TEC 4.0 this argument is deprecated. If needed, precede the call to this function with echo' );
	}

	$org_id = tribe_get_organizer_id( $post_id );
	if ( class_exists( 'Tribe__Events__Pro__Main' ) && get_post_status( $org_id ) == 'publish' ) {

		$url = esc_url_raw( get_permalink( $org_id ) );
		/**
		 * Filter the organizer link target attribute.
		 *
		 * @since 5.1.0
		 *
		 * @param string   $target  The target attribute string. Defaults to "_self".
		 * @param string   $url     The link URL.
		 * @param int      $post_id Either event id or organizer id, if none specified, current post is used.
		 * @param int      $org_id  The organizer id.
		 */
		$target = apply_filters( 'tribe_get_event_organizer_link_target', '_self', $url, $post_id, $org_id );
		$rel    = ( '_blank' === $target ) ? 'noopener noreferrer' : '';

		if ( $full_link ) {
			$name = tribe_get_organizer( $org_id );

			if ( empty( $url ) || empty( $name ) ) {
				$link = false;
			} else {
				$link = sprintf(
					'<a href="%s" title="%s" target="%s" rel="%s">%s</a>',
					esc_url( $url ),
					the_title_attribute(
						[
							'post' => $org_id,
							'echo' => false,
						]
					),
					esc_attr( $target ),
					esc_attr( $rel ),
					esc_html( $name )
				);
			}
		} else {
			$link = $url;
		}

		/**
		 * Filter the organizer link HTML
		 *
		 * @since 4.0
		 *
		 * @param string $link      The link HTML.
		 * @param int    $post_id   The post ID.
		 * @param bool   $full_link If true outputs a complete HTML <a> link, otherwise only the URL is output.
		 * @param string $url       The link URL.
		 */
		return apply_filters( 'tribe_get_organizer_link', $link, $post_id, $full_link, $url );
	}

	//Return Organizer Name if Pro is not Active
	return tribe_get_organizer( $org_id );
}

/**
 * Organizer Phone
 *
 * Returns the event Organizer's phone number
 *
 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
 *
 * @return string Organizer's Phone Number
 */
function tribe_get_organizer_phone( $postId = null ) {
	$postId = Tribe__Events__Main::postIdHelper( $postId );
	$output = esc_html( tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerPhone', true ) );

	return apply_filters( 'tribe_get_organizer_phone', $output );
}

/**
 * Organizer website url
 *
 * Returns the url to the event Organizer's supplied website.
 *
 * @param $postId post ID for an event
 *
 * @return string
 **/
if ( ! function_exists( 'tribe_get_organizer_website_url' ) ) { // wrapped in if function exists to maintain compatibility with community events 3.0.x. wrapper not needed after 3.1.x.
	function tribe_get_organizer_website_url( $postId = null ) {
		$postId = Tribe__Events__Main::postIdHelper( $postId );
		$output = esc_url( esc_html( tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerWebsite', true ) ) );

		return apply_filters( 'tribe_get_organizer_website_url', $output );
	}
}

/**
 * Organizer website link
 *
 * Returns the event Organizer Name with a link to their supplied website
 *
 * @since 3.0
 * @since 6.15.16 Changed the default target to _blank from _self.
 *
 * @param ?int    $post_id The post ID for an event.
 * @param ?string $label   The text for the link.
 * @param ?string $target  The target attribute for the link.
 *
 * @return string
 **/
function tribe_get_organizer_website_link( $post_id = null, $label = null, $target = '_blank' ): string {
	$post_id = tribe_get_organizer_id( $post_id );
	$url     = tribe_get_event_meta( $post_id, '_OrganizerWebsite', true );

	/**
	 * Filter the organizer link target attribute.
	 *
	 * @since 5.1.0
	 * @since 6.15.16 Changed the default target to _blank from _self.
	 *
	 * @param string   $target  The target attribute string. Defaults to "_blank".
	 * @param string   $url     The link URL.
	 * @param null|int $post_id post ID for the organizer.
	 */
	$target = apply_filters( 'tribe_get_event_organizer_link_target', $target, $url, $post_id );

	// Ensure the target is given a valid value.
	$allowed = [ '_self', '_blank', '_parent', '_top', '_unfencedTop' ];
	if ( ! in_array( $target, $allowed, true ) ) {
		$target = '_self';
	}

	$rel = ( '_blank' === $target ) ? 'noopener noreferrer' : 'external';

	/**
	 * Filter the organizer link label
	 *
	 * @since 5.1.0
	 *
	 * @param string $label   The link label/text.
	 * @param int    $post_id The post ID.
	 */
	$label = apply_filters( 'tribe_get_organizer_website_link_label', $label, $post_id );

	if ( ! empty( $url ) ) {
		$label = is_null( $label ) ? $url : $label;

		if ( ! empty( $url ) ) {
			$parse_url = parse_url( $url );
			if ( empty( $parse_url['scheme'] ) ) {
				$url = "http://$url";
			}
		}
		$html = sprintf(
			'<a href="%s" target="%s" rel="%s">%s</a>',
			esc_attr( esc_url( $url ) ),
			esc_attr( $target ),
			esc_attr( $rel ),
			esc_html( $label )
		);
	} else {
		$html = '';
	}

	/**
	 * Filter the organizer link HTML
	 *
	 * @since 3.0
	 *
	 * @param string $html The link HTML.
	 */
	return apply_filters( 'tribe_get_organizer_website_link', $html );
}

/**
 * Get the link for the organizer website.
 *
 * @since 5.5.0
 *
 * @param null|int $post_id The event or organizer ID.
 * @return string  Formatted title for the organizer website link
 */
function tribe_events_get_organizer_website_title( $post_id = null ) {
	$post_id = tribe_get_organizer_id( $post_id );

	/**
	 * Allows customization of a organizer's website title link.
	 *
	 * @since 5.5.0
	 *
	 * @param string $title The title of the organizer's website link.
	 * @param int 	 $post_id The organizer ID.
	 */
	return apply_filters( 'tribe_events_get_organizer_website_title', __( 'Website', 'the-events-calendar' ), $post_id );
}

/**
 * Get all the organizers
 *
 * @param bool  $only_with_upcoming Only return organizers with upcoming events attached to them.
 * @param int   $posts_per_page
 * @param bool  $suppress_filters
 * @param array $args {
 *		Optional. Array of Query parameters.
 *
 *		@type int  $event       Only organizers linked to this event post ID.
 *		@type bool $has_events  Only organizers that have events.
 *		@type bool $found_posts Return the number of found organizers.
 * }
 *
 * @return array|int An array of organizer post objects or an integer value if `found_posts` is set to a truthy value.
 */
function tribe_get_organizers( $only_with_upcoming = false, $posts_per_page = -1, $suppress_filters = true, array $args = [] ) {
	// filter out the `null` values
	$args = array_diff_key( $args, array_filter( $args, 'is_null' ) );

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

		if ('only_with_upcoming' !== $filter_arg) {
			$found = tribe( 'tec.linked-posts.organizer' )->$method( $args[ $filter_arg ] );
		} else {
			$found = tribe( 'tec.linked-posts.organizer' )->find_with_upcoming_events(
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
			'post_type'        => Tribe__Events__Main::ORGANIZER_POST_TYPE,
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
 * Fetches and returns a decorated post object representing a Organizer.
 *
 * @since 5.3.0
 *
 * @param null|int|WP_Post $organizer  The organizer ID or post object or `null` to use the global one.
 * @param string|null      $output The required return type. One of `OBJECT`, `ARRAY_A`, or `ARRAY_N`, which
 *                                 correspond to a WP_Post object, an associative array, or a numeric array,
 *                                 respectively. Defaults to `OBJECT`.
 * @param string           $filter Type of filter to apply. Accepts 'raw'.
 * @param bool             $force  Whether to force a re-fetch ignoring cached results or not.
 *
 * @return array|mixed|void|WP_Post|null {
 *                              The Organizer post object or array, `null` if not found.
 *
 *                              @type string $phone The organizer phone number NOT filtered, apply anti-spambot filters if required.
 *                              @type string $website The organizer full website URL.
 *                              @type string $email The organizer email address NOT filtered, apply anti-spambot filters if required.
 *                          }
 */
function tribe_get_organizer_object( $organizer = null, $output = OBJECT, $filter = 'raw', $force = false ) {
	/**
	 * Filters the organizer result before any logic applies.
	 *
	 * Returning a non `null` value here will short-circuit the function and return the value.
	 * Note: this value will not be cached and the caching of this value is a duty left to the filtering function.
	 *
	 * @since 5.3.0
	 *
	 * @param mixed       $return      The organizer object to return.
	 * @param mixed       $organizer       The organizer object to fetch.
	 * @param string|null $output      The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
	 *                                 correspond to a `WP_Post` object, an associative array, or a numeric array,
	 *                                 respectively. Defaults to `OBJECT`.
	 * @param string      $filter      Type of filter to apply. Accepts 'raw'.
	 */
	$return = apply_filters( 'tribe_get_organizer_object_before', null, $organizer, $output, $filter );

	if ( null !== $return ) {
		return $return;
	}

	/** @var Tribe__Cache $cache */
	$cache = tribe( 'cache' );
	$cache_key = 'tribe_get_organizer_object_' . md5( json_encode( [ $organizer, $output, $filter ] ) );

	// Try getting the memoized value.
	$post = $cache[ $cache_key ];

	if ( false === $post ) {
		// No memoized value, build from properties.
		$post = Organizer::from_post( $organizer )->to_post( $output, $filter );

		/**
		 * Filters the organizer post object before caching it and returning it.
		 *
		 * Note: this value will be cached; as such this filter might not run on each request.
		 * If you need to filter the output value on each call of this function then use the `tribe_get_organizer_object_before`
		 * filter.
		 *
		 * @since 5.3.0
		 *
		 * @param WP_Post $post   The organizer post object, decorated with a set of custom properties.
		 * @param string  $output The output format to use.
		 * @param string  $filter The filter, or context of the fetch.
		 */
		$post = apply_filters( 'tribe_get_organizer_object', $post, $output, $filter );

		// Memoize the value.
		$cache[ $cache_key ] = $post;
	}

	if ( empty( $post ) ) {
		return null;
	}

	/**
	 * Filters the organizer result after the organizer has been built from the function.
	 *
	 * Note: this value will not be cached and the caching of this value is a duty left to the filtering function.
	 *
	 * @deprecated 6.1.4
	 * @since 6.0.3.1
	 *
	 * @param WP_Post     $post        The organizer post object to filter and return.
	 * @param int|WP_Post $organizer   The organizer object to fetch.
	 * @param string|null $output      The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
	 *                                 correspond to a `WP_Post` object, an associative array, or a numeric array,
	 *                                 respectively. Defaults to `OBJECT`.
	 * @param string      $filter      The filter, or context of the fetch.
	 */
	$post = apply_filters_deprecated( 'tribe_get_organiser_object_after', [ $post, $organizer, $output, $filter ], '6.1.4', 'tribe_get_organizer_object_after', 'Deprecated due to misspelling in filter.');

	/**
	 * Filters the organizer result after the organizer has been built from the function.
	 *
	 * Note: this value will not be cached and the caching of this value is a duty left to the filtering function.
	 *
	 * @since 6.1.4
	 *
	 * @param WP_Post     $post        The organizer post object to filter and return.
	 * @param int|WP_Post $organizer   The organizer object to fetch.
	 * @param string|null $output      The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
	 *                                 correspond to a `WP_Post` object, an associative array, or a numeric array,
	 *                                 respectively. Defaults to `OBJECT`.
	 * @param string      $filter      The filter, or context of the fetch.
	 */
	$post = apply_filters( 'tribe_get_organizer_object_after', $post, $organizer, $output, $filter );

	if ( OBJECT !== $output ) {
		$post = ARRAY_A === $output ? (array) $post : array_values( (array) $post );
	}

	return $post;
}
