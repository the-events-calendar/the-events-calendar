<?php
/**
 * Organizer Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Main' ) ) {

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
	 * Get the IDs of all organizers associated with an event
	 *
	 * @param int $event_id The event post ID. Defaults to the current event.
	 *
	 * @return array
	 */
	function tribe_get_organizer_ids( $event_id = null ) {
		$event_id = Tribe__Events__Main::postIdHelper( $event_id );
		$organizer_ids = array();
		if ( is_numeric( $event_id ) && $event_id > 0 ) {
			if ( Tribe__Events__Main::instance()->isOrganizer( $event_id ) ) {
				$organizer_ids[] = $event_id;
			} else {
				$organizer_ids = tribe_get_event_meta( $event_id, '_EventOrganizerID', false );

				// for some reason we store a blank "0" element in this array.
				// let's scrub this garbage out
				$organizer_ids = array_filter( (array) $organizer_ids );
			}
		}
		return apply_filters( 'tribe_get_organizer_ids', $organizer_ids, $event_id );
	}

	/**
	 * Get Organizer Label Singular
	 *
	 * Returns the singular version of the Organizer Label
	 *
	 * @return string
	 */
	function tribe_get_organizer_label_singular() {
		return apply_filters( 'tribe_organizer_label_singular', esc_html__( 'Organizer', 'the-events-calendar' ) );
	}

	/**
	 * Get Organizer Label Plural
	 *
	 * Returns the plural version of the Organizer Label
	 *
	 * @return string
	 */
	function tribe_get_organizer_label_plural() {
		return apply_filters( 'tribe_organizer_label_plural', esc_html__( 'Organizers', 'the-events-calendar' ) );
	}

	/**
	 * Get the organizer label
	 *
	 * @param bool $singular TRUE to return the singular label, FALSE to return plural
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
	 * @param int $post_id
	 *
	 * @return string
	 */
	function tribe_get_organizer_details( $post_id = null ) {
		$post_id = Tribe__Events__Main::postIdHelper( $post_id );
		$organizer_id = (int) tribe_get_organizer_id( $post_id );
		$details = array();

		if ( $organizer_id && $tel = tribe_get_organizer_phone() ) {
			$details[] = '<span class="tel">' . $tel . '</span>';
		}

		if ( $organizer_id && $email = tribe_get_organizer_email() ) {
			$details[] = '<span class="email"> <a href="mailto:' . esc_attr( $email ) . '">' . $email . '</a> </span>';
		}

		if ( $organizer_id && $link = tribe_get_organizer_website_link() ) {
			$details[] = '<span class="link"> <a href="' . esc_attr( $link ) . '">' . $link . '</a> </span>';
		}

		$html = join( '<span class="tribe-events-divider">|</span>', $details );

		if ( ! empty( $html ) ) {
			$html = '<address class="organizer-address">' . $html . '</address>';
		}

		/**
		 * Provides an opportunity to modify the organizer details HTML.
		 *
		 * @param string $html
		 * @param int    $post_id
		 * @param int    $organizer_id
		 */
		return apply_filters( 'tribe_get_organizer_details', $html, $post_id, $organizer_id );
	}

	/**
	 * Get Organizer
	 *
	 * Returns the name of the Organizer
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
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
	 * Returns true or false depending on if the post id has/is a n organizer
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
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
	 * Returns the Organizer's Email
	 *
	 * @param int $postId Can supply either event id or organizer id, if none specified, current post is used
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
		 * Please note that obfuscation of email is done in subsequent line using the `antispambot` function.
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
	 * Returns the event Organizer Name with a link to their single organizer page
	 *
	 * @param int  $postId    Can supply either event id or organizer id, if none specified, current post is used
	 * @param bool $full_link If true outputs a complete HTML <a> link, otherwise only the URL is output
	 * @param bool $echo      If true, echo the link, otherwise return
	 *
	 * @return string Organizer Name and Url
	 */
	function tribe_get_organizer_link( $postId = null, $full_link = true, $echo = false ) {

		// As of TEC 4.0 this argument is deprecated
		// If needed precede the call to this function with echo
		if ( $echo != false ) _deprecated_argument( __FUNCTION__, '4.0' );

		$org_id = tribe_get_organizer_id( $postId );
		if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
			$url = esc_url_raw( get_permalink( $org_id ) );
			if ( $full_link ) {
				$name = tribe_get_organizer( $org_id );
				$attr_title = the_title_attribute( array( 'post' => $org_id, 'echo' => false ) );
				$link = ! empty( $url ) && ! empty( $name ) ? '<a href="' . esc_url( $url ) . '" title="'.$attr_title.'">' . $name . '</a>' : false;
			} else {
				$link = $url;
			}

			// Remove this in or before 5.x to fully deprecate the echo arg
			if ( $echo ) {
				echo apply_filters( 'tribe_get_organizer_link', $link, $postId, $echo, $url );
			} else {
				return apply_filters( 'tribe_get_organizer_link', $link, $postId, $full_link, $url );
			}
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
	 * Returns the event Organizer Name with a url to their supplied website
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
	 * @param $post_id post ID for an event
	 * @param $label   text for the link
	 *
	 * @return string
	 **/
	function tribe_get_organizer_website_link( $post_id = null, $label = null ) {
		$post_id = tribe_get_organizer_id( $post_id );
		$url     = tribe_get_event_meta( $post_id, '_OrganizerWebsite', true );
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
				apply_filters( 'tribe_get_organizer_website_link_target', '_self' ),
				apply_filters( 'tribe_get_organizer_website_link_label', esc_html( $label ) )
			);
		} else {
			$html = '';
		}

		return apply_filters( 'tribe_get_organizer_website_link', $html );
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
	 *		@type int  $event      Only organizers linked to this event post ID.
	 *		@type bool $has_events Only organizers that have events.
	 * }
	 *
	 * @return array An array of organizer post objects.
	 */
	function tribe_get_organizers( $only_with_upcoming = false, $posts_per_page = - 1, $suppress_filters = true, array $args = array() ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// filter out the `null` values
		$args = array_diff_key( $args, array_filter( $args, 'is_null' ) );

		if ( tribe_is_truthy( $only_with_upcoming ) ) {
			$args['only_with_upcoming'] = true;
		}

		$filter_args = array(
			'event'              => 'find_for_event',
			'has_events'         => 'find_with_events',
			'only_with_upcoming' => 'find_with_upcoming_events',
		);

		foreach ( $filter_args as $filter_arg => $method ) {
			if ( ! isset( $args[ $filter_arg ] ) ) {
				continue;
			}

			$found = call_user_func(
				array( tribe( 'tec.linked-posts.organizer' ), $method ),
				$args[ $filter_arg ]
			);

			if ( empty( $found ) ) {
				return array();
			}

			$args['post__in'] = ! empty( $args['post__in'] )
				? array_intersect( (array) $args['post__in'], $found )
				: $found;

			if ( empty( $args['post__in'] ) ) {
				return array();
			}
		}

		$parsed_args = wp_parse_args( $args, array(
				'post_type'        => Tribe__Events__Main::ORGANIZER_POST_TYPE,
				'posts_per_page'   => $posts_per_page,
				'suppress_filters' => $suppress_filters,
			)
		);

		$organizers = get_posts( $parsed_args );

		return $organizers;
	}

}
