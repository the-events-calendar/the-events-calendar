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
		return apply_filters( 'tribe_organizer_label_singular', __( 'Organizer', 'tribe-events-calendar' ) );
	}

	/**
	 * Get Organizer Label Plural
	 *
	 * Returns the plural version of the Organizer Label
	 *
	 * @return string
	 */
	function tribe_get_organizer_label_plural() {
		return apply_filters( 'tribe_organizer_label_plural', __( 'Organizers', 'tribe-events-calendar' ) );
	}

	/**
	 * Get the organizer label
	 *
	 * @param bool $singular TRUE to return the singular label, FALSE to return plural
	 *
	 * @return string
	 */
	function tribe_get_organizer_label( $singular = TRUE ) {
		if ( $singular ) {
			return tribe_get_organizer_label_singular();
		} else {
			return tribe_get_organizer_label_plural();
		}
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
	 *
	 * @return string Organizer's Email
	 */
	function tribe_get_organizer_email( $postId = null ) {
		$postId = Tribe__Events__Main::postIdHelper( $postId );
		$output = esc_html( tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerEmail', true ) );

		return apply_filters( 'tribe_get_organizer_email', $output );
	}

	/**
	 * Organizer Page Link
	 *
	 * Returns the event Organizer Name with a link to their single organizer page
	 *
	 * @param int  $postId    Can supply either event id or organizer id, if none specified, current post is used
	 * @param bool $full_link If true displays full html links around organizers name, if false returns just the link without displaying it
	 * @param bool $echo      If true, echo the link, otherwise return
	 *
	 * @return string Organizer Name and Url
	 */
	function tribe_get_organizer_link( $postId = null, $full_link = true, $echo = true ) {
		$postId = Tribe__Events__Main::postIdHelper( $postId );
		if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
			$url = esc_url_raw( get_permalink( tribe_get_organizer_id( $postId ) ) );
			if ( $full_link ) {
				$name = tribe_get_organizer( $postId );
				$link = ! empty( $url ) && ! empty( $name ) ? '<a href="' . esc_url( $url ) . '">' . $name . '</a>' : false;
				$link = apply_filters( 'tribe_get_organizer_link', $link, $postId, $echo, $url, $name );
			} else {
				$link = $url;
			}
			if ( $echo ) {
				echo $link;
			} else {
				return $link;
			}
		}
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
			$output = esc_url( tribe_get_event_meta( tribe_get_organizer_id( $postId ), '_OrganizerWebsite', true ) );

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
				esc_url( $url ),
				apply_filters( 'tribe_get_organizer_website_link_target', 'self' ),
				apply_filters( 'tribe_get_organizer_website_link_label', $label )
			);
		} else {
			$html = '';
		}

		return apply_filters( 'tribe_get_organizer_website_link', $html );
	}

	/**
	 * Get all the organizers
	 *
	 * @param $deprecated
	 * @param $posts_per_page Maximum number of results
	 *
	 * @return array An array of organizer post objects.
	 */
	function tribe_get_organizers( $deprecated = null, $posts_per_page = -1 ) {
		if ( null !== $deprecated ) {
			_deprecated_argument( __FUNCTION__, '3.0', 'This parameter is no longer supported.' );
		}

		$organizers = get_posts(
			array(
				'post_type' => Tribe__Events__Main::ORGANIZER_POST_TYPE,
				'posts_per_page' => $posts_per_page,
			)
		);

		return $organizers;
	}

}
