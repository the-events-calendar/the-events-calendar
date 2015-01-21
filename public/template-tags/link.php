<?php
/**
 * Link Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'TribeEvents' ) ) {

	/**
	 * Link to Previous Event (Display)
	 *
	 * Displays a link to the previous post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 *
	 * @return void
	 * @see tribe_get_prev_event_link()
	 */
	function tribe_the_prev_event_link( $anchor = false ) {
		echo apply_filters( 'tribe_the_prev_event_link', tribe_get_prev_event_link( $anchor ) );
	}

	/**
	 * Return a link to the previous post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 *
	 * @return string
	 */
	function tribe_get_prev_event_link( $anchor = false ) {
		global $post;

		return apply_filters( 'tribe_get_next_event_link', TribeEvents::instance()->get_event_link( $post, 'previous', $anchor ) );
	}

	/**
	 * Link to Next Event (Display)
	 *
	 * Display a link to the next post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 *
	 * @return void
	 * @see tribe_get_next_event_link()
	 */
	function tribe_the_next_event_link( $anchor = false ) {
		echo apply_filters( 'tribe_the_next_event_link', tribe_get_next_event_link( $anchor ) );
	}

	/**
	 * Return a link to the next post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 *
	 * @return string
	 */
	function tribe_get_next_event_link( $anchor = false ) {
		global $post;

		return apply_filters( 'tribe_get_next_event_link', TribeEvents::instance()->get_event_link( $post, 'next', $anchor ) );
	}

	/**
	 * Get a link to the previous events
	 *
	 * @return string
	 */
	function tribe_get_previous_events_link() {

		$link = '';

		if ( tribe_is_upcoming() && ( ! empty ( $_REQUEST['tribe_paged'] ) && $_REQUEST['tribe_paged'] > 1 ) ) {
			// if we're more than one page into the future, the previous link will be in the future as well
			$link = tribe_get_upcoming_link();
		} else {
			$link = tribe_get_past_link();
		}

		return apply_filters( 'tribe_get_previous_events_link', $link );
	}

	/**
	 * Get a link to the next events
	 *
	 * @return string
	 */
	function tribe_get_next_events_link() {

		$link = '';

		if ( tribe_is_past() && ( ! empty ( $_REQUEST['tribe_paged'] ) && $_REQUEST['tribe_paged'] > 1 ) ) {
			// if we're more than one page into the past, the next link will be in the past as well
			$link = tribe_get_past_link();
		} else {
			$link = tribe_get_upcoming_link();
		}

		return apply_filters( 'tribe_get_next_events_link', $link );
	}

	/**
	 * Link to All Events
	 *
	 * Returns a link to the events URL
	 *
	 * @return string URL
	 */
	function tribe_get_events_link() {
		$tribe_ecp = TribeEvents::instance();
		$output    = $tribe_ecp->getLink( 'home' );

		return apply_filters( 'tribe_get_events_link', $output );
	}

	/**
	 * Link to Grid View
	 *
	 * Returns a link to the general or category calendar grid view
	 *
	 * @param string $term Optional event category to link to.
	 *
	 * @return string URL
	 * @todo rename
	 */
	function tribe_get_gridview_link( $term = null ) {
		$tribe_ecp = TribeEvents::instance();
		$output    = $tribe_ecp->getLink( 'month', false, $term );

		return apply_filters( 'tribe_get_gridview_link', $output );
	}

	/**
	 * Link to List View
	 *
	 * Returns a link to the general or category upcoming view
	 *
	 * @param string $term Optional event category to link to.
	 *
	 * @return string URL
	 */
	function tribe_get_listview_link( $term = null ) {
		$tribe_ecp = TribeEvents::instance();
		$output    = $tribe_ecp->getLink( 'list', false, $term );

		return apply_filters( 'tribe_get_listview_link', $output );
	}

	/**
	 * Link to List View (Past)
	 *
	 * Returns a link to the general or category past view
	 *
	 * @param int|null $term Term ID
	 *
	 * @return string URL
	 */
	function tribe_get_listview_past_link( $term = null ) {
		$tribe_ecp = TribeEvents::instance();
		$output    = $tribe_ecp->getLink( 'past', false, $term );

		return apply_filters( 'tribe_get_listview_past_link', $output );
	}

	/**
	 * Single Event Link (Display)
	 *
	 * Display link to a single event
	 *
	 * @param null|int $post Optional post ID
	 *
	 * @return string Link html
	 */
	function tribe_event_link( $post = null ) {
		// pass in whole post object to retain start date
		echo apply_filters( 'tribe_event_link', tribe_get_event_link( $post ) );
	}

	/**
	 * Single Event Link
	 *
	 * Get link to a single event
	 *
	 * @param int $event Optional post ID
	 *
	 * @return string
	 */
	function tribe_get_event_link( $event = null ) {
		if ( '' == get_option( 'permalink_structure' ) ) {
			return apply_filters( 'tribe_get_event_link', TribeEvents::instance()->getLink( 'single', $event ), $event );
		} else {
			return trailingslashit( apply_filters( 'tribe_get_event_link', TribeEvents::instance()->getLink( 'single', $event ), $event ) );
		}
	}

	/**
	 * Event Website Link (more info)
	 *
	 * @param null|object|int $event
	 * @param null|string     $label
	 *
	 * @return string $html
	 */
	function tribe_get_event_website_link( $event = null, $label = null ) {
		$url = tribe_get_event_website_url( $event );
		if ( ! empty( $url ) ) {
			$label = is_null( $label ) ? $url : $label;
			$html  = sprintf(
				'<a href="%s" target="%s">%s</a>',
				$url,
				apply_filters( 'tribe_get_event_website_link_target', 'self' ),
				apply_filters( 'tribe_get_event_website_link_label', $label )
			);
		} else {
			$html = '';
		}

		return apply_filters( 'tribe_get_event_website_link', $html );
	}


	/**
	 * Event Website URL
	 *
	 * @param null|object|int $event
	 *
	 * @return string The event's website URL
	 */
	function tribe_get_event_website_url( $event = null ) {
		$post_id = ( is_object( $event ) && isset( $event->tribe_is_event ) && $event->tribe_is_event ) ? $event->ID : $event;
		$post_id = ( ! empty( $post_id ) || empty( $GLOBALS['post'] ) ) ? $post_id : get_the_ID();
		$url     = tribe_get_event_meta( $post_id, '_EventURL', true );
		if ( ! empty( $url ) ) {
			$parseUrl = parse_url( $url );
			if ( empty( $parseUrl['scheme'] ) ) {
				$url = "http://$url";
			}
		}

		return apply_filters( 'tribe_get_event_website_url', $url, $post_id );
	}

}
?>