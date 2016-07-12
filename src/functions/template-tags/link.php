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

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Link to Previous Event (Display)
	 *
	 * Displays a link to the previous post by start date for the given event
	 *
	 * @param bool|string $anchor link text. Use %title% to place the post title in your string.
	 *
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

		return apply_filters( 'tribe_get_prev_event_link', Tribe__Events__Main::instance()->get_event_link( $post, 'previous', $anchor ) );
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

		return apply_filters( 'tribe_get_next_event_link', Tribe__Events__Main::instance()->get_event_link( $post, 'next', $anchor ) );
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
		$plugin = Tribe__Events__Main::instance();

		return apply_filters( 'tribe_get_events_link', $plugin->getLink( 'home' ) );
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
		$tribe_ecp = Tribe__Events__Main::instance();
		$output    = $tribe_ecp->getLink( 'month', false, $term );

		return apply_filters( 'tribe_get_gridview_link', $output );
	}

	/**
	 * Link to List View
	 *
	 * Returns a link to the general or category upcoming view
	 *
	 * @param int $term Optional event category ID to link to.
	 *
	 * @return string URL
	 */
	function tribe_get_listview_link( $term = null ) {
		$tribe_ecp = Tribe__Events__Main::instance();
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
		$tribe_ecp = Tribe__Events__Main::instance();
		$output    = $tribe_ecp->getLink( 'past', false, $term );

		return apply_filters( 'tribe_get_listview_past_link', $output );
	}

	/**
	 * Link to a nearby List View page
	 *
	 * Returns a link to the next/previous list view page
	 *
	 * @param string $direction 'next' or 'prev'
	 * @param int|null $term Term ID
	 * @param string $currently_displaying Type of listview page that is currently being displayed ('past' or 'list')
	 * @param int $page Current page number being displayed
	 *
	 * @return string URL
	 */
	function tribe_get_listview_dir_link( $direction = 'next', $term = null, $currently_displaying = null, $page = null ) {
		$link = tribe_get_listview_link( $term );

		// if a page isn't passed in, attempt to fetch it from a get var
		if ( ! $page ) {
			if ( ! empty( $_POST['tribe_paged'] ) ) {
				$page = absint( $_POST['tribe_paged'] );
			} elseif ( ! empty( $_GET['tribe_paged'] ) ) {
				$page = absint( $_GET['tribe_paged'] );
			} else {
				$page = 1;
			}
		}

		// if what we are currently displaying is not passed in, let's set a default and check $_GET
		if ( ! $currently_displaying ) {
			$currently_displaying = 'list';
			if (
				( ! empty( $_GET['tribe_event_display'] ) && 'past' === $_GET['tribe_event_display'] )
				|| ( ! empty( $_POST['tribe_event_display'] ) && 'past' === $_POST['tribe_event_display'] )
			) {
				$currently_displaying = 'past';
			}
		}

		// assume we want to display what we're currently displaying (until we discover otherwise)
		$display = $currently_displaying;

		if (
			( 'next' === $direction && 'list' === $currently_displaying )
			|| ( 'prev' === $direction && 'past' === $currently_displaying )
		) {
			$page++;
		} elseif ( 'list' === $currently_displaying && 1 === $page ) {
			$display = 'past';
		} elseif ( 'past' === $currently_displaying && 1 === $page ) {
			$display = 'list';
		} else {
			$page--;
		}

		$link = add_query_arg( array(
			'tribe_event_display' => $display,
			'tribe_paged' => $page,
		), $link );

		return apply_filters( 'tribe_get_listview_dir_link', $link, $term );
	}

	/**
	 * Link to prev List View
	 *
	 * Returns a link to the previous list view page
	 *
	 * @param int|null $term Term ID
	 *
	 * @return string URL
	 */
	function tribe_get_listview_prev_link( $term = null ) {
		$link = tribe_get_listview_dir_link( 'prev', $term );
		return apply_filters( 'tribe_get_listview_prev_link', $link, $term );
	}

	/**
	 * Link to next List View
	 *
	 * Returns a link to the next list view page
	 *
	 * @param int|null $term Term ID
	 *
	 * @return string URL
	 */
	function tribe_get_listview_next_link( $term = null ) {
		$link = tribe_get_listview_dir_link( 'next', $term );
		return apply_filters( 'tribe_get_listview_next_link', $link, $term );
	}

	/**
	 * Single Event Link
	 *
	 * Get link to a single event
	 *
	 * @param int $postId Optional post ID
	 * @param bool $full_link If true outputs a complete HTML <a> link, otherwise only the URL is output
	 *
	 * @return string
	 */
	function tribe_get_event_link( $postId = null, $full_link = false ) {

		$url = Tribe__Events__Main::instance()->getLink( 'single', $postId );

		if ( '' != get_option( 'permalink_structure' ) ) $url = trailingslashit( $url );

		if ( $full_link ) {
			$title_args = array( 'post' => $postId, 'echo' => false );
			$name = get_the_title( $postId );
			$attr_title = the_title_attribute( $title_args );
			$link = ! empty( $url ) && ! empty( $name ) ? '<a href="' . esc_url( $url ) . '" title="'.$attr_title.'"">' . $name . '</a>' : false;
		} else {
			$link = $url;
		}

		return apply_filters( 'tribe_get_event_link', $link, $postId, $full_link, $url );
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
				esc_url( $url ),
				apply_filters( 'tribe_get_event_website_link_target', '_self' ),
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
